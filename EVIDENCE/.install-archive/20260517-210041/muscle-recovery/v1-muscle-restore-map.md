# V1 Muscle Restore Map — V1-02.5

**Date**: 2026-05-05  
**Stage**: V1-02.5  
**Purpose**: Safe implementation guide for V1 muscle restoration

---

## Missing V1 Muscles - Target Trees

### 1. DataStack/Database

| Sub-Feature    | Old Path                           | Old Namespace     | Target Path                                              | Action  |
|----------------|------------------------------------|-------------------|----------------------------------------------------------|---------|
| Query Builder  | components/Database/Query.php      | App\DB\Query      | DataStack/Database/System/PublicSurface/QueryBuilder.php | restore |
| Schema Builder | components/Database/Schema.php     | App\DB\Schema     | DataStack/Database/System/PublicSurface/Schema.php       | restore |
| Migrations     | components/Database/Migrations.php | App\DB\Migrations | DataStack/Database/System/PublicSurface/Migration.php    | restore |
| Connection     | components/Database/Connection.php | App\DB\Connection | DataStack/Database/System/Capabilities/Connections/      | restore |
| Grammar        | components/Database/Grammar.php    | App\DB\Grammar    | DataStack/Database/System/Capabilities/Grammar/          | restore |

**Target Tree**:

```
DataStack/Database/System/
├── PublicSurface/
│   ├── Database.php
│   ├── QueryBuilder.php
│   ├── Schema.php
│   ├── Migration.php
│   └── Connection.php
├── Flows/
│   ├── RunQuery/
│   ├── BuildQuery/
│   ├── RunMigration/
│   └── RollbackMigration/
├── Capabilities/
│   ├── Connections/
│   ├── QueryBuilding/
│   ├── Grammar/
│   ├── SchemaBuilding/
│   └── Migrations/
├── Configuration/
├── Foundation/
├── tests/
│   ├── QueryBuilderTest.php
│   ├── SchemaTest.php
│   └── MigrationsTest.php
```

---

### 2. DataStack/Persistence

| Sub-Feature    | Old Path                              | Old Namespace        | Target Path                                               | Action  |
|----------------|---------------------------------------|----------------------|-----------------------------------------------------------|---------|
| UnitOfWork     | components/Database/EntityManager.php | App\DB\EntityManager | DataStack/Persistence/System/Capabilities/UnitOfWork/     | restore |
| IdentityMap    | components/Database/EntityManager.php | App\DB\EntityManager | DataStack/Persistence/System/Capabilities/IdentityMap/    | restore |
| Repository     | components/Database/EntityManager.php | App\DB\EntityManager | DataStack/Persistence/System/PublicSurface/Repository.php | restore |
| Hydration      | components/Database/EntityManager.php | App\DB\EntityManager | DataStack/Persistence/System/Capabilities/Hydration/      | restore |
| ChangeTracking | components/Database/EntityManager.php | App\DB\EntityManager | DataStack/Persistence/System/Capabilities/ChangeTracking/ | restore |

**Target Tree**:

```
DataStack/Persistence/System/
├── PublicSurface/
│   ├── Persistence.php
│   └── Repository.php
├── Flows/
│   ├── PersistEntity/
│   ├── RemoveEntity/
│   └── FindEntity/
├── Capabilities/
│   ├── UnitOfWork/
│   ├── IdentityMap/
│   ├── Repositories/
│   ├── Hydration/
│   └── ChangeTracking/
├── Configuration/
├── Foundation/
├── tests/
│   ├── UnitOfWorkTest.php
│   ├── RepositoryTest.php
│   └── HydrationTest.php
```

---

### 3. Presentation/View

| Sub-Feature     | Old Path                                | Old Namespace     | Target Path                                     | Action  |
|-----------------|-----------------------------------------|-------------------|-------------------------------------------------|---------|
| Template Engine | components/View/BladeTemplateEngine.php | App\View\Engine   | Presentation/View/System/PublicSurface/View.php | restore |
| Templates       | components/View/*.php                   | App\View          | Presentation/View/System/Foundation/Templates/  | restore |
| Compiler        | components/View/Compiler.php            | App\View\Compiler | Presentation/View/System/Capabilities/Compiler/ | restore |
| Cache           | components/View/Cache.php               | App\View\Cache    | Presentation/View/System/Capabilities/Cache/    | restore |

**Target Tree**:

```
Presentation/View/System/
├── PublicSurface/
│   ├── View.php
│   └── BladeTemplateEngine.php
├── Flows/
│   ├── RenderTemplate/
│   └── CompileTemplate/
├── Capabilities/
│   ├── Compiler/
│   ├── Cache/
│   └── Templates/
├── Configuration/
├── Foundation/
├── tests/
│   ├── ViewTest.php
│   └── TemplateCompilationTest.php
```

---

### 4. Operations/Events

| Sub-Feature | Old Path                         | Old Namespace         | Target Path                                                | Action  |
|-------------|----------------------------------|-----------------------|------------------------------------------------------------|---------|
| Dispatcher  | components/Events/Dispatcher.php | App\Events\Dispatcher | Operations/Events/System/PublicSurface/EventDispatcher.php | restore |
| Listeners   | components/Events/Listener.php   | App\Events\Listener   | Operations/Events/System/Capabilities/Listeners/           | restore |
| Subscribers | components/Events/Subscriber.php | App\Events\Subscriber | Operations/Events/System/Capabilities/Subscribers/         | restore |

**Target Tree**:

```
Operations/Events/System/
├── PublicSurface/
│   └── EventDispatcher.php
├── Flows/
│   ├── DispatchEvent/
│   └── RegisterListener/
├── Capabilities/
│   ├── Listeners/
│   ├── Subscribers/
│   └── Priorities/
├── Configuration/
├── Foundation/
├── tests/
│   ├── EventDispatcherTest.php
│   └── ListenerTest.php
```

---

### 5. HTTP/Client

| Sub-Feature | Old Path                             | Old Namespace              | Target Path                                         | Action  |
|-------------|--------------------------------------|----------------------------|-----------------------------------------------------|---------|
| Client      | components/HTTP/Client.php           | App\Http\Client            | HTTP/Client/System/PublicSurface/Client.php         | restore |
| Request     | components/HTTP/ClientRequest.php    | App\Http\Client\Request    | HTTP/Client/System/PublicSurface/ClientRequest.php  | restore |
| Response    | components/HTTP/ClientResponse.php   | App\Http\Client\Response   | HTTP/Client/System/PublicSurface/ClientResponse.php | restore |
| Middleware  | components/HTTP/ClientMiddleware.php | App\Http\Client\Middleware | HTTP/Client/System/Capabilities/Middleware/         | restore |

**Target Tree**:

```
HTTP/Client/System/
├── PublicSurface/
│   └── Client.php
├── Flows/
│   ├── SendRequest/
│   └── BuildRequest/
├── Capabilities/
│   ├── Middleware/
│   ├── Retry/
│   └── Retry/
├── Configuration/
├── Foundation/
├── tests/
│   ├── ClientTest.php
│   └── RequestTest.php
```

---

### 6. Operations/Observability

| Sub-Feature | Old Path                             | Old Namespace         | Target Path                                              | Action  |
|-------------|--------------------------------------|-----------------------|----------------------------------------------------------|---------|
| Logger      | components/Logging/Logger.php        | App\Logging\Logger    | Operations/Observability/System/PublicSurface/Logger.php | restore |
| Log Writer  | components/Logging/FileLogWriter.php | App\Logging\Writer    | Operations/Observability/System/Capabilities/Writers/    | restore |
| Channels    | components/Logging/Channel.php       | App\Logging\Channel   | Operations/Observability/System/Capabilities/Channels/   | restore |
| Formatters  | components/Logging/Formatter.php     | App\Logging\Formatter | Operations/Observability/System/Capabilities/Formatters/ | restore |

**Target Tree**:

```
Operations/Observability/System/
├── PublicSurface/
│   └── Logger.php
├── Flows/
│   ├── WriteLog/
│   └── ConfigureChannel/
├── Capabilities/
│   ├── Writers/
│   ├── Channels/
│   ├── Formatters/
│   └── Processors/
├── Configuration/
├── Foundation/
├── tests/
│   ├── LoggerTest.php
│   └── ChannelTest.php
```

---

### 7. Application/Localization

| Sub-Feature | Old Path | Old Namespace | Target Path                                                  | Action |
|-------------|----------|---------------|--------------------------------------------------------------|--------|
| Translator  | -        | -             | Application/Localization/System/PublicSurface/Translator.php | build  |
| Locale      | -        | -             | Application/Localization/System/Capabilities/Locale/         | build  |
| Catalogs    | -        | -             | Application/Localization/System/Capabilities/Catalogs/       | build  |
| Fallbacks   | -        | -             | Application/Localization/System/Capabilities/Fallbacks/      | build  |

**Target Tree**:

```
Application/Localization/System/
├── PublicSurface/
│   ├── Translator.php
│   └── Lang.php
├── Flows/
│   ├── TranslateMessage/
│   └── LoadTranslations/
├── Capabilities/
│   ├── Locale/
│   ├── Catalogs/
│   └── Fallbacks/
├── Configuration/
├── Foundation/
├── tests/
│   ├── TranslatorTest.php
│   └── LocaleTest.php
```

---

### 8. Operations/Notifications

| Sub-Feature | Old Path | Old Namespace | Target Path                                                | Action |
|-------------|----------|---------------|------------------------------------------------------------|--------|
| Notifier    | -        | -             | Operations/Notifications/System/PublicSurface/Notifier.php | build  |
| Channels    | -        | -             | Operations/Notifications/System/Capabilities/Channels/     | build  |
| Templates   | -        | -             | Operations/Notifications/System/Capabilities/Templates/    | build  |

**Target Tree**:

```
Operations/Notifications/System/
├── PublicSurface/
│   └── Notifications.php
├── Flows/
│   └── SendNotification/
├── Capabilities/
│   ├── Channels/
│   ├── Templates/
│   └── Recipients/
├── Configuration/
├── Foundation/
├── tests/
│   ├── NotifierTest.php
│   └── ChannelTest.php
```

---

## Test Family Mapping (Backup -> Target)

| Old Test Path      | Target Test Path                           |
|--------------------|--------------------------------------------|
| tests/Text/*       | tests/components/Application/Text/         |
| tests/Arr/*        | tests/components/DataStack/Data/           |
| tests/Collection/* | tests/components/DataStack/Data/           |
| tests/Container/*  | tests/components/Application/Container/    |
| tests/DateTime/*   | tests/components/Application/DateTime/     |
| tests/Config/*     | tests/components/Application/Config/       |
| tests/Facade/*     | tests/components/Application/Facade/       |
| tests/Cache/*      | tests/components/Application/Cache/        |
| tests/Database/*   | tests/components/DataStack/Database/       |
| tests/ORM/*        | tests/components/DataStack/Persistence/    |
| tests/Router/*     | tests/components/HTTP/Router/              |
| tests/Request/*    | tests/components/HTTP/Request/             |
| tests/Response/*   | tests/components/HTTP/Response/            |
| tests/Middleware/* | tests/components/HTTP/Middleware/          |
| tests/Session/*    | tests/components/HTTP/Session/             |
| tests/Auth/*       | tests/components/Identity/Auth/            |
| tests/Queue/*      | tests/components/Operations/Queue/         |
| tests/Mail/*       | tests/components/Operations/Mail/          |
| tests/View/*       | tests/components/Presentation/View/        |
| tests/Validation/* | tests/components/Application/Validation/   |
| tests/Events/*     | tests/components/Operations/Events/        |
| tests/HttpClient/* | tests/components/HTTP/Client/              |
| tests/Logging/*    | tests/components/Operations/Observability/ |
| tests/Filesystem/* | tests/components/Application/Filesystem/   |

---

## Implementation Priority Order

| Order | Component                | Priority | Risk   |
|-------|--------------------------|----------|--------|
| 1     | DataStack/Database       | high     | high   |
| 2     | DataStack/Persistence    | high     | high   |
| 3     | Operations/Observability | high     | high   |
| 4     | HTTP/Client              | low      | medium |
| 5     | Presentation/View        | medium   | high   |
| 6     | Operations/Events        | low      | medium |
| 7     | Application/Localization | high     | high   |
| 8     | Operations/Notifications | high     | high   |

---

## Output Files

1. `/home/shomsy/projects/avax/EVIDENCE/muscle-recovery/v1-muscle-restore-map.md` (this file)

---

## Acceptance

- [x] Every missing V1 muscle has target tree
- [x] Every sub-feature has action classification
- [x] Every old test family maps to new path
- [x] V2/V3 features locked
- [x] No production code changed

**Status**: ✓ Stage V1-02.5 COMPLETE