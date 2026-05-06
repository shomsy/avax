Da. Sada idemo **jedan master plan**, bez “kasnije ćemo”. Radi se sve, ali redom. Razlika je samo u prioritetu
egzekucije, ne u tome da li ulazi u plan.
Dakle, prokopaj avax-backup.txt i pogledaj main branch na git-u.

Prvo hladna istina: `avax-backup.txt` ne treba tretirati kao smeće ili generated artifact dok traje recovery. Raniji
cleanup plan je hteo da ga briše, ali sada je on **Recovery Source of Truth**. Stari audit jasno kaže da su izgubljeni
ili osiromašeni Collections, Carbon/Date, Blade/BladeOne, Mail/Notifications, Queue/Jobs, ORM/query builder širina,
string helperi, I18N, rich debugging i Console UI. Takođe, stari missing-features izveštaj eksplicitno pominje nestale
oblasti kao `Commands`, `DataFoundation`, `DataLayer`, `Facade`, `Middlewares`, `tests` i `compat.php`.

Još jedna bitna stvar: postojeći data-stack plan već daje pravila koja moraju ostati: `Data` je za in-memory data,
`Database` za konekcije/query/transactions/schema/migrations, a `Persistence` za EntityManager, Repository, UnitOfWork,
IdentityMap, Mapping, Hydration i ChangeTracking. To ne diramo. Samo vraćamo mišiće iz backup-a na prava mesta.

---

# AvaX Feature Recovery & Muscle Restoration Master Plan

## 0. Glavna odluka

Cilj nije da se vratimo na staru haotičnu arhitekturu.

Cilj je:

```text
Stari feature muscle
+
Nova component-suite arhitektura
+
System-design-grade runtime discipline
```

Drugim rečima:

```text
Ne vraćamo stare foldere.
Vraćamo stare sposobnosti.
```

`avax-backup.txt` ostaje privremeni recovery artifact dok se ne završi kompletna feature recovery matrica. Ne briše se.
Ne učitava se u autoload. Ne tretira se kao production source.

---

# 1. Nova pravila za ovaj recovery pass

Ovo daš AI agentu kao obavezna pravila.

```text
You are restoring AvaX feature depth from avax-backup.txt into the new component-suite architecture.

This is not a rewrite.
This is not a rollback.
This is not a cleanup-only pass.

Goal:
Recover useful old features from avax-backup.txt and place them into the correct modern AvaX architecture.

Primary rule:
Old code is source material, not final architecture.

Execution order:
1. Read avax-backup.txt.
2. Inventory old features.
3. Map old features to new component-suite owners.
4. Move or port existing code into the new owner.
5. Rename namespaces and imports.
6. Delete or bridge old paths only after behavior is recovered.
7. Add tests after code is placed.
8. Use tests to prove recovered behavior exists.
9. Run governance checks against how-to-*.md.
10. Run kluster_code_review_auto after file changes if configured.
11. Write documentation last.

Do not:
- rebuild from scratch unless old code is unusable
- restore old folder layout
- create duplicate owners
- hide old monoliths in random helpers
- keep vendor-like files in PublicSurface
- put runtime-specific APIs inside component core
- write docs before code and tests
```

Kluster mora ostati u planu jer projektna pravila kažu da se `kluster_code_review_auto` pokreće posle bilo kakvog
kreiranja, izmene ili brisanja fajla, a dependency check pre promena dependency fajlova.

---

# 2. Finalna target arhitektura

Ostaje component-suite model:

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

Svaka realna komponenta:

```text
components/<Suite>/<Component>/System/
  PublicSurface/
  Flows/
  Capabilities/
  Configuration/
  Foundation/
```

Framework runtime ostaje odvojen:

```text
framework/
  System/
    PublicSurface/
    Flows/
    Capabilities/
    Configuration/
    Foundation/
```

`framework/System` je runtime lifecycle owner. Komponente su reusable capabilities.

---

# 3. Nova odluka za “vendor-like monolite”

Ti si rekao da hoćeš ovo:

```text
globalni Facade folder
Carbon clone u core
BladeOne copy u core
Ignition/Whoops copy u core
vendor-like monoliti
```

Može. Ali mora na pravo mesto.

## 3.1 Globalni Facade folder

Ne vraćamo stari `components/Facade/` kao haotičan dumping ground.

Vraćamo ga kao kontrolisanu core capability:

```text
components/Application/Facade/System/
  PublicSurface/
    Facade.php
    FacadeInterface.php

  Capabilities/
    FacadeResolver/
      ResolveFacadeRoot.php
      FacadeAccessor.php
      FacadeRootRegistry.php

    FacadeState/
      ClearResolvedFacades.php
      FacadeStateResetter.php

  Configuration/
    FacadeProvider.php

  Foundation/
    Failure/
      FacadeRootNotFound.php
      InvalidFacadeAccessor.php
```

Pravilo:

```text
Application/Facade owns only facade machinery.

Actual facades live inside the owning component:
- components/DataStack/Database/System/PublicSurface/Facades/DB.php
- components/HTTP/Router/System/PublicSurface/Facades/Route.php
- components/Application/Cache/System/PublicSurface/Facades/Cache.php
- components/Identity/Auth/System/PublicSurface/Facades/Auth.php
```

Znači, globalni Facade postoji, ali ne postaje kanta.

---

## 3.2 Carbon clone u core

Ne ide u `framework/System`.

Ide u:

```text
components/Application/DateTime/System/
  PublicSurface/
    Date.php
    DateTime.php
    Carbon.php

  Capabilities/
    Clock/
      ClockInterface.php
      SystemClock.php
      FrozenClock.php

    CarbonCompat/
      Carbon.php
      CarbonInterval.php
      CarbonPeriod.php
      CarbonImmutable.php

    Timezones/
      Timezone.php
      ResolveTimezone.php

    Formatting/
      FormatDateTime.php
      HumanizeDuration.php
      DiffForHumans.php

    Parsing/
      ParseDateTime.php

  Configuration/
    DateTimeProvider.php
    DateTimeConfiguration.php

  Foundation/
    Values/
      Duration.php
      DateRange.php
```

Pravilo:

```text
Carbon-like API is allowed as Application/DateTime compatibility capability.
ClockInterface remains the clean system contract.
CarbonCompat must not leak into framework/System.
```

To znači: možeš imati `Carbon.php`, ali ga ne stavljaš u runtime jezgro. Core framework lifecycle ne zavisi od njega.

---

## 3.3 BladeOne copy u core

Ne ide u root. Ne ide u `framework/System`.

Ide u:

```text
components/Presentation/View/System/
  PublicSurface/
    View.php
    ViewInterface.php

  Flows/
    RenderView/
      RenderView.php
      ResolveViewTemplate.php
      PrepareViewData.php

  Capabilities/
    Engines/
      TemplateEngine.php
      TemplateEngineInterface.php

      BladeOne/
        BladeOneTemplateEngine.php
        BladeOneCompiler.php
        BladeOneRuntime.php
        BladeOneDirectives.php

    Templates/
      TemplateFinder.php
      TemplateName.php

    Compilation/
      CompileTemplate.php
      CompiledTemplateCache.php

    Escaping/
      EscapeHtml.php
      EscapingPolicy.php

    Layouts/
    Sections/
    Partials/
    Components/

  Configuration/
    ViewProvider.php
    ViewConfiguration.php

  Foundation/
    Failure/
      ViewNotFound.php
      ViewRenderFailed.php
```

Pravilo:

```text
BladeOne copy is allowed only behind TemplateEngineInterface.
View PublicSurface must not expose BladeOne internals.
```

Dakle, možeš imati BladeOne, ali kao engine capability.

---

## 3.4 Ignition/Whoops copy u core

Ne ide u HTTP. Ne ide u `framework/System`.

Ide u:

```text
components/DeveloperTools/DumpDebugger/System/
  PublicSurface/
    DumpDebugger.php
    ErrorScreen.php

  Flows/
    RenderDebugError/
      RenderDebugError.php
      CollectThrowableContext.php
      RenderHtmlErrorScreen.php
      RenderCliErrorScreen.php

  Capabilities/
    ErrorScreens/
      IgnitionCompat/
        IgnitionErrorScreen.php
        SolutionProvider.php
        StackFrameRenderer.php

      WhoopsCompat/
        WhoopsErrorScreen.php
        WhoopsFrameInspector.php

    Dumps/
      SafeDump.php
      DumpValue.php
      DumpContext.php

    SourcePreview/
      ReadSourceSnippet.php
      SourceFrame.php

    Redaction/
      RedactSecrets.php
      SensitiveKeyDetector.php

  Configuration/
    DumpDebuggerProvider.php
    DumpDebuggerConfiguration.php

  Foundation/
    Failure/
      DebugScreenRenderFailed.php
```

Pravilo:

```text
Ignition/Whoops-like code is dev-only.
It must never run in production unless explicitly allowed.
It must redact secrets.
It must not be required by framework/System.
```

---

## 3.5 Vendor-like monoliti

Dozvoljeni su, ali u karantinu.

Pravilo:

```text
Vendor-like monoliths may exist only inside a component Capability/Vendor or Capability/<EngineName> folder.
They must be wrapped by AvaX contracts.
They must not be imported directly outside their owning component.
They must keep license/header information.
They must be excluded from normal architecture-style expectations if they are copied third-party-style code.
They must have adapter tests.
```

Primeri:

```text
components/Application/DateTime/System/Capabilities/CarbonCompat/Vendor/
components/Presentation/View/System/Capabilities/Engines/BladeOne/Vendor/
components/DeveloperTools/DumpDebugger/System/Capabilities/ErrorScreens/WhoopsCompat/Vendor/
```

---

# 4. Recovery source policy

Napravi folder:

```text
recovery/
  legacy/
    avax-backup.txt

  reports/
    old-feature-inventory.md
    old-public-api-inventory.md
    old-vendor-monolith-inventory.md
    recovered-feature-matrix.md
    unrecovered-feature-matrix.md
```

Ako ne želiš novi `recovery/` folder u root-u, stavi u:

```text
EVIDENCE/recovery/
```

Moj izbor:

```text
EVIDENCE/recovery/
```

jer je to audit/migration artefakt, ne source.

Pravila:

```text
[ ] avax-backup.txt is not production source.
[ ] avax-backup.txt is not autoloaded.
[ ] avax-backup.txt is not deleted until all recovery matrices are closed.
[ ] architecture checkers must ignore avax-backup.txt as source, but recovery scripts may read it.
```

---

# 5. Master execution order

Ovo je redosled. Nema “kasnije”, sve je u planu.

```text
PHASE 0  Recovery setup
PHASE 1  Extract complete feature inventory from avax-backup.txt
PHASE 2  Build old-to-new ownership map
PHASE 3  Recover P0 framework muscles
PHASE 4  Recover P1 developer/runtime muscles
PHASE 5  Restore requested vendor-like monoliths into correct places
PHASE 6  Recover batteries-included features
PHASE 7  Integrate features with framework/System
PHASE 8  Tests
PHASE 9  Governance checks
PHASE 10 Documentation
PHASE 11 Final cleanup
```

---

# PHASE 0: Recovery setup

```text
[ ] Create EVIDENCE/recovery/
[ ] Move or copy avax-backup.txt reference into EVIDENCE/recovery/source-reference.md
[ ] Keep original avax-backup.txt in place until recovery is complete
[ ] Add note: do not autoload avax-backup.txt
[ ] Add note: do not delete avax-backup.txt during recovery
[ ] Add recovery status file: EVIDENCE/recovery/recovery-status.md
```

Create:

```text
EVIDENCE/recovery/recovery-status.md
```

Content:

```md
# AvaX Feature Recovery Status

## Source of Truth

- avax-backup.txt

## Goal

Recover old AvaX feature depth into the new component-suite architecture.

## Rule

Old code is source material, not final architecture.

## Status

- Inventory: pending
- Mapping: pending
- Code recovery: pending
- Tests: pending
- Governance: pending
- Docs: pending
```

---

# PHASE 1: Extract complete feature inventory from `avax-backup.txt`

AI mora prvo da izvuče inventar, ne da odmah piše kod.

Create:

```text
EVIDENCE/recovery/old-feature-inventory.md
EVIDENCE/recovery/old-public-api-inventory.md
EVIDENCE/recovery/old-vendor-monolith-inventory.md
EVIDENCE/recovery/old-tests-inventory.md
```

AI zadatak:

```text
Scan avax-backup.txt and extract every old component, class, function, helper, facade, command, test, and vendor-like monolith.

Classify every item as:
- real framework feature
- public API
- internal mechanism
- vendor-like monolith
- package-local tooling
- tests
- docs
- generated artifact
- duplicate
- obsolete
```

Tabela:

```md
| Old path/class | Old feature | Category | New owner | Recovery action |
|---|---|---|---|---|
| Collection.php | Collection API | real feature | DataStack/Data | restore |
| Arr.php | Array helpers | real feature | DataStack/Data | restore |
| Carbon.php | Date API | vendor-like compat | Application/DateTime | restore isolated |
| BladeOne.php | Template engine | vendor-like engine | Presentation/View | restore isolated |
| Whoops/Ignition files | Debug error screen | dev tool | DeveloperTools/DumpDebugger | restore isolated |
```

Posebno izvući stare oblasti koje su već identifikovane u auditima: Collections, Carbon/Date, Blade/BladeOne,
Mail/Notifications, Queues/Jobs, ORM/query builders, String/Text helperi, I18N, Ignition/Flare debugging i Console UI.

---

# PHASE 2: Build old-to-new ownership map

Create:

```text
EVIDENCE/recovery/old-to-new-ownership-map.md
```

Target mapping:

```text
Old Avax.php
  -> framework/System/PublicSurface/Avax.php
  -> maybe components/Application/Facade for facade bootstrap

Old Commands
  -> components/CLI/Console/System

Old DataFoundation
  -> components/DataStack/Data/System

Old DataLayer
  -> components/DataStack/Persistence/System

Old Facade
  -> components/Application/Facade/System

Old Middlewares
  -> components/HTTP/Middleware/System

Old HTTP Context
  -> components/HTTP/Context/System or components/HTTP/System/Capabilities/Context

Old HTTP Security
  -> components/HTTP/Security/System

Old HTTP URI
  -> components/HTTP/URI/System

Old Collection / Arr
  -> components/DataStack/Data/System

Old Builder / Schema / Query
  -> components/DataStack/Database/System

Old EntityManager / UnitOfWork / Repository
  -> components/DataStack/Persistence/System

Old Str / Stringable / Inflector
  -> components/Application/Text/System

Old Carbon / Date
  -> components/Application/DateTime/System/Capabilities/CarbonCompat

Old BladeOne / Blade
  -> components/Presentation/View/System/Capabilities/Engines/BladeOne

Old Mail / Mailer
  -> components/Operations/Mail/System

Old Queue / Job / Batch / Task
  -> components/Operations/Queue/System

Old Notification
  -> components/Operations/Notifications/System

Old Translator / Lang
  -> components/Application/Localization/System

Old Ignition / Whoops / Flare-like debug
  -> components/DeveloperTools/DumpDebugger/System

Old ProgressBar / Table / Questions
  -> components/CLI/Console/System/Capabilities/UI
```

Ako `Application/Localization` ne postoji, kreirati:

```text
components/Application/Localization/System/
```

jer I18N je application-level service.

---

# PHASE 3: Recover P0 framework muscles

P0 znači: bez ovoga AvaX ostaje skeleton.

## 3.1 DataFoundation -> DataStack/Data

Target:

```text
components/DataStack/Data/System/
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
    LazyCollections/
    DataPath/
    DataTransfer/
    DataShape/
    FieldVisibility/
    ObjectHandling/
    Structures/
    Values/
    Composites/
    Validation/

  Configuration/
    DataProvider.php
    DataConfiguration.php

  Foundation/
    Failure/
```

Restore checklist:

```text
[ ] Restore Arrhae public API
[ ] Restore Arr/array helpers
[ ] Restore Collection full API
[ ] Restore LazyCollection if present
[ ] Restore Enumerable contract if present
[ ] Restore DataPath / FieldPath
[ ] Restore InputData / OutputData
[ ] Restore object-to-array conversion
[ ] Restore shape inspection
[ ] Restore field visibility
[ ] Restore structure/value objects
[ ] Restore data validation primitives
[ ] Remove DataFoundation real behavior
[ ] Keep DataFoundation bridge only if required
```

Collection methods to verify:

```text
[ ] map
[ ] filter
[ ] reduce
[ ] flatMap
[ ] groupBy
[ ] pluck
[ ] flatten
[ ] merge
[ ] chunk
[ ] sortBy
[ ] unique
[ ] reverse
[ ] take
[ ] skip
[ ] sum
[ ] avg
[ ] min
[ ] max
[ ] contains
[ ] firstWhere
[ ] toJson
[ ] implode
[ ] wrap
[ ] empty
```

DataFoundation/DataLayer had known namespace drift and many files needing migration, with reports listing hundreds of
DataFoundation files and a dozen DataLayer files under old namespaces.

---

## 3.2 Container muscle restoration

Target:

```text
components/Application/Container/System/
  PublicSurface/
    Container.php
    ContainerInterface.php

  Flows/
    RegisterService/
    ResolveService/
    CallFunction/
    OpenScope/
    CloseScope/
    CompileContainer/
    ExplainServiceResolution/
    AuditContainerScopes/
    DetectCircularDependencies/

  Capabilities/
    Bindings/
    Resolution/
    Calls/
    Scopes/
    Providers/
    Tags/
    Aliases/
    Decorators/
    LazyServices/
    DeferredProviders/
    Compilation/
    Diagnostics/
    Graph/
    ScopeSafety/
    ProviderBootPlan/
    LifetimePlan/
    ArtifactMetadata/

  Configuration/
    ContainerProvider.php
    ContainerConfiguration.php

  Foundation/
    Failure/
```

Restore checklist:

```text
[ ] alias()
[ ] tagged()
[ ] decorate()
[ ] lazy()
[ ] defer()
[ ] provider composition
[ ] scoped bindings
[ ] singleton/transient/scoped lifetimes
[ ] compile container
[ ] compile report
[ ] runtime report
[ ] provider boot plan
[ ] lifetime plan
[ ] service graph
[ ] resolution diagnostics
[ ] circular dependency detection
[ ] unused binding detector
[ ] singleton safety audit
[ ] request-scoped dependency violation detector
[ ] benchmark harness if present
```

---

## 3.3 Config recovery

Target:

```text
components/Application/Config/System/
  PublicSurface/
    Config.php
    ConfigInterface.php

  Flows/
    LoadConfiguration/
    ValidateConfiguration/
    CompileConfiguration/
    ExplainConfigurationValue/
    DumpSafeConfiguration/

  Capabilities/
    Repository/
    Sources/
    Schema/
    Environment/
    Redaction/
    CompiledConfig/
    Profiles/

  Configuration/
    ConfigProvider.php

  Foundation/
    Failure/
```

Restore checklist:

```text
[ ] file config loading
[ ] dot notation get/set/has
[ ] env override support
[ ] typed config schema
[ ] required env detection
[ ] config source tracing
[ ] config safe dump
[ ] secret redaction
[ ] compiled config
[ ] immutable config after boot
```

---

## 3.4 Middleware pipeline recovery

Target:

```text
components/HTTP/Middleware/System/
  PublicSurface/
    Middleware.php
    MiddlewareInterface.php

  Flows/
    RunMiddlewarePipeline/
    ResolveNextMiddleware/
    ShortCircuitMiddleware/
    ExplainMiddlewarePipeline/
    ProfileMiddlewarePipeline/

  Capabilities/
    Pipeline/
    MiddlewareStack/
    MiddlewarePriority/
    MiddlewareGroups/
    MiddlewareAliases/
    Psr15Adapter/
    ShortCircuiting/
    MiddlewareMetrics/
```

Restore checklist:

```text
[ ] middleware interface
[ ] global middleware pipeline
[ ] route middleware pipeline
[ ] middleware priority
[ ] middleware aliases
[ ] middleware groups
[ ] short-circuit response behavior
[ ] PSR-15 adapter if old code had it
[ ] middleware profiling
```

---

## 3.5 Events recovery

Target:

```text
components/Operations/Events/System/
  PublicSurface/
    Events.php
    EventDispatcher.php

  Flows/
    DispatchEvent/
    ListenToEvent/
    ForgetListener/
    CompileListenerMap/

  Capabilities/
    ListenerRegistry/
    ListenerPriority/
    EventSubscribers/
    ListenerGraph/
    CompiledListeners/
    EventTimeline/
    AsyncBoundary/
    Outbox/
```

Restore checklist:

```text
[ ] EventDispatcher
[ ] ListenerRegistry
[ ] listener priority
[ ] subscribers
[ ] compiled listener map
[ ] event timeline
[ ] async dispatch boundary
[ ] event fake later in testing phase
```

---

## 3.6 Database recovery and hardening

Target:

```text
components/DataStack/Database/System/
  PublicSurface/
    Database.php
    DB.php
    Schema.php

  Flows/
    ConnectToDatabase/
    RunDatabaseQuery/
    RunDatabaseTransaction/
    RunDatabaseMigration/
    BuildDatabaseSchema/
    ExplainDatabaseQuery/

  Capabilities/
    Connections/
    ConnectionPool/
    QueryBuilder/
    QueryExecution/
    Bindings/
    Transactions/
    Savepoints/
    Migrations/
    Schema/
    QueryTimeline/
    SlowQueryDetection/
    QueryFingerprinting/
    ReadWriteSplitting/
    ReplicaRouting/

  Configuration/
    DatabaseProvider.php
    DatabaseConfiguration.php

  Foundation/
    Failure/
```

Restore checklist:

```text
[ ] advanced query builder
[ ] schema builder
[ ] migrations
[ ] migration commands
[ ] transaction manager
[ ] nested transactions/savepoints
[ ] deadlock retry policy
[ ] isolation levels
[ ] query bindings policy
[ ] raw SQL audit
[ ] slow query detector
[ ] query fingerprinting
[ ] query timeline
[ ] connection pool
[ ] lazy connection pool
[ ] multi-tenant pool
[ ] read/write split
[ ] replica routing
[ ] bulk insert/update/upsert
```

The backup TODO already calls out connection pooling, lazy pool, multi-tenant pool, schema builder and migration
commands as desired Database recovery work.

---

## 3.7 Persistence recovery

Target:

```text
components/DataStack/Persistence/System/
  PublicSurface/
    Persistence.php
    EntityManager.php
    Repository.php

  Flows/
    FindEntity/
    SaveEntity/
    DeleteEntity/
    FlushChanges/
    RunUnitOfWork/
    BuildDataQuery/
    CompileDataQuery/
    ExecuteDataQuery/
    ExplainDataQuery/

  Capabilities/
    Repositories/
    UnitOfWork/
    IdentityMap/
    Mapping/
    Hydration/
    ChangeTracking/
    QueryIntent/
    QueryPlanning/
    QueryResult/
    StoredModelShape/
    Specifications/
    Diagnostics/

  Configuration/
    PersistenceProvider.php
    PersistenceConfiguration.php

  Foundation/
    Failure/
```

Restore checklist:

```text
[ ] EntityManager behavior
[ ] Repository behavior
[ ] RepositoryFactory
[ ] UnitOfWork lifecycle
[ ] IdentityMap
[ ] scoped IdentityMap reset
[ ] ChangeTracker
[ ] ChangeSet
[ ] FlushPlan
[ ] Hydrator
[ ] EntityExtractor
[ ] Mapping metadata
[ ] FieldMapping
[ ] RelationMapping
[ ] Specifications
[ ] Query intent
[ ] Stored model shape
[ ] N+1 detector
[ ] transaction boundary integration
```

Database must not keep ORM long-term, and the existing data-stack rule already says Persistence owns EntityManager,
Repository, UnitOfWork, IdentityMap, Mapping, Hydration and ChangeTracking.

---

# PHASE 4: Recover P1 developer/runtime muscles

## 4.1 Filesystem

Target:

```text
components/Application/Filesystem/System/
  PublicSurface/
    Filesystem.php
    Storage.php

  Flows/
    ReadFile/
    WriteFile/
    DeleteFile/
    CopyFile/
    MoveFile/
    EnsureDirectory/

  Capabilities/
    Disks/
    Files/
    Directories/
    Paths/
    Streams/
    Permissions/
    Visibility/
    Checksums/
    Locks/
    TemporaryUrls/
```

Restore:

```text
[ ] Disks
[ ] Paths
[ ] Files
[ ] Directories
[ ] atomic writes
[ ] stream reads/writes
[ ] file locks
[ ] checksum/hash
[ ] path traversal protection
[ ] visibility/permissions
[ ] temporary URLs
```

---

## 4.2 Logging

Target:

```text
components/Operations/Logging/System/
  PublicSurface/
    Logger.php
    Log.php

  Flows/
    WriteLogRecord/
    WriteEmergencyLog/
    FlushLogs/

  Capabilities/
    Records/
    Channels/
    Writers/
    Formatters/
    Processors/
    Context/
    Redaction/
    Buffering/
    Sampling/
```

Restore:

```text
[ ] Writers
[ ] file writer
[ ] stream writer
[ ] JSON formatter
[ ] processors
[ ] structured logs
[ ] correlation id
[ ] trace id
[ ] request id
[ ] secret redaction
[ ] buffered writer
[ ] context reset per request
```

---

## 4.3 Text

Target:

```text
components/Application/Text/System/
  PublicSurface/
    Text.php
    Str.php
    Stringable.php

  Capabilities/
    CaseConversion/
    Slugs/
    Inflection/
    Matching/
    Trimming/
    Unicode/
    Regex/
```

Restore:

```text
[ ] Str
[ ] Stringable
[ ] Inflector
[ ] slug
[ ] plural
[ ] singular
[ ] camel
[ ] snake
[ ] kebab
[ ] studly
[ ] headline
[ ] contains
[ ] startsWith
[ ] endsWith
[ ] limit
[ ] excerpt
[ ] normalize whitespace
[ ] transliteration
[ ] regex helpers
```

The old audit specifically identifies Str, Stringable and Inflector as lost or reduced Text functionality.

---

## 4.4 Validation

Target:

```text
components/Application/Validation/System/
  PublicSurface/
    Validator.php
    Validation.php

  Flows/
    ValidateData/
    ValidateObject/
    ValidateRequest/

  Capabilities/
    Rules/
    ErrorBag/
    Messages/
    NestedData/
    ConditionalRules/
    CustomRules/
    Normalization/
```

Restore:

```text
[ ] required
[ ] string
[ ] integer
[ ] numeric
[ ] email
[ ] min
[ ] max
[ ] between
[ ] in
[ ] array
[ ] each
[ ] nullable
[ ] sometimes
[ ] confirmed
[ ] url
[ ] uuid
[ ] date
[ ] nested validation
[ ] error bag
[ ] localized messages
[ ] custom rules
```

---

## 4.5 HTTP Context

Target:

```text
components/HTTP/Context/System/
  PublicSurface/
    HttpContext.php
    HttpContextInterface.php

  Capabilities/
    Globals/
      GlobalsProviderInterface.php
      PhpGlobalsProvider.php

    Server/
    Query/
    Post/
    Cookies/
    Files/

  Configuration/
    HttpContextProvider.php
```

Restore:

```text
[ ] HttpContext
[ ] HttpContextInterface
[ ] PhpGlobalsProvider
[ ] GlobalsProviderInterface
[ ] mockable access to server/get/post/cookie/files
```

This task is explicitly identified in the backup plan as `TASK-008: HTTP Context`.

---

## 4.6 HTTP Security

Target:

```text
components/HTTP/Security/System/
  PublicSurface/
    HttpSecurity.php

  Flows/
    VerifyCsrfToken/
    ApplySecurityHeaders/
    ValidateSignedUrl/
    ResolveTrustedProxy/
    ResolveTrustedHost/

  Capabilities/
    Csrf/
    Headers/
    SignedUrls/
    TrustedProxy/
    TrustedHost/
    RateLimitBridge/
```

Restore:

```text
[ ] CSRF
[ ] security headers
[ ] trusted proxy
[ ] trusted host
[ ] signed URLs
[ ] rate limit bridge
```

---

## 4.7 URI

Target:

```text
components/HTTP/URI/System/
  PublicSurface/
    Uri.php
    Url.php

  Capabilities/
    QueryString/
    UrlGenerator/
    SignedUrls/
    Normalization/
```

Restore:

```text
[ ] URI value object
[ ] URL generator
[ ] query builder
[ ] canonical URL
[ ] signed URL support
```

---

## 4.8 CLI Console

Target:

```text
components/CLI/Console/System/
  PublicSurface/
    Console.php
    Command.php

  Flows/
    RunConsoleCommand/
    ResolveConsoleCommand/
    ParseConsoleInput/
    RenderConsoleOutput/

  Capabilities/
    CommandRegistry/
    CommandDefinitions/
    Input/
    Output/
    Arguments/
    Options/
    UI/
      ProgressBar.php
      Table.php
      Question.php
      Confirm.php

    Generators/
      ControllerGenerator.php
      EntityGenerator.php
      RepositoryGenerator.php
      ServiceGenerator.php
```

Restore:

```text
[ ] CommandDefinitions
[ ] MakeControllerCommand
[ ] MakeEntityCommand
[ ] MakeRepositoryCommand
[ ] MakeServiceCommand
[ ] ControllerGenerator
[ ] EntityGenerator
[ ] RepositoryGenerator
[ ] ServiceGenerator
[ ] ProgressBar
[ ] Table
[ ] Questions
[ ] exit code contract
```

This is also explicitly listed in the backup plan as CLI Console and Code Generators.

---

# PHASE 5: Restore requested vendor-like monoliths

## 5.1 Application/Facade

```text
[ ] Restore BaseFacade or equivalent
[ ] Rename to Application/Facade/System/PublicSurface/Facade.php
[ ] Add FacadeResolver
[ ] Add FacadeRootRegistry
[ ] Add ClearResolvedFacades
[ ] Register reset hook for long-lived runtimes
[ ] Move actual facades into owning components
```

Actual facades:

```text
[ ] Route facade -> HTTP/Router
[ ] DB facade -> DataStack/Database
[ ] Cache facade -> Application/Cache
[ ] Auth facade -> Identity/Auth
[ ] View facade -> Presentation/View
[ ] Queue facade -> Operations/Queue
[ ] Mail facade -> Operations/Mail
```

---

## 5.2 Carbon clone

```text
[ ] Restore Carbon-like code into Application/DateTime/System/Capabilities/CarbonCompat
[ ] Keep ClockInterface as clean contract
[ ] Add SystemClock
[ ] Add FrozenClock
[ ] Add DateRange
[ ] Add Duration
[ ] Add diffForHumans
[ ] Add timezone helpers
[ ] Add parse/format helpers
[ ] Add Date facade/public API
[ ] Add reset-safe behavior if any static state exists
```

---

## 5.3 BladeOne copy

```text
[ ] Restore BladeOne-like code into Presentation/View/System/Capabilities/Engines/BladeOne
[ ] Keep TemplateEngineInterface
[ ] Add BladeOneTemplateEngine adapter
[ ] Add RenderView flow
[ ] Add View PublicSurface
[ ] Add template cache
[ ] Add layout support
[ ] Add sections
[ ] Add partials
[ ] Add custom directives
[ ] Add escaping policy
```

The backup plan specifically names Blade normalization and moving `BladeTemplateEngine.php` and `TemplateEngine.php`
into `Presentation/View` engine capabilities.

---

## 5.4 Ignition/Whoops clone

```text
[ ] Restore Whoops-like error screen into DeveloperTools/DumpDebugger
[ ] Restore Ignition-like error screen into DeveloperTools/DumpDebugger
[ ] Add ErrorScreen interface
[ ] Add RenderDebugError flow
[ ] Add source preview
[ ] Add stack frame renderer
[ ] Add solution provider
[ ] Add CLI error renderer
[ ] Add HTML error renderer
[ ] Add secret redaction
[ ] Disable in production unless explicitly enabled
```

---

# PHASE 6: Recover batteries-included features

## 6.1 Mail

```text
components/Operations/Mail/System/
  PublicSurface/
  Flows/
    SendMail/
    QueueMail/
  Capabilities/
    Messages/
    Envelopes/
    Transports/
    Attachments/
    Templates/
    Failures/
```

Restore:

```text
[ ] Mail facade/public API
[ ] Mailer
[ ] MailMessage
[ ] Envelope
[ ] SMTP transport
[ ] Sendmail transport
[ ] API transport boundary
[ ] attachments
[ ] template mail
[ ] queue integration
[ ] retry/failure handling
[ ] mail fake
```

---

## 6.2 Queue / Jobs

```text
components/Operations/Queue/System/
  PublicSurface/
  Flows/
    DispatchJob/
    ProcessJob/
    RetryFailedJob/
    RunQueueWorker/
  Capabilities/
    Jobs/
    Drivers/
    Workers/
    Retries/
    FailedJobs/
    Batches/
    Idempotency/
    Backpressure/
    Metrics/
```

Restore:

```text
[ ] Queue public API
[ ] Job
[ ] Batch
[ ] Task
[ ] sync driver
[ ] database driver if old code had it
[ ] redis driver if old code had it
[ ] delayed jobs
[ ] retries
[ ] failed jobs
[ ] job timeout
[ ] unique jobs
[ ] batches
[ ] worker heartbeat
[ ] graceful shutdown
[ ] backpressure
```

---

## 6.3 Notifications

```text
components/Operations/Notifications/System/
  PublicSurface/
  Flows/
    SendNotification/
    QueueNotification/
  Capabilities/
    Channels/
    Routing/
    DeliveryStatus/
    Failures/
```

Restore:

```text
[ ] Notification
[ ] channels
[ ] mail channel
[ ] database channel
[ ] routing
[ ] queue integration
[ ] delivery tracking
[ ] notification fake
```

---

## 6.4 Localization / I18N

```text
components/Application/Localization/System/
  PublicSurface/
    Lang.php
    Translator.php

  Flows/
    TranslateMessage/
    ResolveLocale/

  Capabilities/
    Loaders/
    Catalogues/
    Pluralization/
    FallbackLocale/
    ValidationMessages/
```

Restore:

```text
[ ] Translator
[ ] Lang
[ ] locale resolver
[ ] fallback locale
[ ] translation loader
[ ] pluralization
[ ] validation messages
```

---

## 6.5 Testing fakes

```text
components/DeveloperTools/Testing/System/
  PublicSurface/
    Testing.php

  Capabilities/
    Fakes/
      EventFake.php
      CacheFake.php
      MailFake.php
      QueueFake.php
      HttpFake.php
      TimeFake.php
      BusFake.php
```

Restore:

```text
[ ] EventFake
[ ] CacheFake
[ ] MailFake
[ ] QueueFake
[ ] HttpFake
[ ] TimeFake
[ ] BusFake
```

---

## 6.6 ApplicationWorkflow / Saga

```text
components/Operations/ApplicationWorkflow/System/
  PublicSurface/
    Workflow.php
    Saga.php

  Flows/
    StartSaga/
    RunSagaStep/
    CompensateSaga/
    ResumeSaga/
    FailSaga/

  Capabilities/
    SagaState/
    SagaStore/
    StepRunner/
    Compensation/
    Idempotency/
    Retries/
    Timeouts/
```

Restore:

```text
[ ] real SagaStore
[ ] SagaState
[ ] StepRunner
[ ] compensation execution
[ ] idempotency
[ ] retry policy
[ ] timeout policy
[ ] failure recording
[ ] events integration
```

---

# PHASE 7: Integrate recovered features with framework/System

Target:

```text
framework/System/
  Flows/
    BootApplication/
    HandleIncomingHttp/
    RunConsoleCommand/
    StartWorker/
    HandleWorkerRequest/
    ResetApplicationState/
    ShutdownRuntime/

  Capabilities/
    Runtime/
    RequestScope/
    StateReset/
    ComponentRegistry/
    RuntimeSafety/
    Diagnostics/
```

Integration tasks:

```text
[ ] BootApplication registers Application suite
[ ] BootApplication registers HTTP suite
[ ] BootApplication registers CLI suite
[ ] BootApplication registers DataStack suite
[ ] BootApplication registers Identity suite
[ ] BootApplication registers Operations suite
[ ] BootApplication registers Presentation suite
[ ] BootApplication registers DeveloperTools suite

[ ] Runtime state reset includes Facade reset
[ ] Runtime state reset includes Session reset
[ ] Runtime state reset includes Auth current user reset
[ ] Runtime state reset includes Container scoped services reset
[ ] Runtime state reset includes Logging context reset
[ ] Runtime state reset includes Persistence UnitOfWork reset
[ ] Runtime state reset includes Database transaction leak detection
```

Add developer commands:

```text
[ ] php avax architecture:check
[ ] php avax runtime:doctor
[ ] php avax worker:doctor
[ ] php avax config:validate
[ ] php avax container:graph
[ ] php avax routes:explain
[ ] php avax db:doctor
[ ] php avax cache:doctor
[ ] php avax security:audit
```

---

# PHASE 8: Tests after code placement

User rule: code and architecture first, tests after.

So:

```text
Do not write new tests before moving/recovering code.
Existing old tests may be read as evidence.
After code is placed, write tests.
```

Test structure:

```text
tests/
  Architecture/
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

  Feature/
    Framework/

  Compatibility/
```

Architecture tests:

```text
[ ] component suite structure
[ ] duplicate owners
[ ] namespace drift
[ ] public surface thinness
[ ] docs mirror
[ ] runtime leak boundaries
[ ] vendor monolith isolation
```

Feature recovery tests:

```text
[ ] Collection full API
[ ] Arrhae full API
[ ] DataTransfer object serialization
[ ] Container compile/diagnostics/scope
[ ] Config schema/compiled config
[ ] Middleware pipeline
[ ] Event dispatcher/listener registry
[ ] Database query/transaction/schema/migration
[ ] Persistence UnitOfWork/IdentityMap/Repository
[ ] Text Str/Inflector
[ ] DateTime CarbonCompat
[ ] View BladeOne rendering
[ ] CLI command/generator/UI
[ ] Mail send/queue
[ ] Queue dispatch/process/retry
[ ] Notifications route/send
[ ] Localization translate/fallback
[ ] DumpDebugger error screen/redaction
```

Compatibility tests:

```text
[ ] old DataFoundation API delegates to DataStack/Data
[ ] old DataLayer API delegates to DataStack/Persistence
[ ] old Facade API works
[ ] old Commands API delegates to CLI/Console
[ ] old Middlewares API delegates to HTTP/Middleware
```

---

# PHASE 9: Governance and quality checks

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

Architecture checkers:

```bash
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-docs-mirror.php
php tooling/refactor/check-runtime-leaks.php
php tooling/refactor/check-forbidden-folders.php
php tooling/refactor/check-vendor-monolith-isolation.php
php tooling/refactor/check-feature-recovery-coverage.php
```

Kluster:

```text
[ ] Run kluster_code_review_auto after file changes if configured.
[ ] If composer.json/composer.lock is changed, run kluster_dependency_check before install/update.
```

---

# PHASE 10: Documentation last

Only after code and tests pass.

Docs to write:

```text
docs/
  architecture/
    component-suite-architecture.md
    feature-recovery-policy.md
    vendor-monolith-policy.md
    runtime-safety-policy.md
    public-surface-policy.md

  decisions/
    ADR-global-facade-component.md
    ADR-carbon-compat-in-application-datetime.md
    ADR-bladeone-engine-in-presentation-view.md
    ADR-ignition-whoops-in-developertools.md
    ADR-datastack-boundaries.md
    ADR-batteries-included-recovery.md

  components/
    Application/
      Facade/System/how-this-works.md
      DateTime/System/how-this-works.md
      Config/System/how-this-works.md
      Container/System/how-this-works.md
      Text/System/how-this-works.md
      Localization/System/how-this-works.md

    HTTP/
      Middleware/System/how-this-works.md
      Context/System/how-this-works.md
      Security/System/how-this-works.md
      URI/System/how-this-works.md

    CLI/
      Console/System/how-this-works.md

    DataStack/
      Data/System/how-this-works.md
      Database/System/how-this-works.md
      Persistence/System/how-this-works.md

    Operations/
      Events/System/how-this-works.md
      Logging/System/how-this-works.md
      Mail/System/how-this-works.md
      Queue/System/how-this-works.md
      Notifications/System/how-this-works.md
      ApplicationWorkflow/System/how-this-works.md

    Presentation/
      View/System/how-this-works.md

    DeveloperTools/
      DumpDebugger/System/how-this-works.md
      Testing/System/how-this-works.md
```

---

# PHASE 11: Final cleanup

```text
[ ] Delete old real behavior paths after recovery
[ ] Keep only bridges that are still required
[ ] Add @deprecated to all bridges
[ ] Add removal phase to all bridges
[ ] Remove bridge after compatibility tests prove new paths work
[ ] Keep avax-backup.txt until final feature recovery matrix is 100%
[ ] When recovery is fully closed, move avax-backup.txt to archive or remove from tracked source if desired
```

Final reports:

```text
EVIDENCE/recovery/feature-recovery-final-report.md
EVIDENCE/recovery/vendor-monolith-placement-report.md
EVIDENCE/recovery/unrecovered-features-report.md
EVIDENCE/recovery/compatibility-bridges-report.md
EVIDENCE/recovery/tests-and-quality-gates-report.md
```

---

# Exact AI prompt

Ovo možeš direktno da daš Codex-u.

```text
You are restoring AvaX feature depth from avax-backup.txt.

Important:
Do not delete avax-backup.txt. It is the recovery source of truth until the feature recovery matrix is fully closed.

Goal:
Recover all useful old AvaX features into the new component-suite architecture.

This is not a rollback.
This is not a rewrite.
This is not a cleanup-only task.

Use old code as source material.
Move, rename, adapt, and integrate existing code into the correct new owner.
Do not write from scratch unless old code is missing, unsafe, or unusable.

Final architecture:
framework/System
components/Application
components/HTTP
components/CLI
components/DataStack
components/Identity
components/Operations
components/Presentation
components/DeveloperTools

Canonical shape:
components/<Suite>/<Component>/System/
  PublicSurface/
  Flows/
  Capabilities/
  Configuration/
  Foundation/

Special decisions:
1. Global Facade is allowed, but only as components/Application/Facade.
2. Actual facades must live in owning components under PublicSurface/Facades.
3. Carbon clone is allowed, but only as components/Application/DateTime/System/Capabilities/CarbonCompat.
4. BladeOne copy is allowed, but only as components/Presentation/View/System/Capabilities/Engines/BladeOne.
5. Ignition/Whoops copy is allowed, but only as components/DeveloperTools/DumpDebugger/System/Capabilities/ErrorScreens.
6. Vendor-like monoliths are allowed only behind AvaX contracts and inside isolated Capability/Vendor or engine-specific folders.
7. Vendor-like monoliths must not be imported directly outside their owning component.
8. Vendor-like monoliths must keep license/header information.
9. Vendor-like monoliths must not leak into framework/System.

Execution order:
1. Create recovery inventory from avax-backup.txt.
2. Create old-to-new ownership map.
3. Recover DataFoundation into DataStack/Data.
4. Recover Container advanced runtime features.
5. Recover Config feature depth.
6. Recover Middleware pipeline.
7. Recover Events.
8. Recover Database.
9. Recover Persistence.
10. Recover Filesystem, Logging, Text, Validation.
11. Recover HTTP Context, Security, URI.
12. Recover CLI Console and generators.
13. Recover Application/Facade.
14. Recover Application/DateTime CarbonCompat.
15. Recover Presentation/View BladeOne engine.
16. Recover DeveloperTools/DumpDebugger Ignition/Whoops-like debug screens.
17. Recover Mail, Queue, Notifications, Localization, Testing fakes.
18. Integrate recovered features with framework/System.
19. Only after code placement, write tests.
20. Run architecture tests.
21. Run component integration tests.
22. Run behavior tests.
23. Run compatibility bridge tests.
24. Run how-to-*.md governance compliance.
25. Run static analysis and quality gates.
26. Run kluster_code_review_auto after file changes if configured.
27. Write documentation last.
28. Create final recovery report.

Do not:
- restore old folder structure
- keep duplicate owners
- put vendor-like monoliths in PublicSurface
- put Carbon/Blade/Ignition/Whoops into framework/System
- leave old namespaces in canonical code
- keep DataFoundation or DataLayer as real owners
- claim done until tests prove recovered behavior

Acceptance criteria:
- Old feature inventory completed.
- Every useful old feature has a new owner.
- Every restored feature has code in the new architecture.
- Every restored feature has tests.
- Vendor-like monoliths are isolated and wrapped.
- Global facade machinery exists but actual facades live with owners.
- DataFoundation and DataLayer are bridge-only or removed.
- No duplicate owners.
- No namespace drift.
- PublicSurface remains thin.
- Runtime reset safety is preserved.
- All quality gates pass.
- Documentation describes the real final system.
```

---

# Najkraći operativni redosled

Ako agent hoće “šta prvo”, ovo je red:

```text
1. Inventory avax-backup.txt
2. Recovery matrix
3. DataFoundation -> DataStack/Data
4. Container
5. Config
6. Middleware
7. Events
8. Database
9. Persistence
10. Filesystem / Logging / Text / Validation
11. HTTP Context / Security / URI
12. CLI Console
13. Application/Facade
14. DateTime/CarbonCompat
15. View/BladeOne
16. DumpDebugger/Ignition/Whoops
17. Mail / Queue / Notifications / Localization / Testing fakes
18. Framework integration
19. Tests
20. Governance
21. Docs
22. Final report
```

Ovo je pravi potez: vraćaš **sve mišiće**, uključujući vendor-like delove koje želiš, ali ih zaključavaš u prave
komponente. Tako AvaX ne postaje opet haos, nego dobija staru snagu u novom sistemu.
