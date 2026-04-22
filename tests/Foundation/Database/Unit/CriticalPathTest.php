<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Database\Unit;

use Avax\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Database\System\Capabilities\Querying\Builder\QueryBuilder;
use Avax\Database\System\Capabilities\Querying\Exceptions\QueryException;
use Avax\Database\System\Capabilities\Querying\Execution\PDOExecutor;
use Avax\Database\System\Capabilities\Querying\Execution\QueryOrchestrator;
use Avax\Database\System\Capabilities\Querying\Grammar\MySQLGrammar;
use Avax\Database\System\Capabilities\Querying\Identity\IdentityMap;
use Avax\Database\System\Capabilities\Transactions\RunTransaction\Transaction;
use Exception;
use PDO;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Throwable;

/**
 * Critical path test: Pool lifecycle, Transaction rollback, Security redaction.
 */
final class CriticalPathTest extends TestCase
{
    private PDO $pdo;

    /**
     * Test: Transaction rollback on inner failure.
     *
     * @throws ReflectionException
     * @throws Throwable
     */
    public function test_transaction_rollback_on_failure() : void
    {
        $grammar        = new MySQLGrammar;
        $connection     = new class($this->pdo) implements DatabaseConnection {
            private PDO $pdo;

            public function __construct(PDO $pdo) { $this->pdo = $pdo; }

            public function getConnection() : PDO { return $this->pdo; }

            public function getName() : string { return 'test'; }

            public function config(string|null $key = null, mixed $default = null) : mixed { return null; }

            public function ping() : bool { return true; }

            public function reconnect() : void {}

            public function disconnect() : void {}
        };
        $executor       = new PDOExecutor(connection: $connection, connectionName: 'test');
        $transactionMgr = Transaction::on(connection: $connection);
        $orchestrator   = new QueryOrchestrator(executor: $executor, transactionManager: $transactionMgr);
        $builder        = new QueryBuilder(grammar: $grammar, orchestrator: $orchestrator);

        try {
            $builder->transaction(callback: function (QueryBuilder $query) {
                $query->from(table: 'users')->insert(values: ['name' => 'Alice', 'email' => 'alice@test.com']);

                // Force an exception
                throw new Exception(message: 'Simulated failure');
            });
        } catch (Exception $e) {
            // Expected
        }

        // Verify rollback: no records should exist
        $count = $builder->from(table: 'users')->count();
        $this->assertSame(expected: 0, actual: $count, message: 'Transaction should have rolled back');
    }

    /**
     * Test: Identity Map deferred execution.
     *
     * @throws Throwable
     */
    public function test_identity_map_defers_execution() : void
    {
        $grammar        = new MySQLGrammar;
        $connection     = new class($this->pdo) implements DatabaseConnection {
            private PDO $pdo;

            public function __construct(PDO $pdo) { $this->pdo = $pdo; }

            public function getConnection() : PDO { return $this->pdo; }

            public function getName() : string { return 'test'; }

            public function config(string|null $key = null, mixed $default = null) : mixed { return null; }

            public function ping() : bool { return true; }

            public function reconnect() : void {}

            public function disconnect() : void {}
        };
        $executor       = new PDOExecutor(connection: $connection, connectionName: 'test');
        $transactionMgr = Transaction::on(connection: $connection);
        $orchestrator   = new QueryOrchestrator(executor: $executor, transactionManager: $transactionMgr);
        $identityMap    = new IdentityMap(transactionManager: $transactionMgr, connection: $connection);
        $builder        = new QueryBuilder(grammar: $grammar, orchestrator: $orchestrator->withIdentityMap(map: $identityMap));

        $deferred = $builder->deferred(identityMap: $identityMap);
        $deferred->from(table: 'users')->insert(values: ['name' => 'Bob', 'email' => 'bob@test.com']);

        // Before flush: no records
        $count = $builder->from(table: 'users')->count();
        $this->assertSame(expected: 0, actual: $count);

        // After flush: record exists
        $identityMap->execute();
        $count = $builder->from(table: 'users')->count();
        $this->assertSame(expected: 1, actual: $count);
    }

    /**
     * Test: QueryException never exposes raw bindings by default.
     *
     * @throws ReflectionException
     * @throws Throwable
     */
    public function test_query_exception_redacts_bindings_by_default() : void
    {
        $grammar      = new MySQLGrammar;
        $connection   = new class($this->pdo) implements DatabaseConnection {
            private PDO $pdo;

            public function __construct(PDO $pdo) { $this->pdo = $pdo; }

            public function getConnection() : PDO { return $this->pdo; }

            public function getName() : string { return 'test'; }

            public function config(string|null $key = null, mixed $default = null) : mixed { return null; }

            public function ping() : bool { return true; }

            public function reconnect() : void {}

            public function disconnect() : void {}
        };
        $executor     = new PDOExecutor(connection: $connection, connectionName: 'test');
        $orchestrator = new QueryOrchestrator(executor: $executor);
        $builder      = new QueryBuilder(grammar: $grammar, orchestrator: $orchestrator);

        try {
            // Invalid SQL to trigger exception
            $builder->from(table: 'nonexistent')->insert(values: ['secret' => 'password123']);
        } catch (QueryException $e) {
            $bindings = $e->getBindings(); // Default redacted
            $this->assertSame(expected: ['[REDACTED]'], actual: $bindings);

            $rawBindings = $e->getBindings(redacted: false); // Explicit opt-in
            $this->assertSame(expected: ['password123'], actual: $rawBindings);

            return;
        }

        $this->fail(message: 'Expected QueryException was not thrown.');
    }

    protected function setUp() : void
    {
        $this->pdo = new PDO(dsn: 'sqlite::memory:');
        $this->pdo->setAttribute(attribute: PDO::ATTR_ERRMODE, value: PDO::ERRMODE_EXCEPTION);
        // noinspection SqlNoDataSourceInspection
        $this->pdo->exec(statement: 'CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, email TEXT)');
    }
}
