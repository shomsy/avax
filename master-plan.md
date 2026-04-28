NOTE: PRIDRZAVAJ SE STRIKTNO PRAVILA IZ AI Prompts FOLDERA !!!

Ovo treba da bude **jedan master plan**, ne dva plana zalepljena jedan za drugi.

Korekcija koju si tražio je bitna: **AI prvo mora da napravi arhitekturu i premesti postojeći kod u nju**, a ne da prvo piše nove testove, nove klase i novi framework iz početka. Tek kada struktura i kod legnu na finalna mesta, onda se pišu i sređuju testovi koji proveravaju da li je arhitektura stvarno ispoštovana.

Trenutni truth report već pokazuje zašto je ovo potrebno: neke komponente su clean owneri, neke su partial, a neke su unclear, posebno `Commands`, `DateTime`, `Events`, `Middleware`, `Security`, `Session`, `Validation`, dok `Data` i dalje zavisi od `DataFoundation`, a `Database` još ima legacy namespace i root fajlove.  Governance takođe traži da folder govori flow ili capability, unit responsibility, a function exact action, i da se razlikuje repo root od system root-a. 

Ispod je plan koji možeš direktno da daš AI agentu.

```md
# Avax Component Suite Architecture Migration Plan

## 0. Mission

Migrate Avax from a flat and partially duplicated component layout into a clear component-suite architecture.

This is not a rewrite.

This is not a feature expansion.

This is not an opportunity to invent new implementations from scratch.

The migration must reuse existing code as the source of truth, move it into the correct architecture, rename namespaces, delete duplicates, keep temporary bridges only where necessary, and only then validate the result with tests.

The required order is:

1. architecture
2. code movement and normalization
3. architecture validation tests
4. component integration tests
5. behavior tests
6. governance and coding-standard checks
7. documentation
8. final cleanup report

Do not reverse this order.

Documentation comes after code and tests because documentation must describe the real structure, not an intended fantasy.
```

---

# 1. Non-negotiable laws

```md
## Non-Negotiable Laws

1. Do not rewrite working components from scratch.
2. Move existing code into the new architecture first.
3. Rename namespaces after moving files.
4. Delete duplicate owners after unique behavior is merged.
5. Keep bridge files only when compatibility requires them.
6. Every bridge must be thin, deprecated, tested, and scheduled for removal.
7. Do not create empty decorative folders.
8. Do not introduce new features during structure migration.
9. Do not keep two real owners for one concept.
10. Do not keep old paths as real behavior.
11. Do not keep `components\...` lowercase namespace as canonical.
12. Do not keep `Avax\<Component>\...` as canonical after migration.
13. Do not keep DataFoundation or DataLayer as real components.
14. Do not keep top-level Session or Middleware as real components.
15. Do not write documentation before the code and tests reflect reality.
16. Run architecture and governance checks before final documentation.
17. Run kluster code verification after file changes when configured.
```

Tvoj postojeći plan već kaže da root-level stvari kao `System`, `DI`, `ServerRequest`, `Auth`, `Providers`, `Traits`, `Writers`, `Config`, `Presentation`, `bootstrap` ne smeju ostati kao realni root owneri i da se moraju premestiti, obrisati ili svesti na bridge. Isto važi za `DataFoundation -> Data`, `DataLayer -> Persistence`, i narrow compatibility bridges. 

---

# 2. Final target architecture

## 2.1 Repo root

```text
avax/
  framework/
  components/
  tests/
  docs/
  examples/
  tooling/
  bin/

  composer.json
  phpunit.xml
  phpstan.neon
  psalm.xml
  rector.php
  README.md
  AGENTS.md
```

Repo root is operational. It organizes source, tests, docs, examples and tooling.

No random production owners at repo root.

Forbidden as final root-level source folders:

```text
System/
DI/
ServerRequest/
Auth/
Providers/
Traits/
Writers/
Config/
Presentation/
bootstrap/
scripts/
tools/
public/
```

Allowed only if explicitly documented as tooling, examples, test fixtures or temporary compatibility.

---

## 2.2 Framework root

```text
framework/
  System/
    PublicSurface/
    Flows/
    Capabilities/
    Configuration/
    Foundation/
```

`framework/System` owns framework runtime lifecycle.

It owns:

```text
BootApplication
HandleIncomingHttp
RunConsoleCommand
StartWorker
HandleWorkerRequest
ResetApplicationState
ShutdownRuntime
Runtime adapters
RequestScope
StateReset
ComponentRegistry
Diagnostics
```

It does **not** own component behavior.

It does not own Cache, Auth, Database, Router, Session, View, Queue, Mail.

It orchestrates them.

---

# 3. New component suite structure

This is the key change.

The old flat structure:

```text
components/
  Auth/
  Cache/
  Config/
  Container/
  Data/
  Database/
  Persistence/
  HTTP/
  Session/
  Middleware/
  Queue/
  Mail/
```

must become:

```text
components/
  Application/
  HTTP/
  CLI/
  DataStack/
  Identity/
  Operations/
  Presentation/
  DeveloperTools/
```

Each suite groups related components.

Each component still owns its own `System/` root.

Canonical component shape:

```text
components/<Suite>/<Component>/
  System/
    PublicSurface/
    Flows/
    Capabilities/
    Configuration/
    Foundation/
```

Do not create all folders blindly. Create only folders that have real ownership.

---

## 3.1 Application suite

```text
components/
  Application/
    Config/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Container/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Cache/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Filesystem/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Validation/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Text/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    DateTime/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/
```

`Application/` owns generic application services.

It must not become `Core/`.

Do not create `Core/`, `Shared/`, `Helpers/`, `Utils/`, `Managers/`.

---

## 3.2 HTTP suite

```text
components/
  HTTP/
    System/
      PublicSurface/
      Flows/
      Capabilities/
      Configuration/
      Foundation/

    Request/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Response/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Router/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Middleware/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Session/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Cookies/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    URI/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Uploads/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/
```

`HTTP/` is a component suite.

It owns inbound web protocol capabilities.

`Session` and `Middleware` must not remain top-level real components.

Temporary bridges allowed:

```text
components/Session/
components/Middleware/
```

Final state:

```text
components/HTTP/Session/
components/HTTP/Middleware/
```

---

## 3.3 CLI suite

```text
components/
  CLI/
    Console/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Commands/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/
```

If `Commands` is only a historical name for Console command registration/execution, merge it into:

```text
components/CLI/Console/
```

and delete or bridge:

```text
components/Commands/
```

---

## 3.4 DataStack suite

```text
components/
  DataStack/
    Data/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Database/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Persistence/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/
```

Rules:

```text
Data = in-memory data structures, collections, arrays, data paths, DTO/data transfer, object serialization.

Database = connections, SQL/query execution, transactions, schema, migrations, bindings.

Persistence = EntityManager, Repository, UnitOfWork, IdentityMap, Mapping, Hydration, ChangeTracking, Query intent.
```

The existing plan already established this split: `DataFoundation -> Data`, `DataLayer -> Persistence`, and `Database` remains database mechanics. 

---

## 3.5 Identity suite

```text
components/
  Identity/
    Auth/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Access/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Security/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Tokens/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/
```

Rules:

```text
Auth = login, logout, register, current user, authentication state.
Access = permissions, roles, policies, gates.
Security = password policy, hashing policy, secure primitives, non-HTTP security.
Tokens = access tokens, refresh tokens, token codecs, token storage.
```

HTTP-specific security such as CSRF, security headers, trusted proxies and signed HTTP URLs belongs under:

```text
components/HTTP/Security/
```

only if it exists as real HTTP behavior.

Do not mix HTTP security middleware with identity security.

---

## 3.6 Operations suite

```text
components/
  Operations/
    Events/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Logging/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Mail/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Queue/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Notifications/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/
```

`Operations/` owns background/system operational capabilities.

No queue expansion now.

Move existing code only.

---

## 3.7 Presentation suite

```text
components/
  Presentation/
    View/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/
```

`Presentation/View` owns template rendering and view data.

Do not put HTTP request lifecycle in Presentation.

Do not put controllers here unless a separate controller/component decision exists.

---

## 3.8 DeveloperTools suite

```text
components/
  DeveloperTools/
    Diagnostics/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    DumpDebugger/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    ArchitectureReview/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/
```

`DeveloperTools/` owns development-time inspection, diagnostics, dumps and architecture review support.

Runtime diagnostics used by framework worker safety may remain in:

```text
framework/System/Capabilities/Diagnostics/
```

if it is framework lifecycle state.

Developer-facing diagnostic commands and reports may live in:

```text
components/DeveloperTools/Diagnostics/
```

---

# 4. Canonical namespace

Final namespace:

```php
Avax\Framework\...
Avax\Components\<Suite>\<Component>\System\...
```

Examples:

```php
Avax\Framework\System\Flows\BootApplication\BootApplication

Avax\Components\Application\Config\System\PublicSurface\Config
Avax\Components\Application\Container\System\PublicSurface\Container
Avax\Components\HTTP\Session\System\PublicSurface\Session
Avax\Components\DataStack\Database\System\PublicSurface\Database
Avax\Components\DataStack\Persistence\System\PublicSurface\EntityManager
Avax\Components\Identity\Auth\System\PublicSurface\Auth
Avax\Components\Operations\Logging\System\PublicSurface\Logger
Avax\Components\CLI\Console\System\PublicSurface\Console
```

Final composer target:

```json
{
  "autoload": {
    "psr-4": {
      "Avax\\Framework\\": "framework/",
      "Avax\\Components\\": "components/"
    }
  }
}
```

Temporary legacy namespaces may exist only in bridge files.

Forbidden in final canonical source:

```php
namespace components\...
namespace Avax\DataFoundation\...
namespace Avax\DataLayer\...
namespace Avax\Session\...
namespace Avax\Middleware\...
namespace Avax\Database\System\...
```

The current report already identifies namespace drift and mixed namespace issues, including `components\...` and legacy root namespaces. 

---

# 5. Migration execution order

This is the strict order AI must follow.

```text
0. Freeze and inventory
1. Create target architecture skeleton
2. Move existing code into suites
3. Rename namespaces and imports
4. Merge unique behavior
5. Delete or bridge duplicates
6. Update autoload incrementally
7. Run architecture placement checks
8. Write and move tests
9. Run behavior and integration tests
10. Run governance and coding-standard checks
11. Write docs last
12. Final cleanup and report
```

Do not write docs before tests.

Do not write new implementation before moving existing implementation.

Do not write from scratch unless the plan explicitly says the feature is new or the old code is unrecoverable.

---

# 6. Phase 0: Freeze and inventory

```text
[ ] Create branch: refactor/component-suite-architecture
[ ] Stop all new feature work
[ ] Do not add new runtime adapters
[ ] Do not add queues, notifications, realtime, scheduler, async or mail features
[ ] Generate current tree inventory
[ ] Generate duplicate owner inventory
[ ] Generate namespace inventory
[ ] Generate bridge inventory
[ ] Generate deleted/stale file inventory
```

Create:

```text
Code-Review-And-ToDo/component-suite-migration-truth.md
```

Run and record:

```bash
find components -maxdepth 4 -type d | sort
find components -maxdepth 5 -type f | sort
find framework -maxdepth 5 -type f | sort

grep -R "namespace components\\\\" -n components framework tests || true
grep -R "namespace Avax\\\\DataFoundation" -n components framework tests || true
grep -R "namespace Avax\\\\DataLayer" -n components framework tests || true
grep -R "namespace Avax\\\\Session" -n components framework tests || true
grep -R "namespace Avax\\\\Middleware" -n components framework tests || true
grep -R "namespace Avax\\\\Database\\\\System" -n components framework tests || true
```

Classification table:

```text
| Current path | Current role | Target suite | Target component | Action |
|---|---|---|---|---|
| components/Config | real owner | Application | Config | move |
| components/Container | real owner | Application | Container | move |
| components/Cache | real owner | Application | Cache | move |
| components/HTTP | suite owner | HTTP | HTTP suite | keep and normalize |
| components/Session | duplicate/bridge/unclear | HTTP | Session | bridge or delete |
| components/Data | partial owner | DataStack | Data | move |
| components/DataFoundation | legacy real behavior | DataStack | Data | merge then bridge/delete |
| components/Database | real owner | DataStack | Database | move and normalize |
| components/Persistence | real owner | DataStack | Persistence | move |
| components/DataLayer | legacy real behavior | DataStack | Persistence | merge then bridge/delete |
```

Done criteria:

```text
[ ] Every existing component has a target suite.
[ ] Every unclear component has an explicit action.
[ ] No file is moved before inventory is complete.
```

---

# 7. Phase 1: Create target architecture skeleton

Create suite folders first.

```text
[ ] Create components/Application/
[ ] Create components/HTTP/
[ ] Create components/CLI/
[ ] Create components/DataStack/
[ ] Create components/Identity/
[ ] Create components/Operations/
[ ] Create components/Presentation/
[ ] Create components/DeveloperTools/
```

Do not create all subfolders blindly.

Create target component folder only when moving an existing component.

For each moved component:

```text
components/<Suite>/<Component>/
  System/
```

Inside `System/`, create only lanes that receive existing code:

```text
PublicSurface/
Flows/
Capabilities/
Configuration/
Foundation/
```

Rules:

```text
[ ] PublicSurface only for public API classes
[ ] Flows only for behavior/use-case/action owners
[ ] Capabilities only for reusable mechanisms
[ ] Configuration only for builders/providers/configuration
[ ] Foundation only for local primitives/failures/value objects
```

---

# 8. Phase 2: Move components into suites

## 8.1 Application suite moves

```text
[ ] Move components/Config        -> components/Application/Config
[ ] Move components/Container     -> components/Application/Container
[ ] Move components/Cache         -> components/Application/Cache
[ ] Move components/Filesystem    -> components/Application/Filesystem
[ ] Move components/Validation    -> components/Application/Validation
[ ] Move components/Text          -> components/Application/Text
[ ] Move components/DateTime      -> components/Application/DateTime
```

If `Validation` or `DateTime` has no `System/`, create it and move existing code into proper lanes.

Do not rewrite.

## 8.2 HTTP suite normalization

```text
[ ] Keep components/HTTP as suite
[ ] Ensure components/HTTP/System exists
[ ] Ensure components/HTTP/Request/System exists
[ ] Ensure components/HTTP/Response/System exists
[ ] Ensure components/HTTP/Router/System exists
[ ] Ensure components/HTTP/Middleware/System exists
[ ] Ensure components/HTTP/Session/System exists
[ ] Move components/Session unique behavior into components/HTTP/Session
[ ] Reduce components/Session to bridge-only or delete it
[ ] Move components/Middleware unique behavior into components/HTTP/Middleware
[ ] Reduce components/Middleware to bridge-only or delete it
[ ] Check top-level Request/Response/Router and bridge/delete if duplicate
```

Router extraction is postponed. Keep Router under HTTP for this pass.

## 8.3 CLI suite moves

```text
[ ] Move components/Commands -> components/CLI/Console if it is command execution
[ ] If components/Console exists, merge Commands into Console
[ ] Reduce components/Commands to bridge-only or delete it
```

## 8.4 DataStack suite moves

```text
[ ] Move components/Data        -> components/DataStack/Data
[ ] Move components/Database    -> components/DataStack/Database
[ ] Move components/Persistence -> components/DataStack/Persistence
```

Then normalize legacy owners:

```text
[ ] Move DataFoundation behavior into DataStack/Data
[ ] Reduce DataFoundation to bridge-only or delete it
[ ] Move DataLayer behavior into DataStack/Persistence
[ ] Reduce DataLayer to bridge-only or delete it
```

DataFoundation mapping:

```text
DataFoundation/DataTransfer/ReadDataObject        -> DataStack/Data/System/Flows/ReadDataObject
DataFoundation/DataTransfer/SerializeDataObject   -> DataStack/Data/System/Flows/SerializeDataObject
DataFoundation/DataTransfer/InspectDataShape      -> DataStack/Data/System/Capabilities/DataShape
DataFoundation/DataTransfer/FieldVisibility       -> DataStack/Data/System/Capabilities/FieldVisibility
DataFoundation/Collections                        -> DataStack/Data/System/Capabilities/Collections
DataFoundation/Arrays or Arrhae                   -> DataStack/Data/System/Capabilities/Arrays or PublicSurface
```

DataLayer mapping:

```text
DataLayer/QueryStoredData/BuildDataQuery          -> DataStack/Persistence/System/Flows/BuildDataQuery
DataLayer/QueryStoredData/CompileDataQuery        -> DataStack/Persistence/System/Flows/CompileDataQuery
DataLayer/QueryStoredData/ExecuteDataQuery        -> DataStack/Persistence/System/Flows/ExecuteDataQuery
DataLayer/QueryStoredData/ExplainDataQuery        -> DataStack/Persistence/System/Flows/ExplainDataQuery
DataLayer/QueryStoredData/DataQuery               -> DataStack/Persistence/System/Capabilities/QueryIntent
DataLayer/QueryStoredData/DataQueryPlan           -> DataStack/Persistence/System/Capabilities/QueryPlanning
DataLayer/QueryStoredData/DataQueryResult         -> DataStack/Persistence/System/Capabilities/QueryResult
DataLayer/QueryStoredData/DetectNPlusOneQuery     -> DataStack/Persistence/System/Capabilities/Diagnostics
DataLayer/ShapeStoredData                         -> DataStack/Persistence/System/Capabilities/StoredModelShape
```

Database split:

```text
[ ] Keep connections in Database
[ ] Keep query execution in Database
[ ] Keep transactions in Database
[ ] Keep schema in Database
[ ] Keep migrations in Database
[ ] Extract EntityManager to Persistence
[ ] Extract Repository to Persistence
[ ] Extract UnitOfWork to Persistence
[ ] Extract IdentityMap to Persistence
[ ] Extract Hydration to Persistence
[ ] Extract Mapping to Persistence
[ ] Extract ChangeTracking to Persistence
```

## 8.5 Identity suite moves

```text
[ ] Move components/Auth     -> components/Identity/Auth
[ ] Move components/Security -> components/Identity/Security if not HTTP-specific
[ ] Create components/Identity/Access only if access logic exists
[ ] Create components/Identity/Tokens only if token logic exists
```

If HTTP-specific security exists:

```text
components/HTTP/Security/
```

Do not mix identity security with HTTP middleware security.

## 8.6 Operations suite moves

```text
[ ] Move components/Events  -> components/Operations/Events
[ ] Move components/Logging -> components/Operations/Logging
[ ] Move components/Mail    -> components/Operations/Mail
[ ] Move components/Queue   -> components/Operations/Queue
[ ] Move Notifications only if existing code exists
```

No new queue/mail/notification behavior.

## 8.7 Presentation suite moves

```text
[ ] Move components/View -> components/Presentation/View
```

## 8.8 DeveloperTools suite moves

```text
[ ] Move components/DumpDebugger -> components/DeveloperTools/DumpDebugger
[ ] Move diagnostics component code -> components/DeveloperTools/Diagnostics if developer-facing
[ ] Create ArchitectureReview only if existing tooling/component code exists
```

---

# 9. Phase 3: Rename namespaces and imports

After moving files, normalize namespaces.

Canonical:

```php
Avax\Components\<Suite>\<Component>\System\...
```

Examples:

```php
Avax\Components\Application\Config\System\PublicSurface\Config
Avax\Components\HTTP\Session\System\PublicSurface\Session
Avax\Components\DataStack\Data\System\PublicSurface\Collection
Avax\Components\DataStack\Database\System\PublicSurface\Database
Avax\Components\DataStack\Persistence\System\PublicSurface\EntityManager
Avax\Components\Operations\Events\System\PublicSurface\Events
```

Tasks:

```text
[ ] Update namespace declarations
[ ] Update use statements
[ ] Update provider registrations
[ ] Update framework/System integration imports
[ ] Update composer autoload only when old bridges are stable
[ ] Update static factory references
[ ] Update facade references
[ ] Update helper function references
```

Do not keep two canonical namespaces.

Temporary bridge namespaces are allowed only in bridge files.

---

# 10. Phase 4: Bridge and delete duplicates

Duplicate policy:

```text
Real duplicate behavior:
  merge unique behavior into canonical owner, then delete duplicate.

Compatibility requirement:
  keep thin bridge temporarily.

Placeholder/skeleton:
  delete or move to docs.

Docs-only architecture note in PHP:
  delete or move to docs unless explicitly part of metadata system.
```

Bridge file rules:

```text
[ ] must contain no real behavior
[ ] must be marked @deprecated
[ ] must point to replacement class
[ ] must have test proving delegation
[ ] must have removal phase
```

Temporary bridge examples:

```text
components/Session            -> components/HTTP/Session
components/Middleware         -> components/HTTP/Middleware
components/DataFoundation     -> components/DataStack/Data
components/DataLayer          -> components/DataStack/Persistence
components/Commands           -> components/CLI/Console
```

Final desired state:

```text
[ ] components/Session deleted
[ ] components/Middleware deleted
[ ] components/DataFoundation deleted
[ ] components/DataLayer deleted
[ ] components/Commands deleted or bridge-only until removal
```

---

# 11. Phase 5: Architecture placement validation

Only now write architecture validation checks.

These tests/checkers do not test business behavior yet. They test whether the repo shape is honest.

Create:

```text
tooling/refactor/check-component-suite-structure.php
tooling/refactor/check-duplicate-owners.php
tooling/refactor/check-namespace-drift.php
tooling/refactor/check-public-surface.php
tooling/refactor/check-docs-mirror.php
tooling/refactor/check-runtime-leaks.php
tooling/refactor/check-forbidden-folders.php
```

Checks:

```text
[ ] components root contains only suite folders
[ ] no top-level Session real behavior
[ ] no top-level Middleware real behavior
[ ] no DataFoundation real behavior
[ ] no DataLayer real behavior
[ ] no lowercase components\ namespace in canonical code
[ ] no legacy Avax\DataFoundation namespace in canonical code
[ ] no legacy Avax\DataLayer namespace in canonical code
[ ] no legacy Avax\Session namespace in canonical code
[ ] no legacy Avax\Middleware namespace in canonical code
[ ] no PublicSurface class owns heavy behavior
[ ] no runtime adapter import outside framework/System/Capabilities/Runtime/Adapters
[ ] no docs pointing to old Foundation/HTTP or Foundation/DataLayer as canonical
[ ] no forbidden root production folders
```

Required output:

```text
PASS / FAIL
offending path
violated rule
required action
```

Non-zero exit on failure.

---

# 12. Phase 6: Test migration and architecture completeness tests

Now move and write tests.

Order:

```text
1. move existing tests to new suite paths
2. add architecture completeness tests
3. add integration tests to prove moved code is wired
4. add behavior tests for preserved features
5. add regression tests for previous bugs
6. add compatibility bridge tests
```

## 12.1 Test target structure

```text
tests/
  Architecture/
    ComponentSuiteStructureTest.php
    DuplicateOwnerTest.php
    NamespaceDriftTest.php
    PublicSurfaceTest.php
    DocsMirrorTest.php

  Unit/
    Components/
      Application/
      HTTP/
      CLI/
      DataStack/
      Identity/
      Operations/
      Presentation/
      DeveloperTools/

  Integration/
    Components/
      Application/
      HTTP/
      DataStack/
      Identity/
      Operations/

  Feature/
    Framework/
      BootApplicationFeatureTest.php
      HandleIncomingHttpFeatureTest.php
      RunConsoleCommandFeatureTest.php
      WorkerStateResetFeatureTest.php

  Compatibility/
    SessionBridgeTest.php
    MiddlewareBridgeTest.php
    DataFoundationBridgeTest.php
    DataLayerBridgeTest.php
    CommandsBridgeTest.php

  Support/
    Fakes/
    Fixtures/
    Assertions/
```

## 12.2 Architecture tests

```text
[ ] test_components_root_contains_only_allowed_suites()
[ ] test_each_real_component_has_system_root()
[ ] test_no_duplicate_session_owner_exists()
[ ] test_no_duplicate_middleware_owner_exists()
[ ] test_datafoundation_is_bridge_only_or_deleted()
[ ] test_datalayer_is_bridge_only_or_deleted()
[ ] test_no_components_namespace_exists_in_canonical_source()
[ ] test_public_surface_classes_are_thin()
[ ] test_docs_do_not_reference_obsolete_canonical_paths()
```

## 12.3 Integration tests

```text
[ ] Application suite boots Config, Container, Cache
[ ] HTTP suite handles Request -> Middleware -> Router -> Response
[ ] HTTP Session works through canonical path
[ ] CLI suite runs console command
[ ] DataStack Data collections still work
[ ] DataStack Database query/transaction still works
[ ] DataStack Persistence EntityManager/Repository still works if present
[ ] Identity Auth still works
[ ] Operations Events and Logging still work
```

## 12.4 Behavior tests

Write behavior tests after the moved architecture is in place.

Examples:

```text
[ ] Cache read/write/remember/forget still works
[ ] Container resolve/call/scope still works
[ ] Config read/load still works
[ ] Data Collection map/filter/reduce still works
[ ] Data Arrhae read/write path still works
[ ] Database transaction rollback still works
[ ] Persistence identity map still works
[ ] Router route groups/middleware/named routes still work
[ ] Session read/write/flash/regenerate/terminate still works
[ ] Queue existing dispatch behavior still works
[ ] Mail existing send/build behavior still works
```

## 12.5 Compatibility tests

Only for bridges kept temporarily:

```text
[ ] old Session API delegates to HTTP Session
[ ] old Middleware API delegates to HTTP Middleware
[ ] old DataFoundation API delegates to DataStack Data
[ ] old DataLayer API delegates to DataStack Persistence
[ ] old Commands API delegates to CLI Console
```

---

# 13. Phase 7: Governance and coding standard checks

Only after architecture and tests are in place.

Run:

```bash
composer validate --no-check-publish
composer dump-autoload -o
php -l changed PHP files
vendor/bin/phpunit
vendor/bin/phpstan analyse framework components tests
vendor/bin/psalm
vendor/bin/rector --dry-run
```

Then run architecture checkers:

```bash
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-docs-mirror.php
php tooling/refactor/check-runtime-leaks.php
php tooling/refactor/check-forbidden-folders.php
```

Then run governance review against all `how-to-*.md` files.

Create:

```text
Code-Review-And-ToDo/governance-compliance-after-suite-migration.md
```

Required sections:

```text
GOVERNANCE INVENTORY
STRUCTURE COMPLIANCE
NAMING COMPLIANCE
OWNERSHIP COMPLIANCE
PUBLIC SURFACE COMPLIANCE
TESTING COMPLIANCE
DOCUMENTATION COMPLIANCE
VIOLATIONS
REQUIRED FIXES
FINAL STATUS
```

The architecture governance says docs and code must not disagree, and changes should preserve clarity, ownership, naming, testability, safety and maintainability. 

Also run kluster review after file changes if configured. The project rules explicitly require kluster review after file creation, modification, deletion or code change. 

---

# 14. Phase 8: Documentation last

Only after:

```text
[ ] files are moved
[ ] namespaces are normalized
[ ] duplicate owners are removed or bridged
[ ] tests pass
[ ] governance checks pass
```

Then write docs.

Docs target:

```text
docs/
  architecture/
    component-suite-architecture.md
    framework-runtime-architecture.md
    public-surface-policy.md
    namespace-policy.md
    bridge-removal-policy.md

  decisions/
    00xx-component-suite-architecture.md
    00xx-http-is-component-suite.md
    00xx-datastack-suite.md
    00xx-application-suite.md
    00xx-identity-suite.md
    00xx-operations-suite.md
    00xx-cli-suite.md
    00xx-presentation-suite.md
    00xx-developertools-suite.md

  framework/
    System/
      how-this-works.md

  components/
    Application/
      Config/System/how-this-works.md
      Container/System/how-this-works.md
      Cache/System/how-this-works.md
      Filesystem/System/how-this-works.md
      Validation/System/how-this-works.md
      Text/System/how-this-works.md
      DateTime/System/how-this-works.md

    HTTP/
      System/how-this-works.md
      Request/System/how-this-works.md
      Response/System/how-this-works.md
      Router/System/how-this-works.md
      Middleware/System/how-this-works.md
      Session/System/how-this-works.md
      Cookies/System/how-this-works.md
      URI/System/how-this-works.md
      Uploads/System/how-this-works.md

    CLI/
      Console/System/how-this-works.md

    DataStack/
      Data/System/how-this-works.md
      Database/System/how-this-works.md
      Persistence/System/how-this-works.md

    Identity/
      Auth/System/how-this-works.md
      Access/System/how-this-works.md
      Security/System/how-this-works.md
      Tokens/System/how-this-works.md

    Operations/
      Events/System/how-this-works.md
      Logging/System/how-this-works.md
      Mail/System/how-this-works.md
      Queue/System/how-this-works.md
      Notifications/System/how-this-works.md

    Presentation/
      View/System/how-this-works.md

    DeveloperTools/
      Diagnostics/System/how-this-works.md
      DumpDebugger/System/how-this-works.md
```

For every `how-this-works.md`:

```text
[ ] what this owns
[ ] what this does not own
[ ] public surface
[ ] flows
[ ] capabilities
[ ] configuration
[ ] foundation
[ ] dependency rules
[ ] debug-first path
[ ] failure modes
[ ] mermaid diagram where flow matters
```

Delete obsolete docs:

```text
[ ] docs/Foundation/HTTP
[ ] docs/Foundation/DataLayer
[ ] docs/Foundation/DataHandling
[ ] component-local docs that duplicate canonical docs
```

Documentation governance requires documentation to explain real structure and says that if docs and code disagree, the system is lying somewhere. 

---

# 15. Phase 9: Final cleanup

```text
[ ] Delete empty old component folders
[ ] Delete obsolete bridge files if compatibility window is not needed
[ ] Mark remaining bridges with removal phase
[ ] Delete old component-local docs
[ ] Delete old tests for dead architecture
[ ] Delete generated dumps
[ ] Ensure .env is not committed as real environment secret file
[ ] Ensure .gigaide and IDE artifacts are ignored unless intentionally tracked
[ ] Regenerate autoload
[ ] Run full quality gates
```

Final report:

```text
Code-Review-And-ToDo/component-suite-migration-final-report.md
```

Required sections:

```text
1. Final suite tree
2. Moved components
3. Deleted duplicates
4. Bridges kept
5. Bridges scheduled for removal
6. Namespace normalization result
7. Architecture checker result
8. Test result
9. Static analysis result
10. Governance result
11. Documentation result
12. Remaining risks
13. Next phase recommendation
```

---

# 16. Exact AI execution prompt

Use this as the final prompt for Codex/agent.

```text
You are migrating Avax to a component-suite architecture.

This is not a rewrite.
This is not a feature expansion.
This is not an opportunity to write new components from scratch.

Priority order:
1. Architecture first.
2. Move existing code into the new architecture.
3. Rename files, folders, namespaces, imports and providers.
4. Merge unique behavior into canonical owners.
5. Delete duplicates or reduce them to thin compatibility bridges.
6. Then write and move tests.
7. Use tests to verify architecture placement, component integration and behavior preservation.
8. Then run governance and coding-standard checks against all how-to-*.md files.
9. Only after code, tests and governance pass, write documentation.
10. Then create a final report.

New component-suite root:
components/
  Application/
  HTTP/
  CLI/
  DataStack/
  Identity/
  Operations/
  Presentation/
  DeveloperTools/

Canonical component shape:
components/<Suite>/<Component>/System/
  PublicSurface/
  Flows/
  Capabilities/
  Configuration/
  Foundation/

Final namespace:
Avax\Components\<Suite>\<Component>\System\...

Framework namespace:
Avax\Framework\System\...

Do not keep lowercase components\ namespace as canonical.
Do not keep old Avax\<Component>\ namespace as canonical after migration.
Legacy namespaces are allowed only in explicit bridge files.

Move components:
- Config -> Application/Config
- Container -> Application/Container
- Cache -> Application/Cache
- Filesystem -> Application/Filesystem
- Validation -> Application/Validation
- Text -> Application/Text
- DateTime -> Application/DateTime
- HTTP stays HTTP suite
- Session -> HTTP/Session
- Middleware -> HTTP/Middleware
- Request/Response/Router -> HTTP sub-components if present
- Commands/Console -> CLI/Console
- Data -> DataStack/Data
- DataFoundation -> DataStack/Data, then bridge/delete
- Database -> DataStack/Database
- Persistence -> DataStack/Persistence
- DataLayer -> DataStack/Persistence, then bridge/delete
- Auth -> Identity/Auth
- Security -> Identity/Security unless HTTP-specific
- Events -> Operations/Events
- Logging -> Operations/Logging
- Mail -> Operations/Mail
- Queue -> Operations/Queue
- View -> Presentation/View
- DumpDebugger -> DeveloperTools/DumpDebugger
- Diagnostics -> DeveloperTools/Diagnostics if developer-facing

Rules:
- Do not create empty decorative folders.
- Do not keep duplicate real owners.
- Do not delete behavior before comparing it with canonical owner.
- Do not rewrite working code.
- Do not write from scratch unless the plan explicitly introduces a new file/checker/bridge.
- Do not add new feature scope.
- PublicSurface must stay thin.
- Flows execute behavior.
- Capabilities power behavior.
- Configuration assembles.
- Foundation supports with tiny primitives/failures only.
- Framework/System remains lifecycle owner.
- Components remain reusable capabilities.
- Documentation comes last.

Implementation phases:
1. Create Code-Review-And-ToDo/component-suite-migration-truth.md.
2. Inventory every component and classify target suite/component/action.
3. Create target suite skeleton.
4. Move existing code into target suites.
5. Rename namespaces and imports.
6. Merge unique behavior into canonical owners.
7. Delete duplicate folders or reduce them to bridge-only.
8. Update composer autoload incrementally.
9. Add architecture checkers:
   - check-component-suite-structure.php
   - check-duplicate-owners.php
   - check-namespace-drift.php
   - check-public-surface.php
   - check-docs-mirror.php
   - check-runtime-leaks.php
   - check-forbidden-folders.php
10. Move existing tests to new suite paths.
11. Write architecture tests.
12. Write integration tests proving moved components are wired.
13. Write behavior tests for preserved behavior.
14. Write bridge tests for temporary compatibility.
15. Run full test suite.
16. Run PHPStan/Psalm/Rector/code style.
17. Run architecture checkers.
18. Run governance compliance against all how-to-*.md files.
19. Run kluster_code_review_auto if configured after file changes.
20. Only after all checks pass, write docs under docs/.
21. Delete obsolete docs and stale local component docs.
22. Create Code-Review-And-ToDo/component-suite-migration-final-report.md.

Acceptance criteria:
- components root contains only suite folders plus temporary bridges if explicitly documented.
- Every real component lives under a suite.
- Every real component has System/ as its system root.
- No duplicate Session, Middleware, DataFoundation, DataLayer, Commands owner remains.
- No canonical code uses lowercase components\ namespace.
- No old namespace remains outside bridges.
- Composer autoload reflects Avax\Framework and Avax\Components.
- Architecture tests pass.
- Component integration tests pass.
- Behavior tests pass.
- Governance checks pass.
- Docs mirror actual source.
- Final report lists all moves, deletions, bridges and remaining risks.
```

---

# 17. Short priority list

If the agent needs a short execution order, use this:

```text
1. Inventory
2. Suite skeleton
3. Move HTTP closure into final HTTP suite
4. Move DataStack
5. Move Application
6. Move Identity
7. Move Operations
8. Move CLI
9. Move Presentation
10. Move DeveloperTools
11. Namespace normalization
12. Duplicate deletion/bridges
13. Architecture checkers
14. Tests
15. Governance checks
16. Docs
17. Final report
```

Ovo je sada pravi plan: prvo arhitektura i postojeći kod, zatim testovi koji proveravaju da li je arhitektura stvarno nastala, zatim standardi, pa tek onda dokumentacija. 🧊
