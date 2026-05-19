# V1-D3 Restored Files Map - DataStack/Database

## DataStack/Database (291 files)

```
components/DataStack/Database/System/
├── PublicSurface/
│   ├── Database.php ✓
│   ├── DatabaseInterface.php ✓
│   ├── Query.php ✓
│   ├── Schema.php ✓
│   ├── SchemaBuilder.php ✓
│   ├── Migrations.php ✓
│   ├── EntityManager.php ✓
│   ├── Entities.php ✓
│   ├── Transactions.php ✓
│   ├── Telemetry.php ✓
│   └── shortcuts.php ✓
├── Flows/
│   ├── RunDatabaseQuery/
│   │   ├── RunDatabaseQuery.php ✓
│   │   ├── PrepareDatabaseQuery.php ✓
│   │   ├── QueryResult.php ✓
│   │   └── DatabaseQueryFailed.php ✓
│   ├── RunDatabaseMigration/
│   ├── ConnectToDatabase/
│   │   ├── ConnectToDatabase.php ✓
│   │   ├── DatabaseConnectionFailed.php ✓
│   │   └── ResolveConnectionConfiguration.php ✓
│   ├── BuildDatabaseSchema/
│   │   ├── CreateTable.php ✓
│   │   └── AlterTable.php ✓
│   └── RunDatabaseTransaction/
│       ├── RunDatabaseTransaction.php ✓
│       └── DatabaseTransactionFailed.php ✓
├── Capabilities/
│   ├── Connections/
│   │   ├── Pools/ (30+ pool variants)
│   │   ├── OpenConnection/
│   │   ├── ReadConnection/
│   │   ├── RunWithConnection/
│   │   └── ValueObjects/
│   ├── Query/
│   │   ├── Grammar/ (15+ grammars)
│   │   ├── ValueObjects/
│   │   └── Exceptions/
│   ├── QueryBuilding/
│   ├── Grammar/
│   ├── SchemaBuilding/
│   ├── Migrations/
│   ├── Transactions/
│   │   ├── RunTransaction/
│   │   ├── OnConnection/
│   │   └── Exceptions/
│   └── Observability/
├── Configuration/
└── Foundation/
    ├── Values/
    └── Failure/
```

---

## Restoration Complete

All V1 database behavior is present with 291 files.
Backup code from avax-backup.txt was used as source material.