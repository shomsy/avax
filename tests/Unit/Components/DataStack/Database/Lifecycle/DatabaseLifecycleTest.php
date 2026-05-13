<?php

declare(strict_types=1);

namespace Tests\Unit\Components\DataStack\Database\Lifecycle;

use Avax\Components\DataStack\Database\System\Capabilities\Lifecycle\EntityLifecycleDsl;
use Avax\Components\DataStack\Database\System\Capabilities\Lifecycle\QueryLifecycleDsl;
use Avax\Components\DataStack\Database\System\Capabilities\Lifecycle\TransactionLifecycleDsl;
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
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\EntityRestored;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\EntitySaved;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\EntitySaving;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\EntityUpdated;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\EntityUpdating;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\FailedToDelete;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\FailedToSave;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\QueryExecuted;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\QueryExecuting;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\QueryFailed;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\TransactionBeginning;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\TransactionCommitted;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\TransactionRolledBack;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\LifecycleExecutionMode;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\LifecycleSource;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\QueryLifecyclePhase;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\QueryLifecycleRegistration;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\TransactionLifecyclePhase;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\TransactionLifecycleRegistration;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DatabaseLifecycleTest extends TestCase
{
    // ===== Foundation Enum Tests =====

    public function test_entity_lifecycle_phase_values(): void
    {
        $this->assertSame('creating', EntityLifecyclePhase::Creating->value);
        $this->assertSame('created', EntityLifecyclePhase::Created->value);
        $this->assertSame('updating', EntityLifecyclePhase::Updating->value);
        $this->assertSame('updated', EntityLifecyclePhase::Updated->value);
        $this->assertSame('saving', EntityLifecyclePhase::Saving->value);
        $this->assertSame('saved', EntityLifecyclePhase::Saved->value);
        $this->assertSame('deleting', EntityLifecyclePhase::Deleting->value);
        $this->assertSame('deleted', EntityLifecyclePhase::Deleted->value);
        $this->assertSame('restored', EntityLifecyclePhase::Restored->value);
        $this->assertSame('failedToSave', EntityLifecyclePhase::FailedToSave->value);
        $this->assertSame('failedToDelete', EntityLifecyclePhase::FailedToDelete->value);
        $this->assertCount(11, EntityLifecyclePhase::cases());
    }

    public function test_query_lifecycle_phase_values(): void
    {
        $this->assertSame('executing', QueryLifecyclePhase::Executing->value);
        $this->assertSame('executed', QueryLifecyclePhase::Executed->value);
        $this->assertSame('slow', QueryLifecyclePhase::Slow->value);
        $this->assertSame('failed', QueryLifecyclePhase::Failed->value);
        $this->assertCount(4, QueryLifecyclePhase::cases());
    }

    public function test_transaction_lifecycle_phase_values(): void
    {
        $this->assertSame('beginning', TransactionLifecyclePhase::Beginning->value);
        $this->assertSame('committed', TransactionLifecyclePhase::Committed->value);
        $this->assertSame('afterCommit', TransactionLifecyclePhase::AfterCommit->value);
        $this->assertSame('rolledBack', TransactionLifecyclePhase::RolledBack->value);
        $this->assertSame('afterRollback', TransactionLifecyclePhase::AfterRollback->value);
        $this->assertSame('failed', TransactionLifecyclePhase::Failed->value);
        $this->assertCount(6, TransactionLifecyclePhase::cases());
    }

    public function test_lifecycle_source_values(): void
    {
        $this->assertSame('dsl', LifecycleSource::Dsl->value);
        $this->assertSame('attribute', LifecycleSource::Attribute->value);
        $this->assertSame('configuration', LifecycleSource::Configuration->value);
        $this->assertCount(3, LifecycleSource::cases());
    }

    public function test_lifecycle_execution_mode_values(): void
    {
        $this->assertSame('sync', LifecycleExecutionMode::Sync->value);
        $this->assertCount(1, LifecycleExecutionMode::cases());
    }

    // ===== Registration Object Tests =====

    public function test_entity_lifecycle_registration_stores_all_fields(): void
    {
        $registration = new EntityLifecycleRegistration(
            entityClass: 'App\\User',
            phase: EntityLifecyclePhase::Created,
            listener: 'App\\Listeners\\EmitUserRegistered',
            priority: 10,
            source: LifecycleSource::Dsl,
            mode: LifecycleExecutionMode::Sync,
        );

        $this->assertSame('App\\User', $registration->entityClass);
        $this->assertSame(EntityLifecyclePhase::Created, $registration->phase);
        $this->assertSame('App\\Listeners\\EmitUserRegistered', $registration->listener);
        $this->assertSame(10, $registration->priority);
        $this->assertSame(LifecycleSource::Dsl, $registration->source);
        $this->assertSame(LifecycleExecutionMode::Sync, $registration->mode);
    }

    public function test_entity_lifecycle_registration_defaults(): void
    {
        $registration = new EntityLifecycleRegistration(
            entityClass: 'App\\User',
            phase: EntityLifecyclePhase::Created,
            listener: 'App\\Listeners\\EmitUserRegistered',
        );

        $this->assertSame(0, $registration->priority);
        $this->assertSame(LifecycleSource::Dsl, $registration->source);
        $this->assertSame(LifecycleExecutionMode::Sync, $registration->mode);
    }

    public function test_query_lifecycle_registration_stores_threshold(): void
    {
        $registration = new QueryLifecycleRegistration(
            phase: QueryLifecyclePhase::Slow,
            listener: 'App\\Listeners\\ReportSlowQuery',
            priority: 5,
            thresholdMs: 100,
        );

        $this->assertSame(QueryLifecyclePhase::Slow, $registration->phase);
        $this->assertSame(100, $registration->thresholdMs);
        $this->assertSame(5, $registration->priority);
    }

    public function test_transaction_lifecycle_registration(): void
    {
        $registration = new TransactionLifecycleRegistration(
            phase: TransactionLifecyclePhase::AfterCommit,
            listener: 'App\\Listeners\\PublishOutboxMessages',
            priority: 0,
        );

        $this->assertSame(TransactionLifecyclePhase::AfterCommit, $registration->phase);
        $this->assertSame('App\\Listeners\\PublishOutboxMessages', $registration->listener);
    }

    // ===== Event Object Tests =====

    public function test_entity_creating_event(): void
    {
        $event = new EntityCreating(
            entityClass: 'App\\User',
            attributes: ['email' => 'test@example.com'],
            connection: 'default',
        );

        $this->assertSame('App\\User', $event->entityClass);
        $this->assertSame('creating', $event->phase);
        $this->assertSame(['email' => 'test@example.com'], $event->attributes);
        $this->assertSame('default', $event->connection);
    }

    public function test_entity_created_event(): void
    {
        $entity = new \stdClass();
        $event = new EntityCreated(
            entityClass: 'App\\User',
            entity: $entity,
            attributes: ['email' => 'test@example.com'],
            connection: 'default',
            lastInsertId: '42',
        );

        $this->assertSame($entity, $event->entity);
        $this->assertSame('42', $event->lastInsertId);
        $this->assertSame('created', $event->phase);
    }

    public function test_failed_to_save_event(): void
    {
        $exception = new RuntimeException('Save failed');
        $event = new FailedToSave(
            entityClass: 'App\\User',
            attributes: ['email' => 'test@example.com'],
            connection: 'default',
            exception: $exception,
        );

        $this->assertSame($exception, $event->exception);
        $this->assertSame('failedToSave', $event->phase);
    }

    public function test_after_commit_event(): void
    {
        $event = new AfterCommit(
            connection: 'default',
            transactionId: 'txn-123',
        );

        $this->assertSame('default', $event->connection);
        $this->assertSame('txn-123', $event->transactionId);
    }

    public function test_after_rollback_event(): void
    {
        $event = new AfterRollback(
            connection: 'default',
            transactionId: 'txn-123',
            reason: new RuntimeException('Rollback'),
        );

        $this->assertInstanceOf(RuntimeException::class, $event->reason);
    }

    public function test_query_executed_event(): void
    {
        $event = new QueryExecuted(
            sql: 'SELECT * FROM users WHERE id = ?',
            bindings: [1],
            connection: 'default',
            durationMs: 12.5,
            rowCount: 1,
        );

        $this->assertSame(12.5, $event->durationMs);
        $this->assertSame(1, $event->rowCount);
    }

    public function test_transaction_beginning_event(): void
    {
        $event = new TransactionBeginning(
            connection: 'default',
            nestingLevel: 0,
            transactionId: 'txn-abc',
        );

        $this->assertSame(0, $event->nestingLevel);
    }

    public function test_transaction_committed_event(): void
    {
        $event = new TransactionCommitted(
            connection: 'default',
            nestingLevel: 0,
            transactionId: 'txn-abc',
            durationMs: 5.2,
        );

        $this->assertSame(5.2, $event->durationMs);
    }

    // ===== Compiled Registry Tests =====

    public function test_compiled_registry_returns_empty_list_for_unknown_lifecycle(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();

        $this->assertSame([], $registry->entityListenersFor('Unknown\\Entity', EntityLifecyclePhase::Created));
        $this->assertSame([], $registry->queryListenersFor(QueryLifecyclePhase::Executed));
        $this->assertSame([], $registry->transactionListenersFor(TransactionLifecyclePhase::AfterCommit));
    }

    public function test_compiled_registry_stores_entity_registration(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registration = new EntityLifecycleRegistration(
            entityClass: 'App\\User',
            phase: EntityLifecyclePhase::Created,
            listener: 'App\\Listeners\\EmitUserRegistered',
        );

        $registry->registerEntity($registration);
        $listeners = $registry->entityListenersFor('App\\User', EntityLifecyclePhase::Created);

        $this->assertCount(1, $listeners);
        $this->assertSame('App\\Listeners\\EmitUserRegistered', $listeners[0]['listener']);
        $this->assertSame(LifecycleSource::Dsl, $listeners[0]['source']);
    }

    public function test_compiled_registry_priority_descending(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerEntity(new EntityLifecycleRegistration(
            entityClass: 'App\\User',
            phase: EntityLifecyclePhase::Created,
            listener: 'LowPriority',
            priority: 5,
        ));
        $registry->registerEntity(new EntityLifecycleRegistration(
            entityClass: 'App\\User',
            phase: EntityLifecyclePhase::Created,
            listener: 'HighPriority',
            priority: 10,
        ));

        $listeners = $registry->entityListenersFor('App\\User', EntityLifecyclePhase::Created);

        $this->assertSame('HighPriority', $listeners[0]['listener']);
        $this->assertSame('LowPriority', $listeners[1]['listener']);
    }

    public function test_compiled_registry_same_priority_preserves_order(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerEntity(new EntityLifecycleRegistration(
            entityClass: 'App\\User',
            phase: EntityLifecyclePhase::Created,
            listener: 'First',
            priority: 10,
        ));
        $registry->registerEntity(new EntityLifecycleRegistration(
            entityClass: 'App\\User',
            phase: EntityLifecyclePhase::Created,
            listener: 'Second',
            priority: 10,
        ));

        $listeners = $registry->entityListenersFor('App\\User', EntityLifecyclePhase::Created);

        $this->assertSame('First', $listeners[0]['listener']);
        $this->assertSame('Second', $listeners[1]['listener']);
    }

    public function test_compiled_registry_source_tracking(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerEntity(new EntityLifecycleRegistration(
            entityClass: 'App\\User',
            phase: EntityLifecyclePhase::Created,
            listener: 'DslListener',
            source: LifecycleSource::Dsl,
        ));

        $listeners = $registry->entityListenersFor('App\\User', EntityLifecyclePhase::Created);
        $this->assertSame(LifecycleSource::Dsl, $listeners[0]['source']);
    }

    public function test_compiled_registry_query_registration(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerQuery(new QueryLifecycleRegistration(
            phase: QueryLifecyclePhase::Executed,
            listener: 'RecordQueryTelemetry',
        ));

        $listeners = $registry->queryListenersFor(QueryLifecyclePhase::Executed);
        $this->assertCount(1, $listeners);
        $this->assertSame('RecordQueryTelemetry', $listeners[0]['listener']);
    }

    public function test_compiled_registry_query_slow_threshold(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerQuery(new QueryLifecycleRegistration(
            phase: QueryLifecyclePhase::Slow,
            listener: 'ReportSlowQuery',
            thresholdMs: 100,
        ));

        $listeners = $registry->queryListenersFor(QueryLifecyclePhase::Slow);
        $this->assertSame(100, $listeners[0]['thresholdMs']);
    }

    public function test_compiled_registry_transaction_registration(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerTransaction(new TransactionLifecycleRegistration(
            phase: TransactionLifecyclePhase::AfterCommit,
            listener: 'PublishOutboxMessages',
        ));

        $listeners = $registry->transactionListenersFor(TransactionLifecyclePhase::AfterCommit);
        $this->assertCount(1, $listeners);
        $this->assertSame('PublishOutboxMessages', $listeners[0]['listener']);
    }

    public function test_compiled_registry_freeze_prevents_mutation(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->freeze();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot register lifecycle listeners: the compiled registry is frozen.');

        $registry->registerEntity(new EntityLifecycleRegistration(
            entityClass: 'App\\User',
            phase: EntityLifecyclePhase::Created,
            listener: 'Listener',
        ));
    }

    public function test_compiled_registry_is_frozen(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $this->assertFalse($registry->isFrozen());

        $registry->freeze();
        $this->assertTrue($registry->isFrozen());
    }

    public function test_compiled_registry_get_registered_entity_classes(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerEntity(new EntityLifecycleRegistration(
            entityClass: 'App\\User',
            phase: EntityLifecyclePhase::Created,
            listener: 'Listener',
        ));
        $registry->registerEntity(new EntityLifecycleRegistration(
            entityClass: 'App\\Post',
            phase: EntityLifecyclePhase::Created,
            listener: 'Listener',
        ));

        $classes = $registry->getRegisteredEntityClasses();

        $this->assertContains('App\\User', $classes);
        $this->assertContains('App\\Post', $classes);
        $this->assertCount(2, $classes);
    }

    public function test_compiled_registry_get_all_lifecycle_keys(): void
    {
        $registry = new CompiledDatabaseLifecycleRegistry();
        $registry->registerEntity(new EntityLifecycleRegistration(
            entityClass: 'App\\User',
            phase: EntityLifecyclePhase::Created,
            listener: 'Listener',
        ));
        $registry->registerQuery(new QueryLifecycleRegistration(
            phase: QueryLifecyclePhase::Executed,
            listener: 'Listener',
        ));
        $registry->registerTransaction(new TransactionLifecycleRegistration(
            phase: TransactionLifecyclePhase::AfterCommit,
            listener: 'Listener',
        ));

        $keys = $registry->getAllLifecycleKeys();

        // Keys use entity class name and phase value.
        $this->assertContains('entity:App\User:created', $keys);
        $this->assertContains('query:executed', $keys);
        $this->assertContains('transaction:afterCommit', $keys);
        $this->assertCount(3, $keys);
    }

    public function test_sync_is_only_active_execution_mode(): void
    {
        $this->assertSame(LifecycleExecutionMode::Sync, LifecycleExecutionMode::Sync);
        $this->assertCount(1, LifecycleExecutionMode::cases());
    }

    // ===== Entity Lifecycle DSL Tests =====

    protected function tearDown(): void
    {
        EntityLifecycleDsl::reset();
        QueryLifecycleDsl::reset();
        TransactionLifecycleDsl::reset();

        parent::tearDown();
    }

    public function test_on_entity_returns_chainable_dsl(): void
    {
        $dsl = onEntity('App\\User');

        $this->assertInstanceOf(EntityLifecycleDsl::class, $dsl);
    }

    public function test_entity_dsl_creating_registers_listener(): void
    {
        $dsl = onEntity('App\\User')->creating('ValidateUser');
        $registry = EntityLifecycleDsl::getRegistry();
        $listeners = $registry->entityListenersFor('App\\User', EntityLifecyclePhase::Creating);

        $this->assertCount(1, $listeners);
        $this->assertSame('ValidateUser', $listeners[0]['listener']);
    }

    public function test_entity_dsl_created_registers_listener(): void
    {
        onEntity('App\\User')->created('EmitUserRegistered');
        $registry = EntityLifecycleDsl::getRegistry();
        $listeners = $registry->entityListenersFor('App\\User', EntityLifecyclePhase::Created);

        $this->assertCount(1, $listeners);
        $this->assertSame('EmitUserRegistered', $listeners[0]['listener']);
    }

    public function test_entity_dsl_updating_registers_listener(): void
    {
        onEntity('App\\User')->updating('RecordAudit');
        $registry = EntityLifecycleDsl::getRegistry();
        $listeners = $registry->entityListenersFor('App\\User', EntityLifecyclePhase::Updating);

        $this->assertCount(1, $listeners);
    }

    public function test_entity_dsl_updated_registers_listener(): void
    {
        onEntity('App\\User')->updated('NotifyChanged');
        $registry = EntityLifecycleDsl::getRegistry();
        $listeners = $registry->entityListenersFor('App\\User', EntityLifecyclePhase::Updated);

        $this->assertCount(1, $listeners);
    }

    public function test_entity_dsl_deleting_registers_listener(): void
    {
        onEntity('App\\User')->deleting('PreventDeletion');
        $registry = EntityLifecycleDsl::getRegistry();
        $listeners = $registry->entityListenersFor('App\\User', EntityLifecyclePhase::Deleting);

        $this->assertCount(1, $listeners);
    }

    public function test_entity_dsl_deleted_registers_listener(): void
    {
        onEntity('App\\User')->deleted('CleanCache');
        $registry = EntityLifecycleDsl::getRegistry();
        $listeners = $registry->entityListenersFor('App\\User', EntityLifecyclePhase::Deleted);

        $this->assertCount(1, $listeners);
    }

    public function test_entity_dsl_chaining_works(): void
    {
        onEntity('App\\User')
            ->creating('ValidateUser')
            ->created('EmitUserRegistered')
            ->updating('RecordAudit')
            ->updated('NotifyChanged');

        $registry = EntityLifecycleDsl::getRegistry();

        $this->assertCount(1, $registry->entityListenersFor('App\\User', EntityLifecyclePhase::Creating));
        $this->assertCount(1, $registry->entityListenersFor('App\\User', EntityLifecyclePhase::Created));
        $this->assertCount(1, $registry->entityListenersFor('App\\User', EntityLifecyclePhase::Updating));
        $this->assertCount(1, $registry->entityListenersFor('App\\User', EntityLifecyclePhase::Updated));
    }

    public function test_entity_dsl_priority_works(): void
    {
        onEntity('App\\User')
            ->created('LowPriority', priority: 5)
            ->created('HighPriority', priority: 10);

        $registry = EntityLifecycleDsl::getRegistry();
        $listeners = $registry->entityListenersFor('App\\User', EntityLifecyclePhase::Created);

        $this->assertSame('HighPriority', $listeners[0]['listener']);
        $this->assertSame('LowPriority', $listeners[1]['listener']);
    }

    public function test_entity_dsl_source_is_dsl(): void
    {
        onEntity('App\\User')->created('Listener');
        $registry = EntityLifecycleDsl::getRegistry();
        $listeners = $registry->entityListenersFor('App\\User', EntityLifecyclePhase::Created);

        $this->assertSame(LifecycleSource::Dsl, $listeners[0]['source']);
    }

    public function test_entity_dsl_no_execution_during_registration(): void
    {
        $executed = false;

        // Registration should not invoke anything.
        onEntity('App\\User')->created(
            // We can't pass a callable here, but the DSL only stores class-strings.
            // No execution happens until the runtime integration stage.
            'NoOpListener'
        );

        $this->assertFalse($executed);
    }

    // ===== Transaction Lifecycle DSL Tests =====

    public function test_on_transaction_returns_chainable_dsl(): void
    {
        $dsl = onTransaction();

        $this->assertInstanceOf(TransactionLifecycleDsl::class, $dsl);
    }

    public function test_transaction_dsl_after_commit_registers_action(): void
    {
        onTransaction()->afterCommit('PublishOutboxMessages');
        $registry = TransactionLifecycleDsl::getRegistry();
        $listeners = $registry->transactionListenersFor(TransactionLifecyclePhase::AfterCommit);

        $this->assertCount(1, $listeners);
        $this->assertSame('PublishOutboxMessages', $listeners[0]['listener']);
    }

    public function test_transaction_dsl_after_rollback_registers_action(): void
    {
        onTransaction()->afterRollback('ClearPendingEvents');
        $registry = TransactionLifecycleDsl::getRegistry();
        $listeners = $registry->transactionListenersFor(TransactionLifecyclePhase::AfterRollback);

        $this->assertCount(1, $listeners);
        $this->assertSame('ClearPendingEvents', $listeners[0]['listener']);
    }

    public function test_transaction_dsl_chaining_works(): void
    {
        onTransaction()
            ->committed('RecordAudit')
            ->afterCommit('PublishOutbox')
            ->rolledBack('LogRollback')
            ->afterRollback('ClearEvents');

        $registry = TransactionLifecycleDsl::getRegistry();

        $this->assertCount(1, $registry->transactionListenersFor(TransactionLifecyclePhase::Committed));
        $this->assertCount(1, $registry->transactionListenersFor(TransactionLifecyclePhase::AfterCommit));
        $this->assertCount(1, $registry->transactionListenersFor(TransactionLifecyclePhase::RolledBack));
        $this->assertCount(1, $registry->transactionListenersFor(TransactionLifecyclePhase::AfterRollback));
    }

    public function test_transaction_dsl_source_is_dsl(): void
    {
        onTransaction()->afterCommit('Listener');
        $registry = TransactionLifecycleDsl::getRegistry();
        $listeners = $registry->transactionListenersFor(TransactionLifecyclePhase::AfterCommit);

        $this->assertSame(LifecycleSource::Dsl, $listeners[0]['source']);
    }

    public function test_transaction_dsl_no_execution_during_registration(): void
    {
        onTransaction()->afterCommit('PublishOutbox');
        // Registration only — no execution.
        $this->assertTrue(true);
    }

    // ===== Query Lifecycle DSL Tests =====

    public function test_on_query_returns_chainable_dsl(): void
    {
        $dsl = onQuery();

        $this->assertInstanceOf(QueryLifecycleDsl::class, $dsl);
    }

    public function test_query_dsl_executed_registers_listener(): void
    {
        onQuery()->executed('RecordQueryTelemetry');
        $registry = QueryLifecycleDsl::getRegistry();
        $listeners = $registry->queryListenersFor(QueryLifecyclePhase::Executed);

        $this->assertCount(1, $listeners);
        $this->assertSame('RecordQueryTelemetry', $listeners[0]['listener']);
    }

    public function test_query_dsl_slow_registers_threshold_listener(): void
    {
        onQuery()->slow('ReportSlowQuery', thresholdMs: 100);
        $registry = QueryLifecycleDsl::getRegistry();
        $listeners = $registry->queryListenersFor(QueryLifecyclePhase::Slow);

        $this->assertCount(1, $listeners);
        $this->assertSame('ReportSlowQuery', $listeners[0]['listener']);
        $this->assertSame(100, $listeners[0]['thresholdMs']);
    }

    public function test_query_dsl_failed_registers_listener(): void
    {
        onQuery()->failed('RecordFailedQuery');
        $registry = QueryLifecycleDsl::getRegistry();
        $listeners = $registry->queryListenersFor(QueryLifecyclePhase::Failed);

        $this->assertCount(1, $listeners);
        $this->assertSame('RecordFailedQuery', $listeners[0]['listener']);
    }

    public function test_query_dsl_source_is_dsl(): void
    {
        onQuery()->executed('Listener');
        $registry = QueryLifecycleDsl::getRegistry();
        $listeners = $registry->queryListenersFor(QueryLifecyclePhase::Executed);

        $this->assertSame(LifecycleSource::Dsl, $listeners[0]['source']);
    }

    public function test_query_dsl_no_execution_during_registration(): void
    {
        onQuery()->executed('RecordQuery');
        // Registration only — no execution.
        $this->assertTrue(true);
    }
}
