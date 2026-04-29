# Old-to-New Ownership Map

**Source**: Recovery feature inventory from `old-feature-inventory.md`
**Target Architecture**: `components/<Suite>/<Component>/System/`

This document maps every old feature (from `avax-backup.txt`) to its new component owner in the current component-suite
architecture.

---

## Ownership Mapping Table

| #  | Old Feature (Namespace)                                             | Old Key Classes                                       | New Owner Component           | New Target Namespace                                                       | Notes                                                                                                   |
|----|---------------------------------------------------------------------|-------------------------------------------------------|-------------------------------|----------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------|
| 1  | **DataFoundation\Collections**                                      | `Collection`, `MakeCollection`, `CollectionInterface` | `components/DataStack/Data`   | `Avax\Components\DataStack\Data\System\Capabilities\Collections`           | Collection is mature `final readonly` with MutationGuard. Map Collection, Arr helpers, array utilities. |
| 2  | **DataFoundation\Collections\Strings**                              | String collection operations                          | `components/DataStack/Data`   | `Avax\Components\DataStack\Data\System\Capabilities\Collections\Strings`   | String-specific collection operators.                                                                   |
| 3  | **DataFoundation\Collections\Aggregate**                            | Aggregate operations                                  | `components/DataStack/Data`   | `Avax\Components\DataStack\Data\System\Capabilities\Collections\Aggregate` | Reduce, sum, average, etc.                                                                              |
| 4  | **DataFoundation\Collections\MultiMap**                             | MultiMap                                              | `components/DataStack/Data`   | `Avax\Components\DataStack\Data\System\Capabilities\Collections\MultiMap`  | Multi-key map support.                                                                                  |
| 5  | **DataFoundation\Collections\Order/OrderedMap/OrderedSet/Sequence** | Ordering, ordered collections                         | `components/DataStack/Data`   | `Avax\Components\DataStack\Data\System\Capabilities\Collections\Sort`      | Sorting and ordering capabilities.                                                                      |
| 6  | **DataFoundation\Composites**                                       | `Pair`, `Tuple`, `Record`, `MapEntry`                 | `components/DataStack/Data`   | `Avax\Components\DataStack\Data\System\Capabilities\Composites`            | Functional value objects.                                                                               |
| 7  | **DataFoundation\DataTransfer**                                     | DTO system, field mapping/validation                  | `components/DataStack/Data`   | `Avax\Components\DataStack\Data\System\Capabilities\DataTransfer`          | DTO attributes, field mapping, serialization.                                                           |
| 8  | **DataFoundation\Flows**                                            | `Pipeline`, `Window`, `LazySequence`, `Batch`         | `components/DataStack/Data`   | `Avax\Components\DataStack\Data\System\Capabilities\Pipeline`              | Data processing pipeline.                                                                               |
| 9  | **DataFoundation\Structures\Queue/PriorityQueue**                   | `ArrayQueue`, `PriorityQueue`                         | `components/DataStack/Data`   | `Avax\Components\DataStack\Data\System\Capabilities\Structures`            | Data structures (distinct from job queue).                                                              |
| 10 | **DataFoundation\Internal**                                         | Comparison, Conversion, Iteration, Mutability         | `components/DataStack/Data`   | `Avax\Components\DataStack\Data\System\Capabilities\Internal`              | Internal collection utilities.                                                                          |
| 11 | **DataFoundation\Contracts**                                        | Collection contracts                                  | `components/DataStack/Data`   | `Avax\Components\DataStack\Data\System\Foundation\Contracts`               | Base interfaces.                                                                                        |
| 12 | **DataFoundation\Exceptions**                                       | Data exceptions                                       | `components/DataStack/Data`   | `Avax\Components\DataStack\Data\System\Foundation\Failure`                 | Exception types.                                                                                        |
| 13 | **DataFoundation\Values\Text**                                      | Text values                                           | `components/Application/Text` | `Avax\Components\Application\Text\System\Capabilities\Values`              | Text value objects.                                                                                     |
| 14 | **DataFoundation\Validation**                                       | Attribute rules, field validation                     | `components/DataStack/Data`   | `Avax\Components\DataStack\Data\System\Capabilities\Validation`            | DTO validation rules.                                                                                   |

---

### DataLayer → Persistence

| #  | Old Feature (Namespace)                 | Old Key Classes          | New Owner Component                | New Target Namespace                                                     | Notes                               |
|----|-----------------------------------------|--------------------------|------------------------------------|--------------------------------------------------------------------------|-------------------------------------|
| 15 | **DataLayer** (root)                    | Data layer coordination  | `components/DataStack/Persistence` | `Avax\Components\DataStack\Persistence\System`                           | Top-level data layer.               |
| 16 | **DataLayer\AccessPersistentData**      | Data access              | `components/DataStack/Persistence` | `Avax\Components\DataStack\Persistence\System\Capabilities\Access`       | Repository pattern access.          |
| 17 | **DataLayer\CommitDataChanges**         | Change commit            | `components/DataStack/Persistence` | `Avax\Components\DataStack\Persistence\System\Capabilities\Commit`       | UnitOfWork flush.                   |
| 18 | **DataLayer\ConfigureDataLayer**        | Data layer config        | `components/DataStack/Persistence` | `Avax\Components\DataStack\Persistence\System\Configuration`             | Persistence configuration.          |
| 19 | **DataLayer\CoordinateDataConsistency** | Consistency coordination | `components/DataStack/Persistence` | `Avax\Components\DataStack\Persistence\System\Capabilities\Consistency`  | Transaction isolation, consistency. |
| 20 | **DataLayer\DescribeStorageBehavior**   | Storage description      | `components/DataStack/Persistence` | `Avax\Components\DataStack\Persistence\System\Capabilities\Schema`       | Storage metadata.                   |
| 21 | **DataLayer\DistributeStoredData**      | Data distribution        | `components/DataStack/Persistence` | `Avax\Components\DataStack\Persistence\System\Capabilities\Distribution` | Sharding, partitioning.             |
| 22 | **DataLayer\EvolveStoredSchema**        | Schema evolution         | `components/DataStack/Database`    | `Avax\Components\DataStack\Database\System\Capabilities\Migrations`      | Schema migrations.                  |
| 23 | **DataLayer\InspectDataLayer**          | Data layer inspection    | `components/DataStack/Persistence` | `Avax\Components\DataStack\Persistence\System\Capabilities\Diagnostics`  | Introspection, diagnostics.         |
| 24 | **DataLayer\OperateDataLayer**          | Data layer operations    | `components/DataStack/Persistence` | `Avax\Components\DataStack\Persistence\System\Capabilities\Operations`   | CRUD operations.                    |
| 25 | **DataLayer\PropagateDataChanges**      | Change propagation       | `components/DataStack/Persistence` | `Avax\Components\DataStack\Persistence\System\Capabilities\Events`       | Change events, CDC.                 |
| 26 | **DataLayer\ProtectStoredData**         | Data protection          | `components/DataStack/Persistence` | `Avax\Components\DataStack\Persistence\System\Capabilities\Security`     | Row-level security, encryption.     |
| 27 | **DataLayer\QueryStoredData**           | Data querying            | `components/DataStack/Database`    | `Avax\Components\DataStack\Database\System\Capabilities\Query`           | Query builder, specifications.      |
| 28 | **DataLayer\ShapeStoredData**           | Schema shaping           | `components/DataStack/Database`    | `Avax\Components\DataStack\Database\System\Capabilities\Schema`          | Schema design, DSL.                 |

---

### Database → Database

| #  | Old Feature (Namespace)                 | Old Key Classes                                     | New Owner Component                | New Target Namespace                                                   | Notes                                   |
|----|-----------------------------------------|-----------------------------------------------------|------------------------------------|------------------------------------------------------------------------|-----------------------------------------|
| 29 | **Database** (root)                     | Database coordination                               | `components/DataStack/Database`    | `Avax\Components\DataStack\Database\System`                            | Top-level database.                     |
| 30 | **Database\Connections**                | Connection management, pools                        | `components/DataStack/Database`    | `Avax\Components\DataStack\Database\System\Capabilities\Connections`   | Connection pools, read/write splitting. |
| 31 | **Database\Migrations**                 | Migration system, DSL                               | `components/DataStack/Database`    | `Avax\Components\DataStack\Database\System\Capabilities\Migrations`    | Column/table DSL, type mapping.         |
| 32 | **Database\Query**                      | QueryBuilder, Grammar, CTE, Upsert, WindowFunctions | `components/DataStack/Database`    | `Avax\Components\DataStack\Database\System\Capabilities\Query`         | Full query builder with SQL grammars.   |
| 33 | **Database\Integrations\AvaxContainer** | Container integration                               | `components/DataStack/Database`    | `Avax\Components\DataStack\Database\System\Integrations\Container`     | DI integration.                         |
| 34 | **Database\Integrations\Console**       | Console integration                                 | `components/DataStack/Database`    | `Avax\Components\DataStack\Database\System\Integrations\Console`       | CLI commands.                           |
| 35 | **Database\Telemetry\Events**           | Database events, subscribers                        | `components/DataStack/Database`    | `Avax\Components\DataStack\Database\System\Capabilities\Telemetry`     | Query telemetry, slow queries.          |
| 36 | **Database\ORM\UnitOfWork**             | EntityManager, UnitOfWork                           | `components/DataStack/Persistence` | `Avax\Components\DataStack\Persistence\System\Capabilities\UnitOfWork` | Entity tracking, identity map.          |

---

### Container → Container

| #  | Old Feature (Namespace)             | Old Key Classes                                                          | New Owner Component                | New Target Namespace                                                    | Notes                            |
|----|-------------------------------------|--------------------------------------------------------------------------|------------------------------------|-------------------------------------------------------------------------|----------------------------------|
| 37 | **Container\Core**                  | `Container`, `ContainerBuilder`, `CompiledContainer`                     | `components/Application/Container` | `Avax\Components\Application\Container\System\Capabilities\Core`        | DI container core.               |
| 38 | **Container\DI\Composition**        | Assembly, compilation, testing                                           | `components/Application/Container` | `Avax\Components\Application\Container\System\Capabilities\Composition` | Container composition.           |
| 39 | **Container\DI\Declaration**        | Bindings, blueprints, ownership                                          | `components/Application/Container` | `Avax\Components\Application\Container\System\Capabilities\Declaration` | Service declaration.             |
| 40 | **Container\DI\Resolution**         | Kernel, resolution                                                       | `components/Application/Container` | `Avax\Components\Application\Container\System\Capabilities\Resolution`  | Service resolution.              |
| 41 | **Components\Container**            | Bindings, providers, resolution flows                                    | `components/Application/Container` | `Avax\Components\Application\Container\System`                          | Already in Components structure. |
| 42 | **Auth\Integrations\AvaxContainer** | Auth container integration                                               | `components/Application/Container` | `Avax\Components\Application\Container\System\Integrations\Auth`        | Auth-specific bindings.          |
| 43 | **ServiceProviders**                | `AuthServiceProvider`, `CacheServiceProvider`, `DatabaseServiceProvider` | `components/Application/Container` | `Avax\Components\Application\Container\System\Capabilities\Providers`   | Service provider pattern.        |

---

### Config → Config

| #  | Old Feature (Namespace)            | Old Key Classes                               | New Owner Component             | New Target Namespace                                                | Notes                            |
|----|------------------------------------|-----------------------------------------------|---------------------------------|---------------------------------------------------------------------|----------------------------------|
| 44 | **Config** (root)                  | `Config`, `ConfigLoader`, `ConfigFileLoader`  | `components/Application/Config` | `Avax\Components\Application\Config\System`                         | Config root.                     |
| 45 | **Config\Configurator\FileLoader** | File-based config loading                     | `components/Application/Config` | `Avax\Components\Application\Config\System\Capabilities\Loader`     | PHP array file loader.           |
| 46 | **Components\Config**              | Architecture, loader, repository capabilities | `components/Application/Config` | `Avax\Components\Application\Config\System`                         | Already in Components structure. |
| 47 | **ConfigurationRepository**        | Config repository                             | `components/Application/Config` | `Avax\Components\Application\Config\System\Capabilities\Repository` | Config storage/retrieval.        |

---

### Facade → Facade

| #  | Old Feature (Namespace)              | Old Key Classes                       | New Owner Component             | New Target Namespace                                          | Notes                  |
|----|--------------------------------------|---------------------------------------|---------------------------------|---------------------------------------------------------------|------------------------|
| 48 | **Facade** (root)                    | `BaseFacade`                          | `components/Application/Facade` | `Avax\Components\Application\Facade\System`                   | Facade base class.     |
| 49 | **Facade\Facades**                   | `CacheFacade`, `RouteFacadeContainer` | `components/Application/Facade` | `Avax\Components\Application\Facade\System\Facades`           | Specific facades.      |
| 50 | **Auth\Capabilities\Access\Facades** | Auth access facades                   | `components/Application/Facade` | `Avax\Components\Application\Facade\System\Integrations\Auth` | Auth facade accessors. |

---

### Commands → Console

| #  | Old Feature (Namespace)               | Old Key Classes                                                                                                                   | New Owner Component      | New Target Namespace                                               | Notes                        |
|----|---------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------|--------------------------|--------------------------------------------------------------------|------------------------------|
| 51 | **Commands** (root)                   | `ConsoleKernel`, `CommandDefinitions`                                                                                             | `components/CLI/Console` | `Avax\Components\CLI\Console\System`                               | Console root.                |
| 52 | **Commands\App**                      | App commands                                                                                                                      | `components/CLI/Console` | `Avax\Components\CLI\Console\System\Capabilities\Commands`         | Application commands.        |
| 53 | **Framework\Flows\RunConsoleCommand** | `RunConsoleCommand`                                                                                                               | `components/CLI/Console` | `Avax\Components\CLI\Console\System\Flows\ExecuteCommand`          | Command execution flow.      |
| 54 | **Framework\PublicSurface\Console**   | Console public API                                                                                                                | `components/CLI/Console` | `Avax\Components\CLI\Console\System\PublicSurface`                 | Console entry points.        |
| 55 | **Database\Integrations\Console**     | Migrate, seed commands                                                                                                            | `components/CLI/Console` | `Avax\Components\CLI\Console\System\Integrations\Database`         | Database CLI commands.       |
| 56 | **Generators**                        | `MigrationGenerator`, `*GeneratorInterface`                                                                                       | `components/CLI/Console` | `Avax\Components\CLI\Console\System\Capabilities\Generators`       | Code generators.             |
| 57 | **MakeCommands**                      | `MakeControllerCommand`, `MakeEntityCommand`, `MakeMigrationCommand`, `MakeRepositoryCommand`, `MakeServiceCommand`               | `components/CLI/Console` | `Avax\Components\CLI\Console\System\Capabilities\MakeCommands`     | Scaffolding commands.        |
| 58 | **Components\Commands\UI**            | Command UI capabilities                                                                                                           | `components/CLI/Console` | `Avax\Components\CLI\Console\System\Capabilities\UI`               | Console output/formatting.   |
| 59 | **MigrateCommands**                   | `MigrateCommand`, `MigrateFreshCommand`, `MigrateRefreshCommand`, `MigrateRollbackCommand`, `MigrateStatusCommand`, `SeedCommand` | `components/CLI/Console` | `Avax\Components\CLI\Console\System\Capabilities\DatabaseCommands` | Database migration commands. |

---

### Middlewares → Middleware

| #  | Old Feature (Namespace)     | Old Key Classes                                                                                                                                                              | New Owner Component          | New Target Namespace                                          | Notes                            |
|----|-----------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|------------------------------|---------------------------------------------------------------|----------------------------------|
| 60 | **Middlewares** (root)      | `Middleware`, `MiddlewarePipeline`                                                                                                                                           | `components/HTTP/Middleware` | `Avax\Components\HTTP\Middleware\System`                      | Middleware root.                 |
| 61 | **HTTP\Middleware**         | HTTP middleware                                                                                                                                                              | `components/HTTP/Middleware` | `Avax\Components\HTTP\Middleware\System`                      | HTTP-specific middleware.        |
| 62 | **HTTP\Middleware\CSRF**    | CSRF middleware                                                                                                                                                              | `components/HTTP/Middleware` | `Avax\Components\HTTP\Middleware\System\Capabilities\Csrf`    | CSRF verification.               |
| 63 | **Components\Middleware**   | Pipeline, failure, public surface                                                                                                                                            | `components/HTTP/Middleware` | `Avax\Components\HTTP\Middleware\System`                      | Already in Components structure. |
| 64 | **Built-in Middleware**     | `CorsMiddleware`, `RateLimiterMiddleware`, `TracingMiddleware`, `RequestLoggerMiddleware`, `SessionLifecycleMiddleware`, `JsonResponseMiddleware`, `IpRestrictionMiddleware` | `components/HTTP/Middleware` | `Avax\Components\HTTP\Middleware\System\Capabilities\BuiltIn` | Standard middleware set.         |
| 65 | **Psr15MiddlewarePipeline** | PSR-15 compatibility                                                                                                                                                         | `components/HTTP/Middleware` | `Avax\Components\HTTP\Middleware\System\Capabilities\Psr15`   | PSR-15 adapter.                  |

---

### HTTP Context → Context

| #  | Old Feature (Namespace)   | Old Key Classes                                  | New Owner Component       | New Target Namespace                                           | Notes                 |
|----|---------------------------|--------------------------------------------------|---------------------------|----------------------------------------------------------------|-----------------------|
| 66 | **HTTP\Context**          | `ContextContainer`, `HttpContextTest`            | `components/HTTP/Context` | `Avax\Components\HTTP\Context\System`                          | HTTP context.         |
| 67 | **AuthenticationContext** | `AuthenticationContext`, `AuthenticationRequest` | `components/HTTP/Context` | `Avax\Components\HTTP\Context\System\Capabilities\Auth`        | Auth context in HTTP. |
| 68 | **DiagnosticContext**     | `DiagnosticContext`                              | `components/HTTP/Context` | `Avax\Components\HTTP\Context\System\Capabilities\Diagnostics` | Diagnostic context.   |
| 69 | **RuntimeContext**        | `RuntimeContextTest`                             | `components/HTTP/Context` | `Avax\Components\HTTP\Context\System\Capabilities\Runtime`     | Runtime context.      |

---

### HTTP Security → HTTP Security

| #  | Old Feature (Namespace)                   | Old Key Classes                                              | New Owner Component        | New Target Namespace                                        | Notes                       |
|----|-------------------------------------------|--------------------------------------------------------------|----------------------------|-------------------------------------------------------------|-----------------------------|
| 70 | **HTTP\Middleware\CSRF**                  | `CsrfMiddleware`, `CsrfTokens`, `CsrfVerificationMiddleware` | `components/HTTP/Security` | `Avax\Components\HTTP\Security\System\Capabilities\Csrf`    | CSRF protection.            |
| 71 | **Auth\Tenancy\Security**                 | `TenantSecurity`, `ApplyChange`, `ApproveChange`             | `components/HTTP/Security` | `Avax\Components\HTTP\Security\System\Capabilities\Tenancy` | Tenant security management. |
| 72 | **Auth\Integrations\Http\TenantSecurity** | Tenant security HTTP integration                             | `components/HTTP/Security` | `Avax\Components\HTTP\Security\System\Integrations\Tenancy` | Tenant security HTTP.       |

---

### HTTP URI → URI

| #  | Old Feature (Namespace) | Old Key Classes             | New Owner Component      | New Target Namespace                                      | Notes                            |
|----|-------------------------|-----------------------------|--------------------------|-----------------------------------------------------------|----------------------------------|
| 73 | **BaseUri**             | `BaseUri`, `ParseUriString` | `components/HTTP/URI`    | `Avax\Components\HTTP\URI\System`                         | URI handling.                    |
| 74 | **Router\Paths**        | Path capabilities           | `components/HTTP/Router` | `Avax\Components\HTTP\Router\System\Capabilities\Paths`   | Route paths (belongs to Router). |
| 75 | **RoutePathValidator**  | Path validation             | `components/HTTP/URI`    | `Avax\Components\HTTP\URI\System\Capabilities\Validation` | URI validation.                  |

---

### Text / String → Text

| #  | Old Feature (Namespace)  | Old Key Classes                                                                                                   | New Owner Component           | New Target Namespace                                             | Notes                         |
|----|--------------------------|-------------------------------------------------------------------------------------------------------------------|-------------------------------|------------------------------------------------------------------|-------------------------------|
| 76 | **Text** (root)          | Text transforms, validation                                                                                       | `components/Application/Text` | `Avax\Components\Application\Text\System`                        | Text root.                    |
| 77 | **Text\Transform**       | `TransformToAscii`, `TransformToCamel`, `TransformToPlural`, `TransformToSingular`, `TransformToSlug`             | `components/Application/Text` | `Avax\Components\Application\Text\System\Capabilities\Transform` | String transformations.       |
| 78 | **Text\Validate**        | `ValidateSlug`                                                                                                    | `components/Application/Text` | `Avax\Components\Application\Text\System\Capabilities\Validate`  | Text validation.              |
| 79 | **Text\Extract**         | Text extraction                                                                                                   | `components/Application/Text` | `Avax\Components\Application\Text\System\Capabilities\Extract`   | Text extraction capabilities. |
| 80 | **Text\FuzzyMatch**      | `MatchTextByLevenshtein`, `MatchTextFuzzily`, `MatchTextPhonetically`, `MatchTextPartially`, `MatchTextByPattern` | `components/Application/Text` | `Avax\Components\Application\Text\System\Capabilities\Match`     | Fuzzy text matching.          |
| 81 | **Auth\Foundation\Text** | Auth text utilities                                                                                               | `components/Application/Text` | `Avax\Components\Application\Text\System\Integrations\Auth`      | Auth-specific text.           |

---

### DateTime → DateTime

| #  | Old Feature (Namespace)               | Old Key Classes                      | New Owner Component               | New Target Namespace                                                | Notes                  |
|----|---------------------------------------|--------------------------------------|-----------------------------------|---------------------------------------------------------------------|------------------------|
| 82 | **DateTime\Duration**                 | Duration handling                    | `components/Application/DateTime` | `Avax\Components\Application\DateTime\System\Capabilities\Duration` | Duration calculations. |
| 83 | **DateTime\Timezone**                 | Timezone handling                    | `components/Application/DateTime` | `Avax\Components\Application\DateTime\System\Capabilities\Timezone` | Timezone management.   |
| 84 | **DateTime\Flows\Diff**               | `DiffDates`, date diffing            | `components/Application/DateTime` | `Avax\Components\Application\DateTime\System\Flows\Diff`            | Date difference.       |
| 85 | **DateTime\Flows\Format**             | `FormatDate`, date formatting        | `components/Application/DateTime` | `Avax\Components\Application\DateTime\System\Flows\Format`          | Date formatting.       |
| 86 | **DateTime\Flows\Modify**             | `ModifyDate`, date modification      | `components/Application/DateTime` | `Avax\Components\Application\DateTime\System\Flows\Modify`          | Date modification.     |
| 87 | **DateTime\Flows\Parse**              | `ParseDate`, `InvalidDateTimeString` | `components/Application/DateTime` | `Avax\Components\Application\DateTime\System\Flows\Parse`           | Date parsing.          |
| 88 | **DateTime\Foundation\Failure**       | `DateTimeFailure`                    | `components/Application/DateTime` | `Avax\Components\Application\DateTime\System\Foundation\Failure`    | DateTime exceptions.   |
| 89 | **DateTime\PublicSurface**            | Public DateTime API                  | `components/Application/DateTime` | `Avax\Components\Application\DateTime\System\PublicSurface`         | DateTime entry points. |
| 90 | **Components\DateTime\Configuration** | DateTime configuration               | `components/Application/DateTime` | `Avax\Components\Application\DateTime\System\Configuration`         | DateTime config.       |

---

### Blade / View → View

| #  | Old Feature (Namespace)                      | Old Key Classes                                                                                                | New Owner Component            | New Target Namespace                                                     | Notes                             |
|----|----------------------------------------------|----------------------------------------------------------------------------------------------------------------|--------------------------------|--------------------------------------------------------------------------|-----------------------------------|
| 91 | **View** (root)                              | `View`, `ViewInterface`, `ViewFailure`                                                                         | `components/Presentation/View` | `Avax\Components\Presentation\View\System`                               | View root.                        |
| 92 | **BladeTemplateEngine**                      | Extends `Jenssegers\Blade\Blade`                                                                               | `components/Presentation/View` | `Avax\Components\Presentation\View\System\Capabilities\Engines\Blade`    | Blade engine wrapper.             |
| 93 | **TemplateEngine**                           | Extends `BladeOne`                                                                                             | `components/Presentation/View` | `Avax\Components\Presentation\View\System\Capabilities\Engines\BladeOne` | BladeOne engine.                  |
| 94 | **View\Engines**                             | Engine capabilities                                                                                            | `components/Presentation/View` | `Avax\Components\Presentation\View\System\Capabilities\Engines`          | Template engine abstraction.      |
| 95 | **View\RenderView**                          | `RenderView` flow                                                                                              | `components/Presentation/View` | `Avax\Components\Presentation\View\System\Flows\RenderView`              | View rendering flow.              |
| 96 | **View\Foundation\Failure**                  | `ViewFailure`                                                                                                  | `components/Presentation/View` | `Avax\Components\Presentation\View\System\Foundation\Failure`            | View exceptions.                  |
| 97 | **CompositionViews**                         | `CapabilitySliceView`, `RootCompositionView`, `ConfigurationSliceView`, `FlowSliceView`, `FoundationSliceView` | `components/Presentation/View` | `Avax\Components\Presentation\View\System\Capabilities\Composition`      | Architecture visualization views. |
| 98 | **MaterializedView**                         | `MaterializedViewPolicy`, `UseMaterializedView`                                                                | `components/Presentation/View` | `Avax\Components\Presentation\View\System\Capabilities\Materialized`     | Materialized view pattern.        |
| 99 | **Container\DI\Declaration\Ownership\Views** | Container ownership views                                                                                      | `components/Presentation/View` | `Avax\Components\Presentation\View\System\Integrations\Container`        | Container-diagnostic views.       |

---

### Mail → Mail

| #   | Old Feature (Namespace)  | Old Key Classes                                                             | New Owner Component          | New Target Namespace                                                | Notes                    |
|-----|--------------------------|-----------------------------------------------------------------------------|------------------------------|---------------------------------------------------------------------|--------------------------|
| 100 | **Mail\Address**         | `Email`, `NormalizeEmail`, `IsValidEmail`, `EmailRule`                      | `components/Operations/Mail` | `Avax\Components\Operations\Mail\System\Capabilities\Address`       | Email address handling.  |
| 101 | **Mail\Content**         | `RawMailBuilder`, email content                                             | `components/Operations/Mail` | `Avax\Components\Operations\Mail\System\Capabilities\Content`       | Email body/content.      |
| 102 | **Mail\Transport**       | `Mailer`, `SendmailTransport`                                               | `components/Operations/Mail` | `Avax\Components\Operations\Mail\System\Capabilities\Transport`     | Mail transport layer.    |
| 103 | **Mail\Send**            | `SendMail` flow                                                             | `components/Operations/Mail` | `Avax\Components\Operations\Mail\System\Flows\SendMail`             | Mail sending flow.       |
| 104 | **Mail\PublicSurface**   | Mail public API                                                             | `components/Operations/Mail` | `Avax\Components\Operations\Mail\System\PublicSurface`              | Mail entry points.       |
| 105 | **Mail\Configuration**   | `RegisterMailServices`                                                      | `components/Operations/Mail` | `Avax\Components\Operations\Mail\System\Configuration`              | Mail configuration.      |
| 106 | **EmailVerification**    | `BeginEmailVerification`, `EmailVerificationChallenge`, verification stores | `components/Operations/Mail` | `Avax\Components\Operations\Mail\System\Capabilities\Verification`  | Email verification flow. |
| 107 | **EmailChange**          | `BeginEmailChange`, `ConfirmEmailChange`, `EmailChangeChallenge`            | `components/Operations/Mail` | `Avax\Components\Operations\Mail\System\Capabilities\Change`        | Email change flow.       |
| 108 | **SecurityNotification** | `SecurityNotification`, `SecurityNotificationExporter`                      | `components/Operations/Mail` | `Avax\Components\Operations\Mail\System\Capabilities\Notifications` | Security notifications.  |

---

### Queue / Job → Queue

| #   | Old Feature (Namespace) | Old Key Classes                                                                                       | New Owner Component             | New Target Namespace                                                 | Notes                                        |
|-----|-------------------------|-------------------------------------------------------------------------------------------------------|---------------------------------|----------------------------------------------------------------------|----------------------------------------------|
| 109 | **Queue\Job**           | `JobDefinition`, `JobHandler`, `JobId`, `JobLifetime`, `JobRegistry`, `JobResult`, `JobScopedService` | `components/Operations/Queue`   | `Avax\Components\Operations\Queue\System\Capabilities\Jobs`          | Job definitions and lifecycle.               |
| 110 | **Queue\Queue**         | `Queue`, `ArrayQueue`, `PriorityQueue`, `SyncQueue`                                                   | `components/Operations/Queue`   | `Avax\Components\Operations\Queue\System\Capabilities\Queues`        | Queue implementations.                       |
| 111 | **Queue\Dispatch**      | `DispatchJob` flow                                                                                    | `components/Operations/Queue`   | `Avax\Components\Operations\Queue\System\Flows\DispatchJob`          | Job dispatching.                             |
| 112 | **Queue\Process**       | `ProcessJob` flow                                                                                     | `components/Operations/Queue`   | `Avax\Components\Operations\Queue\System\Flows\ProcessJob`           | Job processing.                              |
| 113 | **Queue\PublicSurface** | Queue public API                                                                                      | `components/Operations/Queue`   | `Avax\Components\Operations\Queue\System\PublicSurface`              | Queue entry points.                          |
| 114 | **Queue\Configuration** | `RegisterQueueServices`                                                                               | `components/Operations/Queue`   | `Avax\Components\Operations\Queue\System\Configuration`              | Queue configuration.                         |
| 115 | **Worker**              | `StartWorker`, `WorkerLifecycle`, `WorkerFailure`, `DeferredWorkerService`                            | `components/Operations/Queue`   | `Avax\Components\Operations\Queue\System\Capabilities\Workers`       | Worker management.                           |
| 116 | **WorkerLoops**         | `FrankenPhpWorkerLoop`, `RoadRunnerWorkerLoop`                                                        | `components/Operations/Queue`   | `Avax\Components\Operations\Queue\System\Capabilities\WorkerLoops`   | Runtime-specific worker loops.               |
| 117 | **WorkerRequestScope**  | `OpenWorkerRequestScope`, `CloseWorkerRequestScope`, `HandleWorkerRequest`                            | `components/Operations/Queue`   | `Avax\Components\Operations\Queue\System\Capabilities\RequestScope`  | Worker request lifecycle.                    |
| 118 | **QueueAudit**          | `QueueAuditExporter`                                                                                  | `components/Operations/Queue`   | `Avax\Components\Operations\Queue\System\Capabilities\Audit`         | Queue audit/export.                          |
| 119 | **Batch**               | `Batch`, `BatchInsert`, `BatchUpdate`, `BatchLoader`                                                  | `components/DataStack/Database` | `Avax\Components\DataStack\Database\System\Capabilities\Batch`       | Database batch operations (NOT queue batch). |
| 120 | **CIJob**               | `CIJob`                                                                                               | `components/Operations/Queue`   | `Avax\Components\Operations\Queue\System\Capabilities\CiJobs`        | CI/CD job integration.                       |
| 121 | **AuthMaintenanceJobs** | `RunAuthMaintenanceJobs`                                                                              | `components/Operations/Queue`   | `Avax\Components\Operations\Queue\System\Capabilities\ScheduledJobs` | Scheduled maintenance jobs.                  |

---

### Notification → Notifications

| #   | Old Feature (Namespace)  | Old Key Classes                                                                             | New Owner Component                   | New Target Namespace                                                    | Notes                        |
|-----|--------------------------|---------------------------------------------------------------------------------------------|---------------------------------------|-------------------------------------------------------------------------|------------------------------|
| 122 | **SecurityNotification** | `SecurityNotification`, `SecurityNotificationExporter`, `SendSecurityNotificationInterface` | `components/Operations/Notifications` | `Avax\Components\Operations\Notifications\System\Capabilities\Security` | Security notifications.      |
| 123 | **Notification system**  | (to be built — referenced in recovery docs)                                                 | `components/Operations/Notifications` | `Avax\Components\Operations\Notifications\System`                       | General notification system. |

---

### Translator / Lang → Localization

| #   | Old Feature (Namespace)     | Old Key Classes                                                     | New Owner Component                   | New Target Namespace                                                   | Notes                                                 |
|-----|-----------------------------|---------------------------------------------------------------------|---------------------------------------|------------------------------------------------------------------------|-------------------------------------------------------|
| 124 | **Translator** (planned)    | `Translator.php`, `Lang.php` — NOT in backup, only in recovery docs | `components/Application/Localization` | `Avax\Components\Application\Localization\System`                      | **NOT IMPLEMENTED** — needs to be built from scratch. |
| 125 | **SpecificationTranslator** | Translates query specs to SQL                                       | `components/DataStack/Database`       | `Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar` | This is a SQL Grammar, NOT i18n.                      |

---

### Ignition / Whoops → DumpDebugger

| #   | Old Feature (Namespace)   | Old Key Classes                                                                            | New Owner Component                      | New Target Namespace                                                             | Notes                      |
|-----|---------------------------|--------------------------------------------------------------------------------------------|------------------------------------------|----------------------------------------------------------------------------------|----------------------------|
| 126 | **DumpDebugger**          | `Dump`, `DumpDebugger`                                                                     | `components/DeveloperTools/DumpDebugger` | `Avax\Components\DeveloperTools\DumpDebugger\System`                             | Debug dump utility.        |
| 127 | **ErrorHandler**          | `ErrorHandler`, `HandleException`, `HandleGlobalError`, `FrameworkExceptionHandlingFailed` | `components/DeveloperTools/DumpDebugger` | `Avax\Components\DeveloperTools\DumpDebugger\System\Capabilities\Errors`         | Exception handling.        |
| 128 | **ErrorResponseFactory**  | `ErrorResponseFactory`                                                                     | `components/DeveloperTools/DumpDebugger` | `Avax\Components\DeveloperTools\DumpDebugger\System\Capabilities\Responses`      | Error response formatting. |
| 129 | **AuthException**         | `AuthException`                                                                            | `components/DeveloperTools/DumpDebugger` | `Avax\Components\DeveloperTools\DumpDebugger\System\Capabilities\AuthExceptions` | Auth-specific errors.      |
| 130 | **Framework\Diagnostics** | `Diagnostics` capabilities                                                                 | `components/DeveloperTools/DumpDebugger` | `Avax\Components\DeveloperTools\DumpDebugger\System\Capabilities\Diagnostics`    | Diagnostic capabilities.   |

---

### Additional Mappings

| #   | Old Feature (Namespace)      | Old Key Classes                                                 | New Owner Component                | New Target Namespace                                             | Notes                                                |
|-----|------------------------------|-----------------------------------------------------------------|------------------------------------|------------------------------------------------------------------|------------------------------------------------------|
| 131 | **ApplicationWorkflow\Saga** | Full saga orchestration system                                  | `components/Application/Workflow`  | `Avax\Components\Application\Workflow\System\Capabilities\Saga`  | Saga pattern implementation.                         |
| 132 | **Auth** (full)              | OAuth2, OIDC, SSO, SCIM, Passkeys, MFA, RBAC, risk-based access | `components/Auth`                  | `Avax\Components\Auth\System`                                    | Already exists as Auth component. Massive subsystem. |
| 133 | **Cache** (full)             | Cache, ConsistentHashRing, TieredCache, CompiledCache           | `components/DataStack/Cache`       | `Avax\Components\DataStack\Cache\System`                         | Or existing Cache component.                         |
| 134 | **Filesystem** (full)        | Filesystem, Disk, File, Directory operations                    | `components/Storage/Filesystem`    | `Avax\Components\Storage\Filesystem\System`                      | File/directory abstraction.                          |
| 135 | **Logging** (full)           | Logger, LoggerFactory, Writers, Transports                      | `components/Observability/Logging` | `Avax\Components\Observability\Logging\System`                   | Logging system.                                      |
| 136 | **Validation** (full)        | Validator, rules, attribute validation                          | `components/DataStack/Data`        | `Avax\Components\DataStack\Data\System\Capabilities\Validation`  | Validation rules and execution.                      |
| 137 | **Events** (full)            | EventDispatcher, ListenerRegistry                               | `components/Application/Events`    | `Avax\Components\Application\Events\System`                      | Event system.                                        |
| 138 | **Session** (full)           | Session, SessionStore, security, encryption, policies           | `components/HTTP/Session`          | `Avax\Components\HTTP\Session\System`                            | HTTP session management.                             |
| 139 | **Router** (full)            | Route registration, resolution, caching, constraints            | `components/HTTP/Router`           | `Avax\Components\HTTP\Router\System`                             | HTTP routing.                                        |
| 140 | **Request**                  | Request building, assembly, uploaded files                      | `components/HTTP/Request`          | `Avax\Components\HTTP\Request\System`                            | HTTP request handling.                               |
| 141 | **Response**                 | Response building, emitting, content types                      | `components/HTTP/Response`         | `Avax\Components\HTTP\Response\System`                           | HTTP response handling.                              |
| 142 | **Framework**                | Kernel, runtime, workers, application lifecycle                 | `components/Application/Kernel`    | `Avax\Components\Application\Kernel\System`                      | Application framework.                               |
| 143 | **HTTP\Kernel**              | `HttpKernel`, `ConsoleKernel`                                   | `components/Application/Kernel`    | `Avax\Components\Application\Kernel\System\Capabilities\Kernels` | HTTP/Console kernels.                                |

---

## Complete Mapping Summary

### By Target Suite/Component

#### `components/DataStack/Data`

- DataFoundation\Collections → Collections capability
- DataFoundation\Composites → Composites capability
- DataFoundation\DataTransfer → DataTransfer capability
- DataFoundation\Flows → Pipeline capability
- DataFoundation\Structures → Data structures
- DataFoundation\Internal → Internal utilities
- DataFoundation\Contracts → Foundation contracts
- DataFoundation\Exceptions → Foundation failures
- DataFoundation\Validation → Validation rules

#### `components/DataStack/Persistence`

- DataLayer → Persistence capabilities
- Database\ORM\UnitOfWork → UnitOfWork capability
- Database\EntityManager → Entity management
- Database\EntityRepository → Repository pattern

#### `components/DataStack/Database`

- Database → Database capabilities
- Database\Connections → Connection management
- Database\Migrations → Migrations (from DataLayer\EvolveStoredSchema)
- Database\Query → QueryBuilder, Grammar
- Database\Telemetry → Telemetry

#### `components/DataStack/Cache`

- Cache → Full cache system

#### `components/Application/Container`

- Container → DI container
- ServiceProviders → Provider pattern

#### `components/Application/Config`

- Config → Configuration system

#### `components/Application/Facade`

- Facade → Static accessors (legacy pattern)

#### `components/Application/Text`

- Text → String/text helpers
- Auth\Foundation\Text → Auth text utilities

#### `components/Application/DateTime`

- DateTime → Date/time system (NOT Carbon)

#### `components/Application/Localization`

- Translator/Lang → **NEEDS IMPLEMENTATION**

#### `components/Application/Events`

- Events → Event dispatcher system

#### `components/Application/Workflow`

- ApplicationWorkflow\Saga → Saga orchestration

#### `components/Application/Kernel`

- Framework → Application kernel, runtime

#### `components/CLI/Console`

- Commands → Console kernel, make commands
- Generators → Code generators
- Database\Console → Database CLI commands

#### `components/HTTP/Middleware`

- Middlewares → Middleware pipeline
- HTTP\Middleware → Built-in middleware
- HTTP\Middleware\CSRF → CSRF (also maps to Security)

#### `components/HTTP/Context`

- HTTP\Context → HTTP context management

#### `components/HTTP/Security`

- HTTP\Middleware\CSRF → CSRF tokens
- Auth\Tenancy\Security → Tenant security

#### `components/HTTP/URI`

- BaseUri → URI handling

#### `components/HTTP/Router`

- Router → Full routing system

#### `components/HTTP/Session`

- Session → Full session system

#### `components/HTTP/Request`

- Request → HTTP request handling

#### `components/HTTP/Response`

- Response → HTTP response handling

#### `components/Presentation/View`

- View → Template rendering
- BladeTemplateEngine → Blade engine
- TemplateEngine → BladeOne engine

#### `components/Operations/Mail`

- Mail → Email system

#### `components/Operations/Queue`

- Queue → Job queue system
- Worker → Worker management

#### `components/Operations/Notifications`

- SecurityNotification → Security notifications

#### `components/Storage/Filesystem`

- Filesystem → File/disk operations

#### `components/Observability/Logging`

- Logging → Logger system

#### `components/DeveloperTools/DumpDebugger`

- DumpDebugger → Debug dump
- ErrorHandler → Exception handling

#### `components/Auth`

- Auth → OAuth2, OIDC, SSO, SCIM, Passkeys, MFA

---

## Features That Need New Implementation

| Feature                    | Status                       | Notes                                                                 |
|----------------------------|------------------------------|-----------------------------------------------------------------------|
| **Translator/Lang (i18n)** | NOT IMPLEMENTED              | Only referenced in recovery docs. Needs full build.                   |
| **Str (static helper)**    | NOT IMPLEMENTED as god-class | Functionality distributed across Text capabilities.                   |
| **Arr (static helper)**    | NOT IMPLEMENTED as god-class | Functionality distributed across Collection.                          |
| **Carbon (date helper)**   | NOT IMPLEMENTED              | Custom DateTime system exists instead.                                |
| **Notification system**    | PARTIAL                      | Only SecurityNotification exists. General notification system needed. |
| **AuthMiddleware**         | STUB                         | Noted as needing implementation (4 references in backup).             |

---

## Migration Strategy Recommendations

1. **Phase 1**: Migrate fully-implemented, self-contained features first (Text, DateTime, Config, Facade)
2. **Phase 2**: Migrate interdependent features (Container → Config → Facade chain)
3. **Phase 3**: Migrate complex features (Cache, Session, Queue, Mail)
4. **Phase 4**: Migrate massive features (Auth, ORM/Database, Router)
5. **Phase 5**: Build new features from scratch (i18n/Localization, general Notifications)
6. **Phase 6**: Clean up legacy patterns (Facade deprecation, god-class elimination)
