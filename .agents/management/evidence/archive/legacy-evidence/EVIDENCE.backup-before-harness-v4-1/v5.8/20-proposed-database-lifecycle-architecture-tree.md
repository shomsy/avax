# V5.8-20: Proposed Database Lifecycle Architecture Tree

**Date:** 2026-05-13
**Stage:** V5.8 Design Lock — Proposed Architecture Tree

## Canonical Owner

Per V5.8-03 Owner Decision: `components/DataStack/Database/`

## Proposed Structure

```
components/DataStack/Database/System/
  PublicSurface/
    functions.php                        ← onEntity(), onQuery(), onTransaction()

  Flows/
    RegisterEntityLifecycle/
      RegisterEntityLifecycle.php        ← register entity lifecycle listeners

    RegisterTransactionLifecycle/
      RegisterTransactionLifecycle.php   ← register transaction lifecycle listeners

    RegisterQueryLifecycle/
      RegisterQueryLifecycle.php         ← register query lifecycle listeners

    InvokeEntityLifecycleListener/
      InvokeEntityLifecycleListener.php  ← invoke entity lifecycle listener

    InvokeTransactionLifecycleListener/
      InvokeTransactionLifecycleListener.php  ← invoke transaction lifecycle listener

    InvokeQueryLifecycleListener/
      InvokeQueryLifecycleListener.php   ← invoke query lifecycle listener

  Capabilities/
    CompileDatabaseLifecycle/
      CompileDatabaseLifecycle.php       ← compile DSL registrations into registry

    ResolveEntityLifecycleListeners/
      ResolveEntityLifecycleListeners.php     ← resolve listeners for entity+phase

    ResolveQueryLifecycleListeners/
      ResolveQueryLifecycleListeners.php      ← resolve listeners for query phase

    ResolveTransactionLifecycleListeners/
      ResolveTransactionLifecycleListeners.php ← resolve listeners for transaction phase

    InvokeDatabaseLifecycleListener/
      InvokeDatabaseLifecycleListener.php  ← invoke a single lifecycle listener

    BufferAfterCommitActions/
      BufferAfterCommitActions.php       ← buffer afterCommit callbacks in transaction

    ClearRollbackActions/
      ClearRollbackActions.php           ← clear afterCommit on rollback

  Configuration/
    BuildDatabaseLifecycle.php           ← assemble and compile lifecycle registry

  Foundation/
    EntityLifecyclePhase.php             ← enum: Creating, Created, Updating, Updated, etc.
    QueryLifecyclePhase.php              ← enum: Executing, Executed, Slow, Failed
    TransactionLifecyclePhase.php        ← enum: Beginning, Committed, AfterCommit, etc.
    LifecycleSource.php                  ← enum: Dsl, Attribute, Configuration
    LifecycleExecutionMode.php           ← enum: Sync (Async is ROADMAP)
    EntityLifecycleRegistration.php      ← registration value object
    QueryLifecycleRegistration.php       ← registration value object
    TransactionLifecycleRegistration.php ← registration value object
    CompiledDatabaseLifecycleRegistry.php ← frozen compiled registry

  Foundation/LifecycleEvents/
    EntityCreating.php                   ← lifecycle event object
    EntityCreated.php
    EntityUpdating.php
    EntityUpdated.php
    EntitySaving.php
    EntitySaved.php
    EntityDeleting.php
    EntityDeleted.php
    EntityRestored.php
    FailedToSave.php
    FailedToDelete.php
    QueryExecuting.php
    QueryExecuted.php
    QueryFailed.php
    TransactionBeginning.php
    TransactionCommitted.php
    AfterCommit.php
    TransactionRolledBack.php
    AfterRollback.php
    TransactionFailed.php
    BulkOperationStarted.php             ← future bulk events
    BulkOperationCompleted.php
    BulkOperationFailed.php
```

## Rules

1. **PublicSurface receives** — DSL functions only.
2. **Flows execute** — register, invoke lifecycle listeners.
3. **Capabilities power** — compile, resolve, invoke, buffer, clear.
4. **Configuration assembles** — build the lifecycle registry.
5. **Foundation supports** — enums, registration objects, event objects, compiled registry.
6. **Adjust to actual canonical DB owner** — this is the proposed tree within `components/DataStack/Database/System/`.
7. **Do not invent folders if existing component shape differs.**

## Integration with Existing Structure

Existing `components/DataStack/Database/System/` structure:

- `Capabilities/Query/` — QueryBuilder
- `Capabilities/Connections/` — Connection management
- `Capabilities/Transactions/` — Transaction management (extend with afterCommit/afterRollback)
- `Capabilities/ORM/` — ORM (extend with entity lifecycle hooks)
- `Capabilities/Telemetry/` — Existing telemetry (evaluate for integration)
- `Capabilities/Migrations/` — Migrations

New additions:

- `Capabilities/Lifecycle/` — Lifecycle compilation, resolution, invocation
- `Flows/RegisterEntityLifecycle/`, `Flows/RegisterTransactionLifecycle/`, `Flows/RegisterQueryLifecycle/`
- `Flows/InvokeEntityLifecycleListener/`, etc.
- `Foundation/LifecycleEvents/` — Lifecycle event objects

## Next Allowed Action

V5.8-21 Implementation Stage Plan.
