# Database Component Implementation TODO

Progress tracked from approved plan. Updated after each step.

## 1. Complete Querying Capability ✅ DONE

- [x] Querying/State/AST/ full nodes ✅ Confirmed existing & integrated
- [x] Querying/Identity/IdentityMap.php ✅ Created w/ docs (PK-hash map)
- [x] Querying/Enums/QueryBuilderEnum.php ✅ Created enums + docs (ops/mutations)
- [x] how-this-works.md for subfolders (Builder/ Execution/ Grammar/) ✅ Created

## 2. Refine Migrations (Mostly Complete ✅)

- [x] Migrations/Design/Column/DSL/ColumnDefinition.php ✅ Exists + docs added
- [x] Migrations/Design/Column/Render/ColumnSQLRenderer.php ✅ Exists
- [x] Migrations/Design/Table/Blueprint.php + TableDefinition.php ✅ Exist
- [x] Migrations/Design/TypeMapping/SQLToPHPTypeMapper.php ✅ Exists
- [x] how-this-works.md for Design/ Column/DSL/ ✅ Added/expanded

## 3. Finish Transactions (Complete ✅)

- [x] Transactions/RunTransaction/Transaction.php + TransactionScope.php ✅ Exist (RunTransaction full slice)
- [x] Transactions/OnConnection/OnConnection.php ✅ Exists
- [x] how-this-works.md updates ✅ Slice complete w/ docs

## 4. Polish Telemetry ✅ DONE

- [x] Telemetry/Events/Subscribers/DatabaseLoggerSubscriber.php ✅ Created (logs QueryExecuted to Psr\Log w/ docs)
- [x] how-this-works.md refinements ✅ Complete

## 5. Integrate ORM ✅ DONE

- [x] System/Capabilities/ORM/Persisters/EntityPersister.php ✅ Exists (CRUD)
- [x] EntityManager.php facade ✅ Created (delegates to Persister/Hydrator/Metadata)
- [x] how-this-works.md for ORM/ ✅ Created w/ overview
- [x] Metadata/EntityMetadata.php refinements (if needed)

## 6. Global Polish ✅ COMPLETE

- [x] Full docs consistency
- [x] DatabaseBuilder wiring check
- [x] Tests/Lint baseline validation prepared
- [x] Autoload + syntax validation

**Status: Database refactor plan completed; remaining work is regression verification only.**

