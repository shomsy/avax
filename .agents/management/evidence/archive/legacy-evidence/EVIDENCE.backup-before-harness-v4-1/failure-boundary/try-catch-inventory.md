# Try/Catch Usage Inventory

Date: 2026-05-12
Scope: All production PHP files in framework/, components/, config/, examples/

## Classification Rules

| Classification                 | Meaning                                                                |
|--------------------------------|------------------------------------------------------------------------|
| LIFECYCLE_BOUNDARY_KEEP        | Boot, shutdown, request-scope boundaries                               |
| LOCAL_DOMAIN_RECOVERY_KEEP     | Domain-specific error recovery (transaction retry, migration wrapping) |
| EXTERNAL_ADAPTER_RECOVERY_KEEP | Wrapping external library exceptions (PDO, HTTP client)                |
| RESOURCE_CLEANUP_KEEP          | try/finally for resource release                                       |
| SHOULD_USE_FAILURE_BOUNDARY    | Framework-level error mapping that could use FailureBoundary           |
| TEST_ONLY                      | Test code only                                                         |
| FALSE_POSITIVE                 | Not a real try/catch                                                   |

## Inventory

| File                                                                                                             | Class/Method                     | Purpose                                         | Classification                 | Action                                                                                                               |
|------------------------------------------------------------------------------------------------------------------|----------------------------------|-------------------------------------------------|--------------------------------|----------------------------------------------------------------------------------------------------------------------|
| `framework/System/Flows/HandleIncomingHttp/HandleIncomingHttp.php:73`                                            | `handleInCurrentScope`           | Catches all Throwable, returns generic 500      | SHOULD_USE_FAILURE_BOUNDARY    | Defer — requires framework-level wiring of FailureBoundary as outer error handler. Middleware approach is preferred. |
| `components/DataStack/Database/System/Capabilities/Transactions/Transactions.php:65-77`                          | `transactionWithRetry`           | Retries on deadlock with backoff                | LOCAL_DOMAIN_RECOVERY_KEEP     | Keep — domain-specific retry logic for database deadlocks.                                                           |
| `components/DataStack/Database/System/Capabilities/Transactions/Transactions.php:99-114`                         | `transaction`                    | Rollback on failure, suppresses rollback errors | LOCAL_DOMAIN_RECOVERY_KEEP     | Keep — transaction lifecycle management.                                                                             |
| `components/DataStack/Database/System/Capabilities/Transactions/Transactions.php:177-181`                        | `executeCommitCallbacks`         | Logs callback failures, continues               | LOCAL_DOMAIN_RECOVERY_KEEP     | Keep — prevents one callback failure from blocking others.                                                           |
| `components/DataStack/Database/System/Capabilities/Transactions/Transactions.php:216-220`                        | `executeRollbackCallbacks`       | Logs callback failures, continues               | LOCAL_DOMAIN_RECOVERY_KEEP     | Keep — same rationale as commit callbacks.                                                                           |
| `components/DataStack/Database/System/Capabilities/Query/Execution/PDOExecutor.php:42-65`                        | `query`                          | Wraps PDO exceptions in QueryException          | EXTERNAL_ADAPTER_RECOVERY_KEEP | Keep — adapter boundary wrapping external library.                                                                   |
| `components/DataStack/Database/System/Capabilities/Query/Execution/PDOExecutor.php:86-110`                       | `execute`                        | Wraps PDO exceptions in QueryException          | EXTERNAL_ADAPTER_RECOVERY_KEEP | Keep — same as above.                                                                                                |
| `components/DataStack/Database/System/Capabilities/Query/Execution/PDOExecutor.php:156-162`                      | `resolveLastInsertId`            | Catches silently, returns null                  | EXTERNAL_ADAPTER_RECOVERY_KEEP | Keep — optional operation, safe to suppress.                                                                         |
| `components/DataStack/Database/System/Capabilities/Connections/OpenConnection/OpenConnection.php:36-53`          | `using`                          | Dispatches ConnectionFailed event, rethrows     | LOCAL_DOMAIN_RECOVERY_KEEP     | Keep — connection lifecycle with telemetry.                                                                          |
| `components/DataStack/Database/System/Capabilities/Connections/OpenConnection/BuildPhysicalConnection.php:34-60` | `from`                           | Wraps PDO exception in ConnectionFailure        | EXTERNAL_ADAPTER_RECOVERY_KEEP | Keep — adapter boundary.                                                                                             |
| `components/DataStack/Database/System/Capabilities/Migrations/RunMigrations/MigrationRunner.php:25-105`          | `up`, `runMigration`, `rollback` | Wraps migration exceptions                      | LOCAL_DOMAIN_RECOVERY_KEEP     | Keep — migration-specific error wrapping.                                                                            |
| `components/Operations/Concurrency/System/Foundation/ConcurrentTask.php:55-68`                                   | `execute`                        | Captures task failure, rethrows                 | LOCAL_DOMAIN_RECOVERY_KEEP     | Keep — fiber/task boundary.                                                                                          |
| `components/Operations/Concurrency/System/Capabilities/RunWithFibers/FiberTaskRuntime.php:128-132`               | `createTaskFiber`                | Captures fiber errors into array                | LOCAL_DOMAIN_RECOVERY_KEEP     | Keep — fiber scheduler boundary.                                                                                     |
| `framework/System/Flows/BootApplication/BootApplication.php:20-24`                                               | `boot`                           | Wraps in ApplicationBootFailed                  | LIFECYCLE_BOUNDARY_KEEP        | Keep — application boot failure wrapping.                                                                            |
| `framework/System/Capabilities/Runtime/Worker/WorkerLoop.php:54-66`                                              | `run` (try/finally)              | Guaranteed cleanup on exit                      | RESOURCE_CLEANUP_KEEP          | Keep — worker lifecycle guarantee.                                                                                   |
| `framework/System/Capabilities/StateReset/StateResetRegistry.php:27-32`                                          | `resetAll`                       | Collects failures, continues resetting          | LOCAL_DOMAIN_RECOVERY_KEEP     | Keep — prevents one reset failure from blocking others.                                                              |
| `components/DataStack/Database/System/Capabilities/Connections/RunWithConnection/RunWithConnection.php:46-50`    | `pool` (try/finally)             | Connection release                              | RESOURCE_CLEANUP_KEEP          | Keep — resource cleanup guarantee.                                                                                   |

## Summary

- **Total try/catch blocks found:** 17
- **Keep (legitimate boundaries):** 16
- **Should use FailureBoundary (deferred):** 1 — `HandleIncomingHttp` catch block
- **Mechanical removal needed:** 0

## Conclusion

No try/catch needs mechanical removal. Every instance serves a legitimate lifecycle, domain recovery, adapter boundary,
or resource cleanup purpose. The one candidate for FailureBoundary integration (`HandleIncomingHttp`) is deferred
because the preferred approach is middleware-level integration rather than modifying the framework flow directly.
