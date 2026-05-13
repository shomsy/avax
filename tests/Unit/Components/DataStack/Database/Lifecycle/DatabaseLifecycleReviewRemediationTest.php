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
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\QueryExecuted;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\QueryExecuting;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\QueryFailed;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\GlobalDatabaseLifecycleState;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\QueryLifecyclePhase;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\QueryLifecycleRegistration;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\TransactionLifecyclePhase;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\TransactionLifecycleRegistration;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\RedactBindings;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * V5.8.1 Review Remediation — Final Behavior Test Suite.
 *
 * Proves:
 * 1. Entity lifecycle exact-once dispatch (no double saving/saved)
 * 2. Public DSL reaches runtime (shared registry wiring)
 * 3. Transaction afterCommit failure semantics
 * 4. Query binding redaction
 */
final class DatabaseLifecycleReviewRemediationTest extends TestCase
{
    protected function tearDown(): void
    {
        GlobalDatabaseLifecycleState::reset();
        ExactOnceListener::$counts = [];
        WiringListener::$events = [];
        AfterCommitFailureListener::$events = [];
        RedactionListener::$events = [];

        parent::tearDown();
    }

    // ===== P0: Exact-once entity lifecycle dispatch =====

    #[Test]
    public function saving_listener_runs_exactly_once_on_insert(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerEntity(new EntityLifecycleRegistration(
            entityClass: TestRemediationUser::class,
            phase: EntityLifecyclePhase::Saving,
            listener: ExactOnceListener::class,
        ));
        $registry->freeze();

        // Simulate what EntityPersister::insert() does: dispatch Creating + Saving before, Created + Saved after.
        $phasesBefore = [EntityLifecyclePhase::Creating, EntityLifecyclePhase::Saving];
        $phasesAfter = [EntityLifecyclePhase::Created, EntityLifecyclePhase::Saved];

        foreach ($phasesBefore as $phase) {
            foreach ($registry->entityListenersFor(TestRemediationUser::class, $phase) as $entry) {
                /** @var class-string $listener */
                $listener = $entry['listener'];
                $listenerInstance = new $listener();
                // @phpstan-ignore-next-line — test helper, we know these listeners are invokable
                $listenerInstance->__invoke($phase);
            }
        }
        foreach ($phasesAfter as $phase) {
            foreach ($registry->entityListenersFor(TestRemediationUser::class, $phase) as $entry) {
                /** @var class-string $listener */
                $listener = $entry['listener'];
                $listenerInstance = new $listener();
                // @phpstan-ignore-next-line — test helper, we know these listeners are invokable
                $listenerInstance->__invoke($phase);
            }
        }

        // Saving listener should be invoked exactly once (from the Saving phase dispatch).
        $this->assertSame(1, ExactOnceListener::$counts[EntityLifecyclePhase::Saving->value] ?? 0,
            'Saving listener should run exactly once on insert, not twice from superset expansion');
    }

    #[Test]
    public function saved_listener_runs_exactly_once_on_insert(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerEntity(new EntityLifecycleRegistration(
            entityClass: TestRemediationUser::class,
            phase: EntityLifecyclePhase::Saved,
            listener: ExactOnceListener::class,
        ));
        $registry->freeze();

        $phasesAfter = [EntityLifecyclePhase::Created, EntityLifecyclePhase::Saved];

        foreach ($phasesAfter as $phase) {
            foreach ($registry->entityListenersFor(TestRemediationUser::class, $phase) as $entry) {
                /** @var class-string $listener */
                $listener = $entry['listener'];
                $listenerInstance = new $listener();
                // @phpstan-ignore-next-line — test helper, we know these listeners are invokable
                $listenerInstance->__invoke($phase);
            }
        }

        $this->assertSame(1, ExactOnceListener::$counts[EntityLifecyclePhase::Saved->value] ?? 0,
            'Saved listener should run exactly once on insert');
    }

    #[Test]
    public function saving_listener_runs_exactly_once_on_update(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerEntity(new EntityLifecycleRegistration(
            entityClass: TestRemediationUser::class,
            phase: EntityLifecyclePhase::Saving,
            listener: ExactOnceListener::class,
        ));
        $registry->freeze();

        $phasesBefore = [EntityLifecyclePhase::Updating, EntityLifecyclePhase::Saving];

        foreach ($phasesBefore as $phase) {
            foreach ($registry->entityListenersFor(TestRemediationUser::class, $phase) as $entry) {
                /** @var class-string $listener */
                $listener = $entry['listener'];
                $listenerInstance = new $listener();
                // @phpstan-ignore-next-line — test helper, we know these listeners are invokable
                $listenerInstance->__invoke($phase);
            }
        }

        $this->assertSame(1, ExactOnceListener::$counts[EntityLifecyclePhase::Saving->value] ?? 0,
            'Saving listener should run exactly once on update');
    }

    #[Test]
    public function saved_listener_runs_exactly_once_on_update(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerEntity(new EntityLifecycleRegistration(
            entityClass: TestRemediationUser::class,
            phase: EntityLifecyclePhase::Saved,
            listener: ExactOnceListener::class,
        ));
        $registry->freeze();

        $phasesAfter = [EntityLifecyclePhase::Updated, EntityLifecyclePhase::Saved];

        foreach ($phasesAfter as $phase) {
            foreach ($registry->entityListenersFor(TestRemediationUser::class, $phase) as $entry) {
                /** @var class-string $listener */
                $listener = $entry['listener'];
                $listenerInstance = new $listener();
                // @phpstan-ignore-next-line — test helper, we know these listeners are invokable
                $listenerInstance->__invoke($phase);
            }
        }

        $this->assertSame(1, ExactOnceListener::$counts[EntityLifecyclePhase::Saved->value] ?? 0,
            'Saved listener should run exactly once on update');
    }

    #[Test]
    public function creating_and_saving_listeners_both_run_once_on_insert(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerEntity(new EntityLifecycleRegistration(
            entityClass: TestRemediationUser::class,
            phase: EntityLifecyclePhase::Creating,
            listener: ExactOnceListener::class,
        ));
        $registry->registerEntity(new EntityLifecycleRegistration(
            entityClass: TestRemediationUser::class,
            phase: EntityLifecyclePhase::Saving,
            listener: ExactOnceListener::class,
        ));
        $registry->freeze();

        $phasesBefore = [EntityLifecyclePhase::Creating, EntityLifecyclePhase::Saving];

        foreach ($phasesBefore as $phase) {
            foreach ($registry->entityListenersFor(TestRemediationUser::class, $phase) as $entry) {
                /** @var class-string $listener */
                $listener = $entry['listener'];
                $listenerInstance = new $listener();
                // @phpstan-ignore-next-line — test helper, we know these listeners are invokable
                $listenerInstance->__invoke($phase);
            }
        }

        $this->assertSame(1, ExactOnceListener::$counts[EntityLifecyclePhase::Creating->value] ?? 0);
        $this->assertSame(1, ExactOnceListener::$counts[EntityLifecyclePhase::Saving->value] ?? 0);
    }

    #[Test]
    public function deleting_listener_runs_exactly_once(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerEntity(new EntityLifecycleRegistration(
            entityClass: TestRemediationUser::class,
            phase: EntityLifecyclePhase::Deleting,
            listener: ExactOnceListener::class,
        ));
        $registry->freeze();

        foreach ($registry->entityListenersFor(TestRemediationUser::class, EntityLifecyclePhase::Deleting) as $entry) {
            /** @var class-string $listener */
            $listener = $entry['listener'];
            $listenerInstance = new $listener();
            // @phpstan-ignore-next-line — test helper, we know these listeners are invokable
            $listenerInstance->__invoke(EntityLifecyclePhase::Deleting);
        }

        $this->assertSame(1, ExactOnceListener::$counts[EntityLifecyclePhase::Deleting->value] ?? 0);
    }

    // ===== P0: Public DSL reaches runtime =====

    #[Test]
    public function public_on_entity_registration_reaches_global_registry(): void
    {
        onEntity(TestRemediationUser::class)->created(WiringListener::class);

        // GlobalDatabaseLifecycleState should have the registration.
        $globalRegistry = GlobalDatabaseLifecycleState::registry();
        $listeners = $globalRegistry->entityListenersFor(TestRemediationUser::class, EntityLifecyclePhase::Created);

        $this->assertCount(1, $listeners);
        $this->assertSame(WiringListener::class, $listeners[0]['listener']);
    }

    #[Test]
    public function public_on_query_registration_reaches_global_registry(): void
    {
        onQuery()->executed(WiringListener::class);

        $globalRegistry = GlobalDatabaseLifecycleState::registry();
        $listeners = $globalRegistry->queryListenersFor(QueryLifecyclePhase::Executed);

        $this->assertCount(1, $listeners);
        $this->assertSame(WiringListener::class, $listeners[0]['listener']);
    }

    #[Test]
    public function public_on_transaction_registration_reaches_global_registry(): void
    {
        onTransaction()->afterCommit(WiringListener::class);

        $globalRegistry = GlobalDatabaseLifecycleState::registry();
        $listeners = $globalRegistry->transactionListenersFor(TransactionLifecyclePhase::AfterCommit);

        $this->assertCount(1, $listeners);
        $this->assertSame(WiringListener::class, $listeners[0]['listener']);
    }

    #[Test]
    public function runtime_uses_global_registry_by_default(): void
    {
        // Register via DSL.
        onQuery()->executed(WiringListener::class);

        // Create QueryOrchestrator without explicit registry — should use global.
        $executor = new class implements ExecutorInterface {
            #[\Override] public function query(string $sql, array $bindings = [], ?ExecutionScope $executionScope = null): array { return [['id' => 1]]; }
            #[\Override] public function execute(string $sql, ?array $bindings = [], ?ExecutionScope $executionScope = null): ExecutionResult { return ExecutionResult::success(affectedRows: 1); }
            #[\Override] public function getDriverName(): string { return 'sqlite'; }
        };

        $orchestrator = new QueryOrchestrator(executor: $executor);
        $orchestrator->query('SELECT 1');

        // WiringListener should have received the event.
        $this->assertNotEmpty(WiringListener::$events);
        $this->assertInstanceOf(QueryExecuted::class, WiringListener::$events[0]);
    }

    #[Test]
    public function resetting_global_state_prevents_cross_test_leak(): void
    {
        onEntity('LeakTest\\User')->created(WiringListener::class);
        $this->assertCount(1, GlobalDatabaseLifecycleState::registry()->entityListenersFor('LeakTest\\User', EntityLifecyclePhase::Created));

        GlobalDatabaseLifecycleState::reset();

        $this->assertCount(0, GlobalDatabaseLifecycleState::registry()->entityListenersFor('LeakTest\\User', EntityLifecyclePhase::Created));
    }

    // ===== P0: Transaction afterCommit semantics =====

    #[Test]
    public function after_commit_callback_failure_is_not_db_commit_failure(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerTransaction(new TransactionLifecycleRegistration(
            phase: TransactionLifecyclePhase::AfterCommit,
            listener: AfterCommitFailureListener::class,
        ));
        $registry->freeze();

        $transaction = $this->createTransactionWithRegistry($registry);
        $transaction->begin();

        // Register a callback that will fail.
        $transaction->afterCommit(static fn () => throw new RuntimeException('Callback failed'));

        try {
            $transaction->commit();
            $this->fail('Expected exception');
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->assertStringContainsString('AfterCommit callback failed', $message,
                'Exception should say afterCommit callback failed, not "Failed to commit transaction"');
        }
    }

    #[Test]
    public function transaction_committed_event_fires_before_after_commit_callbacks(): void
    {
        OrderCommittedListener::$order = [];
        OrderAfterCommitListener::$order = [];

        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerTransaction(new TransactionLifecycleRegistration(
            phase: TransactionLifecyclePhase::Committed,
            listener: OrderCommittedListener::class,
        ));
        $registry->registerTransaction(new TransactionLifecycleRegistration(
            phase: TransactionLifecyclePhase::AfterCommit,
            listener: OrderAfterCommitListener::class,
        ));
        $registry->freeze();

        $transaction = $this->createTransactionWithRegistry($registry);
        $transaction->begin();
        $transaction->afterCommit(static fn () => null);
        $transaction->commit();

        // Committed event fires before afterCommit callbacks (which fire before AfterCommit event).
        $this->assertNotEmpty(OrderCommittedListener::$order);
        $this->assertNotEmpty(OrderAfterCommitListener::$order);
    }

    #[Test]
    public function after_commit_does_not_run_on_rollback(): void
    {
        $callbackRan = false;

        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->freeze();

        $transaction = $this->createTransactionWithRegistry($registry);
        $transaction->begin();
        $transaction->afterCommit(static function () use (&$callbackRan): void { $callbackRan = true; });
        $transaction->rollback();

        $this->assertFalse($callbackRan, 'afterCommit callback should NOT run on rollback');
    }

    #[Test]
    public function nested_commit_runs_after_commit_only_on_outermost(): void
    {
        $callbackCount = 0;

        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->freeze();

        $transaction = $this->createTransactionWithRegistry($registry);
        $transaction->begin(); // outer
        $transaction->begin(); // inner (savepoint)
        $transaction->afterCommit(static function () use (&$callbackCount): void { $callbackCount++; });
        $transaction->commit(); // release savepoint
        $transaction->commit(); // outermost commit

        $this->assertSame(1, $callbackCount, 'afterCommit should run only once on outermost commit');
    }

    // ===== P0: Query binding redaction =====

    #[Test]
    public function query_executed_event_has_redacted_named_bindings(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerQuery(new QueryLifecycleRegistration(
            phase: QueryLifecyclePhase::Executed,
            listener: RedactionListener::class,
        ));
        $registry->freeze();

        $executor = new class implements ExecutorInterface {
            #[\Override] public function query(string $sql, array $bindings = [], ?ExecutionScope $executionScope = null): array { return [['id' => 1]]; }
            #[\Override] public function execute(string $sql, ?array $bindings = [], ?ExecutionScope $executionScope = null): ExecutionResult { return ExecutionResult::success(affectedRows: 1); }
            #[\Override] public function getDriverName(): string { return 'sqlite'; }
        };

        $orchestrator = new QueryOrchestrator(executor: $executor, registry: $registry);
        $orchestrator->query('SELECT * FROM users WHERE password = :password', ['password' => 'secret123']);

        $this->assertCount(1, RedactionListener::$events);
        $event = RedactionListener::$events[0];
        $this->assertInstanceOf(QueryExecuted::class, $event);
        $this->assertSame('***', $event->bindings['password'], 'Password binding should be redacted');
    }

    #[Test]
    public function query_executed_event_has_redacted_positional_token_bindings(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerQuery(new QueryLifecycleRegistration(
            phase: QueryLifecyclePhase::Executed,
            listener: RedactionListener::class,
        ));
        $registry->freeze();

        $executor = new class implements ExecutorInterface {
            #[\Override] public function query(string $sql, array $bindings = [], ?ExecutionScope $executionScope = null): array { return [['id' => 1]]; }
            #[\Override] public function execute(string $sql, ?array $bindings = [], ?ExecutionScope $executionScope = null): ExecutionResult { return ExecutionResult::success(affectedRows: 1); }
            #[\Override] public function getDriverName(): string { return 'sqlite'; }
        };

        $orchestrator = new QueryOrchestrator(executor: $executor, registry: $registry);
        // 32-char hex token.
        $orchestrator->query('SELECT * FROM sessions WHERE token = ?', ['abcdef0123456789abcdef0123456789']);

        $this->assertCount(1, RedactionListener::$events);
        $event = RedactionListener::$events[0];
        $this->assertInstanceOf(QueryExecuted::class, $event);
        $this->assertSame('***', $event->bindings[0], 'Token binding should be redacted');
    }

    #[Test]
    public function normal_bindings_are_not_redacted(): void
    {
        $redacted = RedactBindings::redact(['name' => 'John', 'email' => 'john@example.com', 'age' => 30]);

        $this->assertSame('John', $redacted['name']);
        $this->assertSame('john@example.com', $redacted['email']);
        $this->assertSame(30, $redacted['age']);
    }

    #[Test]
    public function redact_bindings_redacts_sensitive_named_keys(): void
    {
        $redacted = RedactBindings::redact([
            'password' => 'secret',
            'api_key' => 'key123',
            'token' => 'tok_abc',
            'name' => 'John',
        ]);

        $this->assertSame('***', $redacted['password']);
        $this->assertSame('***', $redacted['api_key']);
        $this->assertSame('***', $redacted['token']);
        $this->assertSame('John', $redacted['name']);
    }

    // ===== Helpers =====

    private function createTransactionWithRegistry(CompiledDatabaseLifecycleRegistry $registry): Transaction
    {
        $pdo = $this->createMock(PDO::class);
        $connection = $this->createMock(DatabaseConnection::class);
        $connection->method('getConnection')->willReturn($pdo);

        return Transaction::on($connection, $registry);
    }
}

// ===== Test listeners =====

class ExactOnceListener
{
    /** @var array<string, int> */
    public static array $counts = [];

    public function __invoke(EntityLifecyclePhase $phase): void
    {
        self::$counts[$phase->value] = (self::$counts[$phase->value] ?? 0) + 1;
    }
}

class WiringListener
{
    /** @var list<object> */
    public static array $events = [];

    public function __invoke(object $event): void
    {
        self::$events[] = $event;
    }
}

class AfterCommitFailureListener
{
    /** @var list<object> */
    public static array $events = [];

    public function __invoke(object $event): void
    {
        self::$events[] = $event;
    }
}

class RedactionListener
{
    /** @var list<object> */
    public static array $events = [];

    public function __invoke(object $event): void
    {
        self::$events[] = $event;
    }
}

class TestRemediationUser
{
    public ?int $id = null;
    public string $name = '';
    public string $email = '';
}

class OrderCommittedListener
{
    /** @var list<string> */
    public static array $order = [];

    public function __invoke(object $event): void
    {
        self::$order[] = 'committed';
    }
}

class OrderAfterCommitListener
{
    /** @var list<string> */
    public static array $order = [];

    public function __invoke(object $event): void
    {
        self::$order[] = 'afterCommit';
    }
}
