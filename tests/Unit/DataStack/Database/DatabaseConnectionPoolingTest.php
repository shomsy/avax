<?php

declare(strict_types=1);

namespace Tests\Unit\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\PdoConnectionPool\PdoConnectionPool;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\PdoPooledConnection\PdoPooledConnection;
use Avax\Components\DataStack\Database\System\Flows\ExecuteQuery\ExecuteQuery;
use Avax\Components\DataStack\Database\System\Foundation\Values\QueryResult;
use PDO;
use PHPUnit\Framework\TestCase;

final class DatabaseConnectionPoolingTest extends TestCase
{
    private PDO $sqlite;

    protected function setUp() : void
    {
        $this->sqlite = new PDO('sqlite::memory:');
        $this->sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // -- PdoPooledConnection tests

    public function test_pooled_connection_returns_resource() : void
    {
        $pooled = new PdoPooledConnection($this->sqlite);

        self::assertSame($this->sqlite, $pooled->getResource());
    }

    public function test_pooled_connection_is_valid() : void
    {
        $pooled = new PdoPooledConnection($this->sqlite);

        self::assertTrue($pooled->isValid());
    }

    public function test_pooled_connection_tracks_created_at() : void
    {
        $pooled = new PdoPooledConnection($this->sqlite);

        self::assertGreaterThan(0, $pooled->getCreatedAt());
    }

    public function test_pooled_connection_tracks_execute_count() : void
    {
        $pooled = new PdoPooledConnection($this->sqlite);

        self::assertSame(0, $pooled->executeCount());
        $pooled->recordExecute();
        self::assertSame(1, $pooled->executeCount());
    }

    // -- PdoConnectionPool tests

    public function test_pool_get_and_release() : void
    {
        $pool = new PdoConnectionPool(
            dsn: ['driver' => 'sqlite', 'database' => ':memory:'],
            minConnections: 1,
            maxConnections: 5,
        );

        $conn = $pool->get();

        self::assertInstanceOf(PdoPooledConnection::class, $conn);
        self::assertTrue($conn->isValid());

        $pool->release($conn);
        $pool->destroy();
    }

    public function test_pool_stats() : void
    {
        $pool = new PdoConnectionPool(
            dsn: ['driver' => 'sqlite', 'database' => ':memory:'],
            minConnections: 1,
            maxConnections: 5,
        );

        $pool->get();
        $stats = $pool->stats();

        self::assertGreaterThan(0, $stats->totalConnections);
        self::assertGreaterThan(0, $stats->activeConnections);

        $pool->destroy();
    }

    public function test_pool_reuses_idle_connection() : void
    {
        $pool = new PdoConnectionPool(
            dsn: ['driver' => 'sqlite', 'database' => ':memory:'],
            minConnections: 1,
            maxConnections: 5,
        );

        $conn1 = $pool->get();
        $resource1 = $conn1->getResource();
        $pool->release($conn1);

        $conn2 = $pool->get();
        $resource2 = $conn2->getResource();

        // Should reuse the same pooled connection
        self::assertSame($resource1, $resource2);

        $pool->destroy();
    }

    // -- ExecuteQuery tests

    public function test_execute_select_query() : void
    {
        $this->sqlite->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)');
        $this->sqlite->exec("INSERT INTO users (name) VALUES ('alice')");
        $this->sqlite->exec("INSERT INTO users (name) VALUES ('bob')");

        $executor = new ExecuteQuery();
        $result = $executor->execute($this->sqlite, 'SELECT * FROM users');

        self::assertIsArray($result);
        self::assertCount(2, $result);
        $rows = $result;
        self::assertSame('alice', $rows[0]['name']);
        self::assertSame('bob', $rows[1]['name']);
    }

    public function test_execute_insert_query_returns_affected_rows() : void
    {
        $this->sqlite->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)');

        $executor = new ExecuteQuery();
        $affected = $executor->execute($this->sqlite, "INSERT INTO users (name) VALUES ('charlie')");

        self::assertSame(1, $affected);
    }

    public function test_execute_with_parameters() : void
    {
        $this->sqlite->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)');

        $executor = new ExecuteQuery();
        $executor->execute($this->sqlite, 'INSERT INTO users (name) VALUES (?)', ['dave']);

        $result = $executor->execute($this->sqlite, 'SELECT * FROM users WHERE name = ?', ['dave']);

        self::assertIsArray($result);
        self::assertCount(1, $result);
        $rows = $result;
        self::assertSame('dave', $rows[0]['name']);
    }

    public function test_execute_cursor_returns_statement() : void
    {
        $this->sqlite->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)');
        $this->sqlite->exec("INSERT INTO users (name) VALUES ('eve')");

        $executor = new ExecuteQuery();
        $stmt = $executor->executeCursor($this->sqlite, 'SELECT * FROM users');

        self::assertInstanceOf(\PDOStatement::class, $stmt);
        self::assertCount(1, $stmt->fetchAll());
    }

    // -- QueryResult tests

    public function test_query_result_select_factory() : void
    {
        $rows = [['id' => 1, 'name' => 'test']];
        $result = QueryResult::select($rows, elapsedMs: 1.5);

        self::assertTrue($result->isRead());
        self::assertCount(1, $result->rows);
        self::assertSame(1.5, $result->elapsedMs);
    }

    public function test_query_result_write_factory() : void
    {
        $result = QueryResult::write(affectedRows: 3, lastInsertId: '42', elapsedMs: 2.0);

        self::assertFalse($result->isRead());
        self::assertSame(3, $result->affectedRows);
        self::assertSame('42', $result->lastInsertId);
    }
}
