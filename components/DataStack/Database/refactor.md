# Database: Laravel-like Query DSL + Doctrine-style ORM under AI Prompt rules

## Status

- Phase 1 complete
- Phase 2 complete
- Phase 3 complete
- Phase 4 complete
- Phase 5 complete
- Phase 6 complete
- Phase 7 complete
- Ownership documentation completed for newly introduced folders
- PSR-4/autoload normalization completed for Database source

## Enterprise Expansion v2 - Phase Plan

Ovaj dokument proširuje original refactor.md sa enterprise-grade funkcijama.

---

### Phase 1: Dialect Engine Expansion

**Cilj**: Multi-database podrška - MySQL, PostgreSQL, SQLite, SQL Server

```
System/Capabilities/Query/Grammar/
├── GrammarInterface.php    (postoji - proširiti)
├── MySQLGrammar.php       (postoji)
├── PostgreSQLGrammar.php  [NOVO]
├── SQLiteGrammar.php      [NOVO]
├── SQLServerGrammar.php [NOVO]
└── DialectFactory.php    [NOVO]
```

**NOVO**: `PostgreSQLGrammar` sa:

- JSONB, ARRAY, HSTORE tipovi
- ON CONFLICT (upsert)
- RETURNING
- Window functions (ROW_NUMBER, RANK, DENSE_RANK, LAG, LEAD)
- WITH RECURSIVE (CTE)
- ILIKE, ~ (regex)
- advisory locks

**NOVO**: `SQLiteGrammar` sa:

- UPSERT (ON CONFLICT)
- RETURNING
- Window functions (novije verzije)
- REGEXP (via extension)

**NOVO**: `SQLServerGrammar` sa:

- MERGE
- OUTPUT clause
- Window functions
- CTEs
- PIVOT/UNPIVOT
- hierarchyid

---

### Phase 2: Query IR/AST

**Cilj**: Intermediate Representation za query optimizaciju, linting, static analysis

```
System/Capabilities/Query/IR/
├── Nodes/
│   ├── QueryNode.php
│   ├── SelectNode.php
│   ├── FromNode.php
│   ├── JoinNode.php
│   ├── WhereNode.php
│   ├── GroupByNode.php
│   ├── OrderByNode.php
│   ├── HavingNode.php
│   └── ProjectionNode.php
├── IRBuilder.php        (fluent API za gradnju IR-a)
├── IRTransformer.php   (AST → SQL)
├── IRValidator.php     (static analysis, query safety)
├── IRNormalizer.php    (canonical form)
└── IRCache.php        (query plan caching)
```

**Svrha**:

- Optimizacija upita pre renderovanja
- Statička validacija (postojanje kolona, tipovi)
- Query plan fingerprinting
- Cross-dialect portability

---

### Phase 3: Type-Safe Projections

**Cilj**: Typed result mapping umesto `array`

```
System/Capabilities/Query/Projections/
├── Projection.php         (interface)
├── TypedResult.php        (generic wrapper)
├── ResultMapper.php       (mapiranje)
├── ProjectionBuilder.php   (fluent gradnja)
└── TypeGuesser.php         (inference iz query-ja)
```

**API**:

```php
// Umesto:
$users = $qb->get(); // array

// Sa projekcijom:
$result = $qb->select('id', 'name', 'email')
    ->as(UserSummary::class)
    ->get();

/** @var UserSummary $user */ foreach ($result as $user) {
    echo $user->name; // type-safe
}
```

---

### Phase 4: Advanced Query Features

**Cilj**: CTE, Window Functions, Bulk Operations, Upsert

```
System/Capabilities/Query/Advanced/
├── CTE/
│   ├── CTEBuilder.php
│   ├── RecursiveCTE.php
│   └── CTEUnion.php
├── WindowFunctions/
│   ├── WindowFunction.php
│   ├── RowNumber.php
│   ├── Rank.php
│   ├── LagLead.php
│   └── PartitionBuilder.php
├── BulkOperations/
│   ├── BatchInsert.php
│   ├── BatchUpdate.php
│   └── BulkUpsert.php
└── Upsert/
    ├── UpsertBuilder.php
    └── OnConflict.php
```

**Primeri**:

```php
// CTE
$qb->with('cte_rank', function($cte) {
    return $cte->selectRaw('ROW_NUMBER() OVER (PARTITION BY dept ORDER BY salary DESC) as rn')
        ->from('employees');
})->from('cte_rank')->where('rn', '<=', 3);

// Window function
$qb->select('name', 'department')
  ->over('department', 'ROW_NUMBER() OVER (PARTITION BY department ORDER BY salary DESC)')
  ->rank('department_salary_rank');

// Bulk upsert
$db->upsert('users')->values($users)->onConflict('email')->doNothing();
```

---

### Phase 5: Transaction Control

**Cilj**: Isolation levels, retry policies, savepoints, deadlock handling

```
System/Capabilities/Transactions/
├── Transactions.php       (proširiti)
├── IsolationLevels.php    [NOVO]
│   ├── READ_UNCOMMITTED
│   ├── READ_COMMITTED
│   ├── REPEATABLE_READ
│   └── SERIALIZABLE
├── SavepointManager.php  [NOVO]
├── RetryPolicy.php        [NOVO]
│   ├── maxAttempts
│   ├── backoffStrategy
│   └── retryOn
├── DeadlockDetector.php   [NOVO]
├── LockManager.php       [NOVO]
│   ├── advisoryLock()
│   ├── pgAdvisoryLock()
│   └── rowLock()
└── TransactionProfiler.php [NOVO]
```

---

### Phase 6: Observability

**Cilj**: OpenTelemetry, query fingerprinting, N+1 detection

```
System/Capabilities/Telemetry/
├── Telemetry.php       (proširiti)
├── OpenTelemetry/     [NOVO]
│   ├── SpanBuilder.php
│   ├── TraceExporter.php
│   ├── MetricsExporter.php
│   └── OtelConfig.php
├── QueryFingerprint.php [NOVO]
├── SlowQueryDetector.php [NOVO]
├── N1QueryDetector.php [NOVO]
├── QueryTimeline.php   [NOVO]
└── DbMetricsCollector.php [NOVO]
```

---

### Phase 7: DataLoader Pattern

**Cilj**: Batched relation loading, N+1 prevention

```
System/Capabilities/ORM/
├── DataLoader/          [NOVO]
│   ├── DataLoaderInterface.php
│   ├── BatchLoader.php
│   ├── LoaderRegistry.php
│   └── LoaderContext.php
└── N1QueryDetector.php [NOVO] (alarm, ne prevention)
```

---

### Phase 8: Modeling Enhancements

**Cilj**: Composite PKs, unique constraints, custom types

```
System/Capabilities/ORM/Metadata/
├── FieldMetadata.php  (proširiti)
│   - unique: bool
│   - generated: bool
│   - computed: string|callable
├── EntityMetadata.php (proširiti)
│   - compositeKeys: array
│   - naturalKeys: array
│   - uniqueConstraints: array
└── FieldTypeRegistry.php [NOVO]
    - registerCustomType()
    - getTypeHandler()
```

---

## Enterprise Public API Additions

```php
// Dialect-aware query
$db->query()
   ->useDialect('postgresql')
   ->table('users');

// Type-safe projections
$result = $qb->select('id', 'name')
    ->as(UserVO::class)
    ->get();

// CTE
$qb->with('high_earners', fn($cte) => $cte->...)
    ->from('high_earners');

// Window functions
$qb->select('*')
   ->over('department', 'ROW_NUMBER() OVER (...)');

// Bulk upsert
$db->upsert('users')->values($batch)->onConflict('email')->doUpdate();

// Transaction control
$db->transactions()
   ->isolation(Isolation::REPEATABLE_READ)
   ->retry(3)
   ->run(fn() => ...);
```

---

## Test Plan - Enterprise

- Dialect switching tests (MySQL ↔ PostgreSQL ↔ SQLite)
- IR validation tests
- Projection mapping tests
- CTE and window function tests
- Bulk upsert tests
- Isolation level tests
- Retry policy tests
- N+1 detection tests
- Query fingerprint tests

---

## Locked Architectural Decisions (v2)

- Dialect Factory se registruje kroz DatabaseBuilder (ne globals)
- IR je interni - ne izlaže se kao public API
- Projections su opt-in (array je default za back-compat)
- Window functions rade samo ako ih dialect podržava
- DataLoader je default za relation loading
- Retry policy se konfiguriše po connection-u, ne global

---

## Assumptions (v2)

- PostgreSQL je prioritetni dialect #1 posle MySQL
- SQLite je za testiranje embedded scenarija
- IR caching za prepared statement reuse
- DataLoader koristi query batching, ne N+1

---

## Test Plan - Enterprise

- Dialect switching tests (MySQL ↔ PostgreSQL ↔ SQLite)
- IR validation tests
- Projection mapping tests
- CTE and window function tests
- Bulk upsert tests
- Isolation level tests
- Retry policy tests
- N+1 detection tests
- Query fingerprint tests

---

## Locked Architectural Decisions (v2)

- Dialect Factory se registruje kroz DatabaseBuilder (ne globals)
- IR je interni - ne izlaže se kao public API
- Projections su opt-in (array je default za back-compat)
- Window functions rade samo ako ih dialect podržava
- DataLoader je default za relation loading
- Retry policy se konfiguriše po connection-u, ne global

---

## Assumptions (v2)

- PostgreSQL je prioritetni dialect #1 posle MySQL
- SQLite je za testiranje embedded scenarija
- IR caching za prepared statement reuse
- DataLoader koristi query batching, ne N+1

---

## Current Tree

```text
Foundation/
├── Entity/Entity.php
├── Repository/Repository.php
└── Database/
    ├── Integrations/
    ├── System/
    │   ├── Database.php
    │   ├── DatabaseInterface.php
    │   ├── Configuration/DatabaseBuilder.php
    │   └── Capabilities/
    │       ├── Connections/
    │       ├── Querying/
    │       ├── Migrations/
    │       ├── Transactions/
    │       └── Telemetry/
    ├── refactor.md
    └── how-this-works.md
```

## Target Tree

```text
Foundation/Database/
├── Database.php
├── Query.php
├── EntityManager.php
├── Schema.php
├── Migrations.php
├── Transactions.php
├── Telemetry.php
├── Integrations/
│   ├── AvaxContainer/
│   └── Console/
└── System/
    ├── Configuration/
    │   └── DatabaseBuilder.php
    ├── Foundation/
    └── Capabilities/
        ├── Connections/
        ├── Query/
        │   ├── Query.php
        │   ├── Builder/
        │   ├── Execution/
        │   ├── Grammar/
        │   ├── DSL/
        │   └── State/
        ├── ORM/
        │   ├── EntityManager.php
        │   ├── Metadata/
        │   ├── Attributes/
        │   ├── Hydration/
        │   ├── Persisters/
        │   ├── UnitOfWork/
        │   ├── IdentityMap/
        │   ├── Relations/
        │   ├── Proxies/
        │   └── Repositories/
        ├── Migrations/
        │   ├── Migrations.php
        │   ├── Schema/
        │   ├── CreateMigration/
        │   ├── LoadMigrations/
        │   ├── RunMigrations/
        │   ├── RollbackMigrations/
        │   ├── ReadMigrationStatus/
        │   ├── ExportDatabase/
        │   └── SeedDatabase/
        ├── Transactions/
        └── Telemetry/
```

## Summary

- Root Database komponenta dobija **realne javne predstavnike** za glavne slice-ove: `Query`, `EntityManager`, `Schema`,
  `Migrations`, `Transactions`, `Telemetry`.
- Trenutni `Querying` se deli na dve poštene capability zone:
    - `Query`: Laravel-like fluent DSL i SQL execution surface
    - `ORM`: Doctrine/Hibernate/EF-style persistence model
- `Schema` postaje javni Laravel-like facade za schema DSL, dok `Migrations` ostaje runtime/admin capability za load,
  run, rollback, status, export i seed.
- `IdentityMap` i deferred-write ponašanje izlaze iz query capability-ja i prelaze pod `ORM/UnitOfWork`, jer su ORM
  concern, ne query DSL concern.
- Root predstavnici su **instance-based public surfaces**, ne Laravel-style global static facades; inspiracija je
  Laravel ergonomija, ali arhitektura ostaje AI Prompt-compliant.

## Locked Architectural Decisions

- `Querying/` se preimenuje u `Query/` da ne postoji parallel naming između public `Query` surface-a i internog
  capability-ja.
- ORM postaje zaseban capability umesto da ostane razliven kroz `QueryBuilder`, `Repository` i `IdentityMap`.
- `Foundation/Entity/Entity.php` prestaje da bude kanonski ORM oslonac; ciljni ORM koristi **POPO entitete sa PHP
  attributes metadata**.
- `Foundation/Repository/Repository.php` prestaje da bude kanonski persistence API; zamenjuje ga ORM repository model
  vezan za `EntityManager`.
- `QueryBuilder::transaction()` se uklanja iz javnog surface-a; transaction ownership ostaje isključivo u `Transactions`
  i ORM transactional API-ju.
- `Migrations/RunMigrations` više ne sme zavisiti od `QueryBuilder::transaction()`, nego od `Transactions::run(...)`.
- `SchemaOperations/` se preoblikuje u `Migrations/Schema/` da folder kaže capability, ne tehnički bucket.
- Root representative class se uvodi samo kada ima jasno public ownership značenje; ne uvode se generički `Manager` ili
  `Facade` nazivi bez potrebe.

## Public API / Type Changes

- `Database` postaje kanonski composition root sa:
    - `connections()`
    - `query()`
    - `entityManager()`
    - `schema()`
    - `migrations()`
    - `transactions()`
    - `telemetry()`
    - `table(string $table)`
- `Query` je javni fluent DSL entrypoint i daje:
    - `table(...)`
    - `builder(...)`
    - `from(...)`
    - `raw(...)`
    - `on(connection: ...)`
- `EntityManager` je javni ORM entrypoint i daje:
    - `find(...)`
    - `persist(...)`
    - `remove(...)`
    - `flush()`
    - `clear()`
    - `refresh(...)`
    - `repository(...)`
    - `transactional(...)`
- `Schema` je javni schema DSL entrypoint i daje Laravel-like surface:
    - `create(...)`
    - `table(...)`
    - `drop(...)`
    - `dropIfExists(...)`
    - `truncate(...)`
    - `createDatabase(...)`
    - `dropDatabase(...)`
- `Migrations` ostaje runtime surface:
    - `loader()`
    - `runner()`
    - `rollbacker()`
    - `status()`
    - `generator()`
    - `exporter()`
    - `seed(...)`
- ORM metadata je **PHP attributes first**. Docblock annotations mogu postojati samo kao migracioni compatibility driver
  ako se pokaže potrebnim, ali nisu kanonski model.

## Implementation Changes

- **Query capability**
    - Zadržati Laravel-like builder ergonomiju i proširiti je prema modernom DSL standardu.
    - `Builder` ostaje query object, ne ORM owner.
    - `Execution`, `Grammar`, `State` i DSL concerns ostaju pod Query capability-jem.
    - Iz Query capability-ja izvući `IdentityMap` i transaction flush logiku.

- **ORM capability**
    - Uvesti `EntityManager`, metadata reader, hydrator, persister, repository factory, UnitOfWork i IdentityMap.
    - Uvesti relation mapping i cascade ownership za minimum: `ManyToOne`, `OneToMany`, `OneToOne`, `ManyToMany`.
    - Uvesti lazy loading/proxy support kao deo enterprise core scope-a.
    - ORM gradi na `Query`, `Transactions` i `Connections`, ali ne sme da ih zamagli.

- **Schema + Migrations**
    - Razdvojiti javni `Schema` DSL od migration runtime priče.
    - Schema design i type mapping ostaju u migrations capability-ju, ali pod jasno imenovanim `Schema/` ownership-om.
    - Uskladiti type mapping sa ORM metadata modelom kako bi migrations i ORM govorili isti jezik za tipove, ključeve i
      relacije.

- **Docs / AI Prompt compliance**
    - `how-this-works.md` ostaje u svakom ownership folderu i svaki mora koristiti realne komande, realne fajlove i
      realne funkcije.
    - `docs/Foundation/Database/...` mora mirror-ovati source tree i dokumentovati novi public root surfaces i
      capability split.
    - Generički i placeholder `how-this-works` tekstovi se brišu i pišu ponovo.
    - Legacy konceptualni docs koji još pričaju o `Kernel`, `Manifest`, starom module sistemu ili starom query
      ownership-u moraju biti prepisani ili uklonjeni.

## Test Plan

- Query DSL tests:
    - Laravel-like chaining za select/filter/join/group/order/aggregate/paginate/upsert/raw guardrails
    - builder cloning, connection switching i pretend mode
- ORM tests:
    - attributes metadata reading
    - entity hydration i identity stability
    - dirty tracking, `persist/remove/flush`
    - UnitOfWork ordering
    - relation loading, cascade rules i lazy proxies
    - repository resolution kroz `EntityManager`
- Schema/Migrations tests:
    - `Schema` facade API
    - migration load/run/rollback/status/export/seed
    - migrations inside `Transactions`
- Governance tests:
    - nema `QueryBuilder::transaction()`
    - nema parallel naming alias-a na public root-u
    - svaki ownership folder ima validan `how-this-works.md`
    - `docs/Foundation/Database/...` mirroruje source tree

## Assumptions

- Laravel je inspiracija za **ergonomiju Query DSL-a i Schema API-ja**, ne za static/global facade model.
- Doctrine/Hibernate/EF Core su inspiracija za **ORM semantics**, identity, UnitOfWork, metadata i repository model.
- Root representative classes postoje samo za velike, user-facing slice-ove; ne uvode se mehanički u svaki folder.
- ORM target ne zahteva da entiteti nasleđuju baznu klasu; atributi i metadata su kanonski mehanizam.
- `EntityManager` je kanonski ORM public naziv; ne uvodi se dodatni paralelni public naziv tipa `orm()`.
