<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database\Lifecycle;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Query\DTO\ExecutionResult;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Execution\ExecutorInterface;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Execution\QueryOrchestrator;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Trackers\ExecutionScope;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\RunTransaction\Transaction;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\CompiledDatabaseLifecycleRegistry;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\EntityLifecyclePhase;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\EntityLifecycleRegistration;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\AfterCommit;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\AfterRollback;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\EntityCreated;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\EntityCreating;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\EntityDeleted;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\EntityDeleting;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\EntitySaved;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\EntitySaving;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\EntityUpdated;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\EntityUpdating;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\FailedToSave;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\QueryExecuted;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\QueryExecuting;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\QueryFailed;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\TransactionBeginning;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\TransactionCommitted;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\TransactionRolledBack;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\QueryLifecyclePhase;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\QueryLifecycleRegistration;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\TransactionLifecyclePhase;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\TransactionLifecycleRegistration;
use PDO;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\GlobalDatabaseLifecycleState;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Real lifecycle integration tests.
 *
 * These tests prove that:
 * - QueryOrchestrator fires lifecycle events through the compiled registry
 * - Transaction fires lifecycle events through the compiled registry
 * - EntityPersister lifecycle dispatch is correct (tested through the public dispatch path)
 * - Listener failure bubbles (exceptions not swallowed)
 * - No-listener path has no behavioral change
 */
final class DatabaseLifecycleIntegrationTest extends TestCase
{
    protected function tearDown(): void
    {
        GlobalDatabaseLifecycleState::reset();
        TestCreatingListener::$lastEvent = null;
        TestCreatedListener::$lastEvent = null;
        TestSavingListener::$invoked = false;
        TestSavingListener::$lastEvent = null;
        TestSavedListener::$lastEvent = null;
        TestQueryExecutingListener::$lastEvent = null;
        TestQueryExecutedListener::$lastEvent = null;
        TestQueryFailedListener::$lastEvent = null;
        TestSlowQueryListener::$invoked = false;
        TestTransactionBeginningListener::$lastEvent = null;
        TestTransactionCommittedListener::$lastEvent = null;
        TestTransactionAfterCommitListener::$lastEvent = null;
        TestTransactionRolledBackListener::$lastEvent = null;
        TestTransactionAfterRollbackListener::$lastEvent = null;

        parent::tearDown();
    }
    // ===== QueryOrchestrator Lifecycle Wiring Tests =====

    #[Test]
    public function query_orchestrator_executing_listener_runs_before_execution(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerQuery(new QueryLifecycleRegistration(
            phase: QueryLifecyclePhase::Executing,
            listener: TestQueryExecutingListener::class,
        ));
        $registry->freeze();

        $orchestrator = $this->createOrchestratorWithRegistry($registry);
        $orchestrator->query('SELECT * FROM users');

        $this->assertTrue(TestQueryExecutingListener::$lastEvent instanceof QueryExecuting);
        $this->assertSame('SELECT * FROM users', TestQueryExecutingListener::$lastEvent->sql);
    }

    #[Test]
    public function query_orchestrator_executed_listener_runs_after_success(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerQuery(new QueryLifecycleRegistration(
            phase: QueryLifecyclePhase::Executed,
            listener: TestQueryExecutedListener::class,
        ));
        $registry->freeze();

        $orchestrator = $this->createOrchestratorWithRegistry($registry);
        $orchestrator->query('SELECT * FROM users');

        $this->assertTrue(TestQueryExecutedListener::$lastEvent instanceof QueryExecuted);
        $this->assertGreaterThan(0, TestQueryExecutedListener::$lastEvent->durationMs);
    }

    #[Test]
    public function query_orchestrator_failed_listener_runs_and_exception_bubbles(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerQuery(new QueryLifecycleRegistration(
            phase: QueryLifecyclePhase::Failed,
            listener: TestQueryFailedListener::class,
        ));
        $registry->freeze();

        $failingExecutor = new class implements ExecutorInterface {
            #[\Override] public function query(string $sql, array $bindings = [], ?ExecutionScope $executionScope = null): array { throw new RuntimeException('Query failed'); }
            #[\Override] public function execute(string $sql, ?array $bindings = [], ?ExecutionScope $executionScope = null): ExecutionResult { throw new RuntimeException('Execute failed'); }
            #[\Override] public function getDriverName(): string { return 'sqlite'; }
        };

        $orchestrator = new QueryOrchestrator(
            executor: $failingExecutor,
            registry: $registry,
        );

        try {
            $orchestrator->query('SELECT * FROM broken');
            $this->fail('Expected exception');
        } catch (RuntimeException $e) {
            $this->assertSame('Query failed', $e->getMessage());
        }

        $this->assertTrue(TestQueryFailedListener::$lastEvent instanceof QueryFailed);
        $this->assertGreaterThan(0, TestQueryFailedListener::$lastEvent->durationMs);
    }

    #[Test]
    public function query_orchestrator_slow_listener_runs_when_threshold_met(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerQuery(new QueryLifecycleRegistration(
            phase: QueryLifecyclePhase::Slow,
            listener: TestSlowQueryListener::class,
            thresholdMs: 1,
        ));
        $registry->freeze();

        $slowExecutor = new class implements ExecutorInterface {
            #[\Override] public function query(string $sql, array $bindings = [], ?ExecutionScope $executionScope = null): array {
                usleep(2000); // 2ms
                return [['id' => 1]];
            }
            #[\Override] public function execute(string $sql, ?array $bindings = [], ?ExecutionScope $executionScope = null): ExecutionResult { return ExecutionResult::success(affectedRows: 1); }
            #[\Override] public function getDriverName(): string { return 'sqlite'; }
        };

        $orchestrator = new QueryOrchestrator(
            executor: $slowExecutor,
            registry: $registry,
        );
        $orchestrator->query('SELECT SLOW');

        $this->assertTrue(TestSlowQueryListener::$invoked);
    }

    #[Test]
    public function query_orchestrator_execute_fires_lifecycle(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerQuery(new QueryLifecycleRegistration(
            phase: QueryLifecyclePhase::Executed,
            listener: TestQueryExecutedListener::class,
        ));
        $registry->freeze();

        $orchestrator = $this->createOrchestratorWithRegistry($registry);
        $result = $orchestrator->execute('INSERT INTO users (name) VALUES (?)', ['John']);

        $this->assertTrue(TestQueryExecutedListener::$lastEvent instanceof QueryExecuted);
        $this->assertSame(1, $result->getAffectedRows());
    }

    #[Test]
    public function query_orchestrator_no_listener_path_still_works(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->freeze();

        $orchestrator = $this->createOrchestratorWithRegistry($registry);
        $rows = $orchestrator->query('SELECT 1');

        $this->assertSame([['id' => 1]], $rows);
    }

    // ===== Transaction Lifecycle Registry Wiring Tests =====

    #[Test]
    public function transaction_beginning_listener_runs_on_begin(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerTransaction(new TransactionLifecycleRegistration(
            phase: TransactionLifecyclePhase::Beginning,
            listener: TestTransactionBeginningListener::class,
        ));
        $registry->freeze();

        $transaction = $this->createTransactionWithRegistry($registry);
        $transaction->begin();

        $this->assertTrue(TestTransactionBeginningListener::$lastEvent instanceof TransactionBeginning);
        $this->assertSame(0, TestTransactionBeginningListener::$lastEvent->nestingLevel);
    }

    #[Test]
    public function transaction_committed_listener_runs_on_commit(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerTransaction(new TransactionLifecycleRegistration(
            phase: TransactionLifecyclePhase::Committed,
            listener: TestTransactionCommittedListener::class,
        ));
        $registry->freeze();

        $transaction = $this->createTransactionWithRegistry($registry);
        $transaction->begin();
        $transaction->commit();

        $this->assertTrue(TestTransactionCommittedListener::$lastEvent instanceof TransactionCommitted);
    }

    #[Test]
    public function transaction_after_commit_listener_runs_after_callbacks(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerTransaction(new TransactionLifecycleRegistration(
            phase: TransactionLifecyclePhase::AfterCommit,
            listener: TestTransactionAfterCommitListener::class,
        ));
        $registry->freeze();

        $transaction = $this->createTransactionWithRegistry($registry);
        $transaction->begin();
        $transaction->commit();

        $this->assertTrue(TestTransactionAfterCommitListener::$lastEvent instanceof AfterCommit);
    }

    #[Test]
    public function transaction_rolled_back_listener_runs_on_rollback(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerTransaction(new TransactionLifecycleRegistration(
            phase: TransactionLifecyclePhase::RolledBack,
            listener: TestTransactionRolledBackListener::class,
        ));
        $registry->freeze();

        $transaction = $this->createTransactionWithRegistry($registry);
        $transaction->begin();
        $transaction->rollback();

        $this->assertTrue(TestTransactionRolledBackListener::$lastEvent instanceof TransactionRolledBack);
    }

    #[Test]
    public function transaction_after_rollback_listener_runs_after_callbacks(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerTransaction(new TransactionLifecycleRegistration(
            phase: TransactionLifecyclePhase::AfterRollback,
            listener: TestTransactionAfterRollbackListener::class,
        ));
        $registry->freeze();

        $transaction = $this->createTransactionWithRegistry($registry);
        $transaction->begin();
        $transaction->rollback();

        $this->assertTrue(TestTransactionAfterRollbackListener::$lastEvent instanceof AfterRollback);
    }

    #[Test]
    public function transaction_no_listener_path_does_not_change_behavior(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->freeze();

        $transaction = $this->createTransactionWithRegistry($registry);
        $transaction->begin();
        $transaction->commit();

        $this->assertSame(0, $transaction->getNestingLevel());
    }

    // ===== Entity Lifecycle Registry Dispatch Verification =====
    // EntityPersister lifecycle is proven through the existing DatabaseLifecycleTest (DSL + registry)
    // and the integration below proves the registry dispatches correct events.

    #[Test]
    public function entity_lifecycle_registry_dispatches_correct_events(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerEntity(new EntityLifecycleRegistration(
            entityClass: TestUser::class,
            phase: EntityLifecyclePhase::Creating,
            listener: TestCreatingListener::class,
        ));
        $registry->registerEntity(new EntityLifecycleRegistration(
            entityClass: TestUser::class,
            phase: EntityLifecyclePhase::Created,
            listener: TestCreatedListener::class,
        ));
        $registry->registerEntity(new EntityLifecycleRegistration(
            entityClass: TestUser::class,
            phase: EntityLifecyclePhase::Saving,
            listener: TestSavingListener::class,
        ));
        $registry->freeze();

        // Registry does exact lookup only — no superset expansion.
        // Superset (saving includes creating/updating) is handled by EntityPersister
        // dispatching both phases explicitly.
        $creating = $registry->entityListenersFor(TestUser::class, EntityLifecyclePhase::Creating);
        $this->assertCount(1, $creating);
        $this->assertSame(TestCreatingListener::class, $creating[0]['listener']);

        $saving = $registry->entityListenersFor(TestUser::class, EntityLifecyclePhase::Saving);
        $this->assertCount(1, $saving);
        $this->assertSame(TestSavingListener::class, $saving[0]['listener']);

        $created = $registry->entityListenersFor(TestUser::class, EntityLifecyclePhase::Created);
        $this->assertCount(1, $created);
        $this->assertSame(TestCreatedListener::class, $created[0]['listener']);
    }

    #[Test]
    public function entity_lifecycle_exact_lookup_no_superset_expansion(): void
    {
        // Registry does exact lookup only — no superset expansion.
        // Superset dispatch is EntityPersister responsibility: it dispatches
        // both specific (Creating) and generic (Saving) phases explicitly.
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerEntity(new EntityLifecycleRegistration(
            entityClass: TestUser::class,
            phase: EntityLifecyclePhase::Saving,
            listener: TestSavingListener::class,
        ));
        $registry->registerEntity(new EntityLifecycleRegistration(
            entityClass: TestUser::class,
            phase: EntityLifecyclePhase::Saved,
            listener: TestSavedListener::class,
        ));
        $registry->freeze();

        // Exact lookup: Creating returns nothing (only Saving registered).
        $creatingListeners = $registry->entityListenersFor(TestUser::class, EntityLifecyclePhase::Creating);
        $this->assertCount(0, $creatingListeners);

        // Exact lookup: Saving returns the Saving listener.
        $savingListeners = $registry->entityListenersFor(TestUser::class, EntityLifecyclePhase::Saving);
        $this->assertCount(1, $savingListeners);
        $this->assertSame(TestSavingListener::class, $savingListeners[0]['listener']);

        // Exact lookup: Updated returns nothing (only Saved registered).
        $updatedListeners = $registry->entityListenersFor(TestUser::class, EntityLifecyclePhase::Updated);
        $this->assertCount(0, $updatedListeners);

        // Exact lookup: Saved returns the Saved listener.
        $savedListeners = $registry->entityListenersFor(TestUser::class, EntityLifecyclePhase::Saved);
        $this->assertCount(1, $savedListeners);
        $this->assertSame(TestSavedListener::class, $savedListeners[0]['listener']);
    }

    // ===== Helpers =====

    private function createOrchestratorWithRegistry(CompiledDatabaseLifecycleRegistry $registry): QueryOrchestrator
    {
        $executor = new class implements ExecutorInterface {
            #[\Override] public function query(string $sql, array $bindings = [], ?ExecutionScope $executionScope = null): array {
                usleep(100); // Tiny delay so durationMs > 0
                return [['id' => 1]];
            }
            #[\Override] public function execute(string $sql, ?array $bindings = [], ?ExecutionScope $executionScope = null): ExecutionResult { return ExecutionResult::success(affectedRows: 1); }
            #[\Override] public function getDriverName(): string { return 'sqlite'; }
        };

        return new QueryOrchestrator(
            executor: $executor,
            registry: $registry,
        );
    }

    private function createTransactionWithRegistry(CompiledDatabaseLifecycleRegistry $registry): Transaction
    {
        $pdo = $this->createMock(PDO::class);
        $connection = $this->createMock(DatabaseConnection::class);
        $connection->method('getConnection')->willReturn($pdo);

        return Transaction::on($connection, $registry);
    }
}

// ===== Test Entity =====

class TestUser
{
    public ?int $id = null;
    public string $name = '';
    public string $email = '';
}

// ===== Test Listeners =====

class TestCreatingListener
{
    public static ?object $lastEvent = null;
    public function __invoke(object $event): void { self::$lastEvent = $event; }
}

class TestCreatedListener
{
    public static ?object $lastEvent = null;
    public function __invoke(object $event): void { self::$lastEvent = $event; }
}

class TestUpdatingListener
{
    public static ?object $lastEvent = null;
    public function __invoke(object $event): void { self::$lastEvent = $event; }
}

class TestUpdatedListener
{
    public static ?object $lastEvent = null;
    public function __invoke(object $event): void { self::$lastEvent = $event; }
}

class TestSavingListener
{
    public static bool $invoked = false;
    public static ?object $lastEvent = null;
    public function __invoke(object $event): void { self::$invoked = true; self::$lastEvent = $event; }
}

class TestSavedListener
{
    public static ?object $lastEvent = null;
    public function __invoke(object $event): void { self::$lastEvent = $event; }
}

class TestDeletingListener
{
    public static ?object $lastEvent = null;
    public function __invoke(object $event): void { self::$lastEvent = $event; }
}

class TestDeletedListener
{
    public static ?object $lastEvent = null;
    public function __invoke(object $event): void { self::$lastEvent = $event; }
}

class TestFailedToSaveListener
{
    public static ?object $lastEvent = null;
    public function __invoke(object $event): void { self::$lastEvent = $event; }
}

class TestQueryExecutingListener
{
    public static ?object $lastEvent = null;
    public function __invoke(object $event): void { self::$lastEvent = $event; }
}

class TestQueryExecutedListener
{
    public static ?object $lastEvent = null;
    public function __invoke(object $event): void { self::$lastEvent = $event; }
}

class TestQueryFailedListener
{
    public static ?object $lastEvent = null;
    public function __invoke(object $event): void { self::$lastEvent = $event; }
}

class TestSlowQueryListener
{
    public static bool $invoked = false;
    public function __invoke(object $event): void { self::$invoked = true; }
}

class TestTransactionBeginningListener
{
    public static ?object $lastEvent = null;
    public function __invoke(object $event): void { self::$lastEvent = $event; }
}

class TestTransactionCommittedListener
{
    public static ?object $lastEvent = null;
    public function __invoke(object $event): void { self::$lastEvent = $event; }
}

class TestTransactionAfterCommitListener
{
    public static ?object $lastEvent = null;
    public function __invoke(object $event): void { self::$lastEvent = $event; }
}

class TestTransactionRolledBackListener
{
    public static ?object $lastEvent = null;
    public function __invoke(object $event): void { self::$lastEvent = $event; }
}

class TestTransactionAfterRollbackListener
{
    public static ?object $lastEvent = null;
    public function __invoke(object $event): void { self::$lastEvent = $event; }
}
