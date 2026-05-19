# V5.8-02: Current Database Architecture Audit

**Date:** 2026-05-13
**Stage:** V5.8 Design Lock — Database Architecture Audit

## Scope

Full audit of all database/data/persistence code in `framework/`, `components/`, `tests/`, `docs/`, `EVIDENCE/`,
`examples/`, `labs/`.

## Audit Results

### Canonical Database Owner

| Area                     | File(s)                                                                                                                                                          | Current responsibility                                                                                             | Lifecycle hooks? | Transaction support?           | Event integration?                                | Tests            | Classification                                    |
|--------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------|------------------|--------------------------------|---------------------------------------------------|------------------|---------------------------------------------------|
| Query Builder            | `components/DataStack/Database/System/Capabilities/Query/Builder/QueryBuilder.php` + Concerns (11 traits) + Grammar (19 files) + State + Execution + Projections | Fluent immutable SQL builder with dialect compilation, safe bindings, soft deletes, CTEs, upsert, window functions | No               | Delegates to orchestrator      | No (telemetry via separate EventBus)              | YES (1333 lines) | CANONICAL_DATABASE_OWNER, QUERY_BUILDER           |
| Connection Pool          | `.../Connections/Pools/PdoConnectionPool.php`, `PdoPooledConnection.php`                                                                                         | PDO connection pool with min/max bounds, timeout                                                                   | No               | No                             | No                                                | No               | CANONICAL_DATABASE_OWNER                          |
| Connection Contract      | `.../Connections/Contracts/DatabaseConnection.php`                                                                                                               | Interface: getConnection, ping, beginTransaction, commit, rollBack, exec                                           | No               | YES (interface)                | No                                                | No               | CANONICAL_DATABASE_OWNER, TRANSACTION_OWNER       |
| Connection Manager       | `.../Connections/Connections.php`                                                                                                                                | Named connection resolution                                                                                        | No               | No                             | No                                                | No               | CANONICAL_DATABASE_OWNER                          |
| Connection Open          | `.../Connections/OpenConnection/OpenConnection.php`                                                                                                              | Opens DB connections                                                                                               | No               | No                             | YES (EventBus: ConnectionOpened/ConnectionFailed) | No               | DB_TELEMETRY_SOURCE                               |
| Transaction Manager      | `.../Transactions/RunTransaction/Transaction.php`                                                                                                                | Nested transactions with savepoints, RAII scope                                                                    | No               | YES (full nested + savepoints) | No                                                | No               | CANONICAL_DATABASE_OWNER, TRANSACTION_OWNER       |
| Transaction Runner       | `.../Transactions/RunTransaction/RunTransaction.php`                                                                                                             | Executes callbacks in transactions                                                                                 | No               | YES                            | No                                                | No               | TRANSACTION_OWNER                                 |
| Transaction OnConnection | `.../Transactions/OnConnection/OnConnection.php`                                                                                                                 | Resolves transaction manager per connection                                                                        | No               | YES                            | No                                                | No               | TRANSACTION_OWNER                                 |
| UnitOfWork               | `.../ORM/UnitOfWork/UnitOfWork.php`                                                                                                                              | Change tracking (new/dirty/removed), flush to persisters, identity map                                             | No               | Delegates to persister         | No                                                | No               | ORM_ENTITY_MAPPING, EXISTING_DB_LIFECYCLE_SOURCE  |
| IdentityMap              | `.../ORM/IdentityMap/IdentityMap.php`                                                                                                                            | Entity identity cache by class+ID                                                                                  | No               | No                             | No                                                | No               | ORM_ENTITY_MAPPING                                |
| EntityPersister          | `.../ORM/Persisters/EntityPersister.php`                                                                                                                         | Insert/update/delete/find/refresh via QueryBuilder + Hydrator                                                      | No               | No                             | No                                                | No               | ORM_ENTITY_MAPPING                                |
| ORM Metadata             | `.../ORM/Metadata/` (AttributeMetadataReader, FieldMetadata, EntityMetadata)                                                                                     | Attribute-based entity metadata reading                                                                            | No               | No                             | No                                                | No               | ORM_ENTITY_MAPPING                                |
| ORM Hydrator             | `.../ORM/Hydration/Hydrator.php`                                                                                                                                 | Hydrates entities from query rows                                                                                  | No               | No                             | No                                                | No               | ORM_ENTITY_MAPPING                                |
| Migrations Design        | `.../Migrations/Design/Migration.php`, `Blueprint.php`                                                                                                           | Abstract migration + fluent schema DSL                                                                             | YES (up/down)    | Uses QueryBuilder              | No                                                | No               | MIGRATION_OWNER                                   |
| Migrations Runner        | `.../Migrations/RunMigrations/`, `RollbackMigrations/`, `ReadMigrationStatus/`, `CreateMigration/`                                                               | Migration batch execution, rollback, status, generation                                                            | No               | Unknown                        | No                                                | No               | MIGRATION_OWNER                                   |
| Telemetry EventBus       | `.../Telemetry/Events/EventBus.php` + Events + Trackers                                                                                                          | DB lifecycle signal dispatcher (ConnectionOpened, QueryExecuted, etc.)                                             | No               | No                             | YES (own isolated event system)                   | No               | DB_TELEMETRY_SOURCE, EXISTING_DB_LIFECYCLE_SOURCE |

### Related Components

| Area                     | File(s)                                                                                                                                | Current responsibility                                                 | Lifecycle hooks? | Transaction support?          | Event integration? | Tests | Classification                                         |
|--------------------------|----------------------------------------------------------------------------------------------------------------------------------------|------------------------------------------------------------------------|------------------|-------------------------------|--------------------|-------|--------------------------------------------------------|
| DataTransfer             | `components/DataStack/DataTransfer/` (67 files)                                                                                        | Data object serialization, field mapping, value conversion, validation | No               | No                            | No                 | No    | CANONICAL_DATABASE_OWNER                               |
| Persistence Flows        | `.../Persistence/System/Flows/` (FindEntity, SaveEntity, DeleteEntity, FlushChanges, RunUnitOfWork, BuildDataQuery, etc.)              | Command-style persistence operations                                   | No               | Unknown                       | Unknown            | No    | CANONICAL_DATABASE_OWNER, EXISTING_DB_LIFECYCLE_SOURCE |
| Persistence Capabilities | `.../Persistence/System/Capabilities/` (Hydration, UnitOfWork, Consistency, Diagnostics, IdentityMap, QueryIntent, Repositories, etc.) | Persistence domain capabilities                                        | No               | Unknown                       | Unknown            | No    | CANONICAL_DATABASE_OWNER                               |
| Persistence Public API   | `.../Persistence/CommitDataChanges.php`, `ConfigureDataLayer.php`, `AccessPersistentData.php`                                          | Public surface for persistence                                         | No               | Unknown                       | Unknown            | No    | CANONICAL_DATABASE_OWNER                               |
| QueryGovernance          | `.../QueryGovernance/` (4 files + Detection)                                                                                           | Query governance and detection                                         | No               | No                            | Unknown            | No    | DB_TELEMETRY_SOURCE                                    |
| Database Queue Driver    | `components/Operations/Queue/.../DatabaseQueue/DatabaseQueue.php` + Schema                                                             | PDO-backed queue with dead letter, uses transactions internally        | No               | YES (internal for deadLetter) | No                 | YES   | OUTBOX_CANDIDATE                                       |
| MessageBus Envelope      | `components/Operations/MessageBus/.../Envelope/MessageEnvelope.php`                                                                    | Message wrapper with routing/tracing metadata                          | No               | No                            | No                 | No    | EVENT_STORE_CANDIDATE                                  |
| MessageBus Projection    | `.../MessageBus/.../Projection/Projection.php`                                                                                         | Event projection: transforms envelopes into read model state           | No               | No                            | No                 | No    | PROJECTION_CANDIDATE                                   |

## Key Findings

1. **Canonical DB owner**: `components/DataStack/Database/` — ~380 files, well-structured
2. **Transaction support**: Full nested transactions with savepoints exist in `Transaction.php`
3. **ORM exists**: UnitOfWork, IdentityMap, EntityPersister, Metadata, Hydrator — complete
4. **Lifecycle hooks do NOT exist**: No beforeSave/afterSave/afterCommit/afterRollback anywhere
5. **Telemetry EventBus is isolated**: `components/DataStack/Database/System/Telemetry/Events/EventBus.php` is a
   separate event dispatcher, NOT integrated with canonical `components/Operations/Events/`
6. **Persistence flows exist**: SaveEntity, DeleteEntity, FlushChanges etc. in DataStack/DataTransfer
7. **Outbox candidate**: DatabaseQueue driver already uses transactions internally
8. **Projection candidate**: MessageBus has Projection capability, Database has Query Projections
9. **No EventStore**: No dedicated event store implementation exists

## Classification Summary

| Classification               | Count    | Key files                                                    |
|------------------------------|----------|--------------------------------------------------------------|
| CANONICAL_DATABASE_OWNER     | Primary  | `components/DataStack/Database/`                             |
| QUERY_BUILDER                | Complete | QueryBuilder.php + 11 concerns + 19 grammars                 |
| ORM_ENTITY_MAPPING           | Complete | UnitOfWork, IdentityMap, EntityPersister, Metadata, Hydrator |
| TRANSACTION_OWNER            | Complete | Transaction.php, RunTransaction.php, OnConnection.php        |
| MIGRATION_OWNER              | Complete | Migration.php, Blueprint.php, runners                        |
| DB_TELEMETRY_SOURCE          | Present  | EventBus.php, OpenConnection.php, Trackers                   |
| EXISTING_DB_LIFECYCLE_SOURCE | Partial  | UnitOfWork, Persistence Flows, Telemetry EventBus            |
| OUTBOX_CANDIDATE             | Present  | DatabaseQueue                                                |
| PROJECTION_CANDIDATE         | Present  | MessageBus Projection, Query Projections                     |
| EVENT_STORE_CANDIDATE        | Present  | MessageEnvelope (infrastructure only)                        |
| LEGACY_DUPLICATE             | 0        | No duplicates found                                          |
| LABS_ONLY                    | 0        | All DB code is production components                         |
| TEST_FIXTURE                 | Minimal  | Queue tests exist, DB tests mostly QueryBuilder only         |
| UNKNOWN_NEEDS_REVIEW         | Low      | Some connection pool/ORM tests missing                       |

## Next Allowed Action

V5.8-03 Canonical Owner Decision.
