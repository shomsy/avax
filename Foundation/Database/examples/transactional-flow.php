<?php

/**
 * Example: Transactional Flow with Execution Scope and Identity Map
 *
 * This example demonstrates:
 * - ExecutionScope for correlation tracking
 * - Transactional boundaries
 * - Deferred execution via Identity Map
 * - Query telemetry with binding redaction
 */

declare(strict_types=1);

use Avax\Database\Identity\IdentityMap;
use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;
use Avax\Database\QueryBuilder\Core\Executor\PDOExecutor;
use Avax\Database\QueryBuilder\Core\Executor\QueryOrchestrator;
use Avax\Database\QueryBuilder\Core\Grammar\MySQLGrammar;
use Avax\Database\Support\ExecutionScope;
use Avax\Database\Transaction\TransactionManager;

// 1. Bootstrap the infrastructure
$pdo = new PDO(dsn: 'mysql:host=localhost;dbname=example', username: 'user', password: 'pass');
$pdo->setAttribute(attribute: PDO::ATTR_ERRMODE, value: PDO::ERRMODE_EXCEPTION);

$grammar = new MySQLGrammar;
$executor = new PDOExecutor(pdo: $pdo, connectionName: 'primary');
$transactionMgr = new TransactionManager(pdo: $pdo);
$orchestrator = new QueryOrchestrator(
    executor          : $executor,
    transactionManager: $transactionMgr
);

// 2. Create an execution scope for correlation tracking
$scope = ExecutionScope::fresh(correlationId: 'req_'.bin2hex(string: random_bytes(length: 8)));

// 3. Initialize the Query Builder
$builder = new QueryBuilder(grammar: $grammar, orchestrator: $orchestrator->withScope(scope: $scope));

// 4. Execute within a transactional boundary
$builder->transaction(callback: function (QueryBuilder $query) {

    // Standard INSERT
    $query->from(table: 'users')->insert(values: [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => password_hash(password: 'secret', algo: PASSWORD_BCRYPT), // This will be redacted in logs
    ]);

    // Deferred execution with Identity Map (batch optimization)
    $identityMap = new IdentityMap(orchestrator: $query->orchestrator);

    $deferredQuery = $query->deferred(identityMap: $identityMap);

    $deferredQuery->from(table: 'audit_log')->insert(values: ['action' => 'user_created']);
    $deferredQuery->from(table: 'audit_log')->insert(values: ['action' => 'email_sent']);

    // Flush deferred operations
    $identityMap->execute();

    // Safe raw SQL (validated by guardrails)
    $query->from(table: 'stats')
        ->selectRaw('COUNT(*) as total')
        ->get();
});

// 5. Observability: All queries dispatched QueryExecuted events with redacted bindings
// Check your logs to see:
// - correlation_id: req_xxxxx
// - bindings: ['[REDACTED]', '[REDACTED]', '[REDACTED]']
// - raw_bindings: (only if DB_LOG_BINDINGS=raw)

echo "Transaction completed successfully.\n";
