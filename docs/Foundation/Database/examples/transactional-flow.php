<?php

/**
 * Example: Transactional Flow with Execution Scope
 *
 * This example demonstrates:
 * - ExecutionScope for correlation tracking
 * - Transactional boundaries
 * - Query telemetry with binding redaction
 */

declare(strict_types=1);

use Avax\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Database\System\Capabilities\Query\Execution\PDOExecutor;
use Avax\Database\System\Capabilities\Query\Execution\QueryOrchestrator;
use Avax\Database\System\Capabilities\Query\Grammar\MySQLGrammar;
use Avax\Database\System\Capabilities\Telemetry\Support\ExecutionScope;
use Avax\Database\System\Capabilities\Transactions\RunTransaction\Transaction;

// 1. Bootstrap the infrastructure
$pdo = new PDO(dsn: 'mysql:host=localhost;dbname=example', username: 'user', password: 'pass');
$pdo->setAttribute(attribute: PDO::ATTR_ERRMODE, value: PDO::ERRMODE_EXCEPTION);

$grammar    = new MySQLGrammar();
$connection = new class($pdo) implements DatabaseConnection {
    private PDO $pdo;

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function getConnection() : PDO { return $this->pdo; }

    public function getName() : string { return 'primary'; }

    public function config(string|null $key = null, mixed $default = null) : mixed { return null; }

    public function ping() : bool { return true; }

    public function reconnect() : void {}

    public function disconnect() : void {}
};

$executor       = new PDOExecutor(connection: $connection, connectionName: 'primary');
$transactionMgr = Transaction::on(connection: $connection);
$orchestrator   = new QueryOrchestrator(
    executor          : $executor,
    transactionManager: $transactionMgr
);

// 2. Create an execution scope for correlation tracking
$scope = ExecutionScope::fresh(correlationId: 'req_' . bin2hex(string: random_bytes(length: 8)));

// 3. Initialize the Query Builder
$builder = new QueryBuilder(grammar: $grammar, orchestrator: $orchestrator->withScope(scope: $scope));

// 4. Execute within a transactional boundary
$transactionMgr->transaction(callback: static function () use ($builder) {

    // Standard INSERT
    $builder->from(table: 'users')->insert(values: [
                                                       'name'     => 'John Doe',
                                                       'email'    => 'john@example.com',
                                                       'password' => password_hash(password: 'secret', algo: PASSWORD_BCRYPT), // This will be redacted in logs
                                                   ]);

    // Safe raw SQL (validated by guardrails)
    $builder->from(table: 'stats')
        ->selectRaw('COUNT(*) as total')
        ->get();
});

// 5. Observability: All queries dispatched QueryExecuted events with redacted bindings
// Check your logs to see:
// - correlation_id: req_xxxxx
// - bindings: ['[REDACTED]', '[REDACTED]', '[REDACTED]']

echo "Transaction completed successfully.\n";
