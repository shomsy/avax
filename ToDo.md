Da. Ako je HTTP suite sada rešen, sledeći plan mora da bude **stabilizacija jezgra**, ne novo širenje.

Hladan redosled:

```text
1. Data stack
2. Namespace/autoload cleanup
3. PublicSurface audit
4. Database/Persistence split
5. Framework integration tests
6. Docs/tests mirror cleanup
7. Architecture guard tooling
8. Tek onda novi feature-i
```

Ne bih sada grupisao sve ostale komponente. HTTP je bio poseban slučaj jer je već realno postao suite. Sledeći najopasniji nered je `DataFoundation -> Data`, `DataLayer -> Persistence`, i `Database` koji još verovatno drži ORM/persistence stvari. Trenutni planovi već prepoznaju da je `Data` partial, `DataFoundation` deprecated ali sa real behavior-om, `Persistence` partial, `DataLayer` uklonjen ili u migraciji, a `Database` ostaje ali sa ORM extraction pending. 

## Phase 1: Zaključaj HTTP kao završenu odluku

Prvo ne diraj dalje HTTP dok ne napraviš “closure report”.

```text
[ ] Create Code-Review-And-ToDo/http-suite-closure-report.md
[ ] Confirm components/Session is deleted or bridge-only
[ ] Confirm components/Middleware is deleted or bridge-only
[ ] Confirm Request/Response/Router are not duplicated at top level
[ ] Confirm docs no longer point to Foundation/HTTP as canonical
[ ] Confirm tests pass for HTTP request lifecycle, Session, Middleware, Router
[ ] Confirm framework/System/HandleIncomingHttp uses canonical HTTP paths
```

Ako je ovo rešeno, označi ga kao **closed**, ne “mostly done”. Taj mentalni rez je bitan.

---

## Phase 2: DataFoundation -> Data

Ovo je sledeći najveći dug.

Cilj:

```text
components/DataFoundation -> deleted or bridge-only
components/Data           -> real owner
```

`DataFoundation` ne sme više da nosi real behavior. U trenutnom report-u stoji da `DataFoundation` i dalje poseduje kolekcije, Arrhae, DataTransfer, ObjectHandling/DTO, composites, structures, values i flow-like delove, dok je `Data` samo partial. 

Target:

```text
components/Data/
  System/
    PublicSurface/
      Data.php
      Arrhae.php
      Collection.php

    Flows/
      ReadDataObject/
      SerializeDataObject/
      NormalizeData/
      ReadDataValue/
      WriteDataValue/

    Capabilities/
      Arrays/
      Collections/
      DataPath/
      DataTransfer/
      DataShape/
      FieldVisibility/
      Structures/
      Values/
      Composites/

    Configuration/
    Foundation/
```

AI ToDo:

```text
You are normalizing Avax Data ownership.

Goal:
Move all real DataFoundation behavior into components/Data.

Rules:
- DataFoundation must become bridge-only or be deleted.
- Data owns in-memory data structures, collections, arrays, data paths, DTO/data transfer, object-to-array serialization, values, composites, and pure data transformations.
- Data must not depend on Database, Persistence, HTTP, or framework runtime.
- Do not delete behavior before comparing it.
- Do not keep duplicate owners.

Tasks:
1. Inventory components/DataFoundation.
2. Inventory components/Data.
3. Create Code-Review-And-ToDo/data-ownership-normalization.md.
4. Classify every DataFoundation file as:
   - move to Data/PublicSurface
   - move to Data/Flows
   - move to Data/Capabilities
   - move to Data/Configuration
   - move to Data/Foundation
   - bridge only
   - delete
5. Move unique behavior into Data.
6. Add characterization tests before moving behavior.
7. Reduce DataFoundation to bridge-only.
8. Delete DataFoundation after compatibility window if no external usage exists.
9. Normalize namespace.
10. Move docs to docs/components/Data/System.
11. Run Data tests and static analysis.
```

---

## Phase 3: Persistence + Database split

Posle Data, rešavaš `Persistence`.

Cilj:

```text
Database    = database mechanics
Persistence = object/query persistence
Data        = pure in-memory data
```

Granica:

```text
Database owns:
- connections
- query execution
- query builder
- transactions
- schema
- migrations
- bindings
- database telemetry

Persistence owns:
- EntityManager
- Repository
- UnitOfWork
- IdentityMap
- Mapping
- Hydration
- ChangeTracking
- Query intent
- Stored model shape
- N+1 diagnostics
```

Trenutni plan već kaže da `Database` ostaje, ali da ORM extraction ostaje pending.  To znači da sledeći pass nije “refactor Database”, nego **extract Persistence behavior out of Database**.

AI ToDo:

```text
You are normalizing Avax Database and Persistence ownership.

Goal:
Separate database mechanics from object/query persistence.

Rules:
- Database must not own ORM long-term.
- Persistence may depend on Database contracts.
- Database must not depend on Persistence.
- Data must remain lower-level than both.
- Do not move SQL mechanics into Persistence.
- Do not keep EntityManager/Repository/UnitOfWork in Database as final owner.

Tasks:
1. Inventory components/Database for ORM-like behavior.
2. Inventory components/Persistence.
3. Create Code-Review-And-ToDo/persistence-database-split.md.
4. Classify Database files:
   - keep in Database
   - extract to Persistence
   - bridge
   - delete
5. Move EntityManager, Repository, UnitOfWork, IdentityMap, Mapping, Hydration, ChangeTracking into Persistence.
6. Keep connections, query execution, transactions, schema, migrations in Database.
7. Add characterization tests before moving behavior.
8. Add contract tests for DatabaseConnection and Transaction.
9. Add Persistence tests for EntityManager, Repository, UnitOfWork, IdentityMap.
10. Normalize namespaces.
11. Update docs mirror.
```

---

## Phase 4: Namespace and autoload cleanup

Ovo je sistemski dug. Trenutni autoload plan ima i `Avax\\` i `components\\` mapirane na `components/`, što direktno omogućava namespace drift. 

Cilj:

```json
"psr-4": {
  "Avax\\Framework\\": "framework/",
  "Avax\\Components\\": "components/"
}
```

Ne odmah ako još ima legacy namespace-a. Ali ovo mora biti krajnji cilj.

AI ToDo:

```text
You are removing namespace drift from Avax.

Goal:
Move toward one canonical namespace.

Canonical namespaces:
- Avax\Framework\...
- Avax\Components\<Component>\...

Forbidden in canonical code:
- namespace components\
- namespace Avax\DataFoundation
- namespace Avax\DataLayer
- namespace Avax\HTTP
- namespace Avax\Session
- namespace Avax\Middleware

Tasks:
1. Create tooling/refactor/check-namespace-drift.php.
2. Report every namespace components\ occurrence.
3. Report every legacy Avax\DataFoundation, Avax\DataLayer, Avax\HTTP, Avax\Session, Avax\Middleware occurrence.
4. Allow legacy namespaces only in explicit bridge files.
5. Normalize migrated components one by one.
6. Only after all migrated components are clean, remove "components\\": "components/" from composer.json.
7. Run composer dump-autoload -o.
8. Run PHPStan on migrated paths.
```

---

## Phase 5: PublicSurface audit

Ne sme ti se desiti da `PublicSurface` postane novi `Services`.

Audituj:

```text
components/Config/System/PublicSurface
components/Data/System/PublicSurface
components/Database/System/PublicSurface
components/Persistence/System/PublicSurface
components/HTTP/System/PublicSurface
components/Auth/System/PublicSurface
components/Cache/System/PublicSurface
```

Posebno `Config`, jer raniji review je već pokazivao da `Config.php` verovatno drži state i loading behavior, što nije idealno za PublicSurface.

AI ToDo:

```text
You are auditing PublicSurface in Avax.

Goal:
Make every PublicSurface thin, stable, and externally useful.

Rules:
- PublicSurface receives public calls.
- PublicSurface delegates.
- PublicSurface must not own heavy behavior.
- PublicSurface must not hold mutable request/runtime state.
- PublicSurface must not contain runtime machinery.
- PublicSurface must be small enough to document.

Tasks:
1. Inventory all PublicSurface folders.
2. For every public file, classify:
   - valid public API
   - too much behavior
   - internal machinery
   - duplicate facade
   - bridge
   - delete
3. Move behavior into Flows or Capabilities.
4. Move assembly into Configuration.
5. Move small primitives/failures into Foundation.
6. Add tests proving public API still works.
7. Update docs.
```

---

## Phase 6: Clean docs and tests

Trenutno imaš staru dokumentaciju koja još pominje `Foundation/HTTP` i `Foundation/DataLayer`, dok ciljni model sada ide kroz `docs/components/...`. Prethodni sadržaj čak opisuje `Foundation/HTTP` kao mirror za HTTP shape, što je sada zastarela terminologija. 

Cilj:

```text
docs/components/HTTP/...
docs/components/Data/...
docs/components/Database/...
docs/components/Persistence/...
docs/framework/System/...
```

AI ToDo:

```text
You are cleaning Avax docs and tests after ownership normalization.

Goal:
Make docs and tests mirror the real source ownership.

Tasks:
1. Move docs/Foundation/HTTP to docs/components/HTTP.
2. Move docs/Foundation/DataLayer to docs/components/Persistence.
3. Move DataFoundation docs to docs/components/Data.
4. Delete docs for removed structures.
5. Move tests/Foundation/* to tests/Unit/Components/* or tests/Integration/Components/*.
6. Move component-local tests into repo-level tests unless the package intentionally owns local package tests.
7. Remove tests for deleted dead architecture.
8. Add how-this-works.md only for real ownership folders.
9. Add docs mirror validator.
```

---

## Phase 7: Component hardening order

Kad ownership bude čist, onda kreće hardening komponenti, redom:

```text
1. Config
2. Container
3. Data
4. Database
5. Persistence
6. HTTP
7. Cache
8. Auth
9. Events
10. Filesystem
11. Logging
12. Console
13. Validation
14. View
```

Zašto ovako?

`Config` i `Container` su composition foundation. `Data`, `Database`, `Persistence` su sada najveći preostali nered. `HTTP` je već rešen ali treba integration hardening. `Cache` već ima ozbiljan contract-test pristup, što je dobar model za ostale komponente.  `Auth` ima već konkretne security/risk testove, što znači da ga ne treba lomiti, nego ga kasnije standardizovati u isti architecture shape. 

---

## Phase 8: Architecture guard tooling

Ovo će sprečiti da se haos vrati.

Napravi:

```text
tooling/refactor/check-duplicate-owners.php
tooling/refactor/check-namespace-drift.php
tooling/refactor/check-public-surface.php
tooling/refactor/check-docs-mirror.php
tooling/refactor/check-runtime-leaks.php
tooling/refactor/check-forbidden-folders.php
```

AI ToDo:

```text
You are adding Avax architecture guard tooling.

Goal:
Prevent duplicate ownership and governance drift.

Checks:
1. No DataFoundation real behavior.
2. No DataLayer real behavior.
3. No top-level Session real behavior.
4. No top-level Middleware real behavior.
5. No namespace components\ in canonical code.
6. No runtime-specific imports outside framework/System/Capabilities/Runtime/Adapters.
7. No business logic inside PublicSurface.
8. No docs pointing to obsolete Foundation/* ownership.
9. No duplicate component owners.
10. No Core/Shared/Helpers/Utils dumping ground.

Each checker must:
- print clear PASS/FAIL output
- show offending paths
- return non-zero exit code on failure
```

---

## Phase 9: Framework runtime integration

Tek kada komponente imaju jasne owner-e, radiš framework integration.

Cilj:

```text
framework/System
  boots application
  registers components
  opens request scope
  handles HTTP
  handles console
  resets state
  shuts down cleanly
```

AI ToDo:

```text
You are hardening framework/System integration.

Goal:
Make framework/System the real runtime lifecycle owner.

Tasks:
1. Verify BootApplication registers Config, Container, HTTP, Data, Database, Persistence, Cache, Auth.
2. Verify HandleIncomingHttp uses HTTP suite only.
3. Verify RunConsoleCommand uses Console component only.
4. Verify RequestScope opens and closes around HTTP requests.
5. Verify StateReset runs after worker request.
6. Verify no component directly depends on Swoole/RoadRunner/FrankenPHP.
7. Add feature test:
   - app boots
   - route handles request
   - middleware runs
   - session persists expected values
   - response emits
   - state resets
8. Add fake worker test:
   - same process handles two requests
   - request-local state does not leak
```

---

## Phase 10: Quality gates

Za svaki sledeći pass:

```text
composer validate --no-check-publish
composer dump-autoload -o
php -l changed PHP files
vendor/bin/phpunit targeted-suite
vendor/bin/phpstan analyse changed-paths
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-docs-mirror.php
php tooling/refactor/check-runtime-leaks.php
```

Pošto tvoja pravila zahtevaju kluster posle bilo kakve izmene fajlova i u planu eksplicitno traže kluster verification, svaki implementation pass treba da završi sa `kluster_code_review_auto`, ako je alat dostupan u tom okruženju. 

---

# Finalni plan, kratko

```text
NEXT PASS 1:
DataFoundation -> Data

NEXT PASS 2:
Database/Persistence split

NEXT PASS 3:
Namespace/autoload cleanup

NEXT PASS 4:
PublicSurface audit

NEXT PASS 5:
Docs/tests mirror cleanup

NEXT PASS 6:
Architecture guard tooling

NEXT PASS 7:
Framework runtime integration tests

NEXT PASS 8:
Component hardening, Config -> Container -> Data -> Database -> Persistence -> HTTP -> Cache -> Auth
```

Ne bih sada uvodio nove feature-e. Avax trenutno treba da postane **istinit, čist i proverljiv**. Posle toga možeš da gradiš modernije stvari kao `runtime:doctor`, `architecture:check`, `container:graph`, `routes:explain`, tracing timeline, config schema validation i worker-safety inspector.
