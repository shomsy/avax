# Component Suite Migration Truth

## Status

Phase 0: Inventory - Refreshed (2026-04-28)

## Current `components/` root folders (fact)

```
components/
├── Application/            # suite (exists)
├── CLI/                    # suite (exists)
├── DataStack/              # suite (exists)
├── DeveloperTools/         # suite (exists)
├── HTTP/                   # suite (exists)
├── Identity/               # suite (exists)
├── Operations/             # suite (exists)
├── Presentation/           # suite (exists)
│
├── ApplicationWorkflow/    # legacy root owner (must be moved or deleted)
├── Auth/                   # legacy root component (must become Identity/Auth bridge or be deleted)
├── Cache/                  # legacy root component (must become Application/Cache bridge or be deleted)
├── Commands/               # legacy root component (must become CLI/Console bridge or be deleted)
├── Container/              # legacy root component (must become Application/Container bridge or be deleted)
├── Data/                   # legacy root component (must become DataStack/Data bridge or be deleted)
├── DataFoundation/         # legacy (must become bridge-only or be deleted)
├── Database/               # legacy root component (must become DataStack/Database bridge or be deleted)
├── DateTime/               # legacy root component (must become Application/DateTime bridge or be deleted)
├── DumpDebugger/           # legacy root component (must become DeveloperTools/DumpDebugger bridge or be deleted)
├── Events/                 # legacy root component (must become Operations/Events bridge or be deleted)
├── Filesystem/             # legacy root component (must become Application/Filesystem bridge or be deleted)
├── Logging/                # legacy root component (must become Operations/Logging bridge or be deleted)
├── Mail/                   # legacy root component (must become Operations/Mail bridge or be deleted)
├── Persistence/            # legacy root component (must become DataStack/Persistence bridge or be deleted)
├── Queue/                  # legacy root component (must become Operations/Queue bridge or be deleted)
├── Router/                 # legacy root component (must become HTTP/Router bridge or be deleted)
├── Security/               # legacy root component (must become Identity/Security or HTTP/Security bridge or be deleted)
├── Text/                   # legacy root component (must become Application/Text bridge or be deleted)
├── Validation/             # legacy root component (must become Application/Validation bridge or be deleted)
├── View/                   # legacy root component (must become Presentation/View bridge or be deleted)
│
├── compat.php              # temporary compatibility file autoloaded by composer
├── new-component.md        # documentation/helper (non-production)
├── storage/                # test/dev storage (verify scope)
└── tests/                  # component-local tests (should be migrated to repo `tests/` or documented)
```

## Target Suite Structure

```
components/
├── Application/       # NEW - Config, Container, Cache, Filesystem, Validation, Text, DateTime
├── HTTP/              # SUITE - Request, Response, Router, Middleware, Session, Cookies, URI, Uploads
├── CLI/               # NEW - Console (Commands)
├── DataStack/         # NEW - Data, Database, Persistence (DataFoundation -> Data, DataLayer -> Persistence)
├── Identity/          # NEW - Auth, Security
├── Operations/        # NEW - Events, Logging, Mail, Queue
├── Presentation/      # NEW - View
├── DeveloperTools/    # NEW - DumpDebugger
```

## Classification Table

| Current Path              | Current Role      | Target Suite   | Target Component | Action                           |
|---------------------------|-------------------|----------------|------------------|----------------------------------|
| components/Auth           | legacy root owner | Identity       | Auth             | move/merge then bridge or delete |
| components/Security       | legacy root owner | Identity       | Security         | move/merge then bridge or delete |
| components/Cache          | legacy root owner | Application    | Cache            | move/merge then bridge or delete |
| components/Container      | legacy root owner | Application    | Container        | move/merge then bridge or delete |
| components/DateTime       | legacy root owner | Application    | DateTime         | move/merge then bridge or delete |
| components/Filesystem     | legacy root owner | Application    | Filesystem       | move/merge then bridge or delete |
| components/Text           | legacy root owner | Application    | Text             | move/merge then bridge or delete |
| components/Validation     | legacy root owner | Application    | Validation       | move/merge then bridge or delete |
| components/Data           | legacy root owner | DataStack      | Data             | move/merge then bridge or delete |
| components/DataFoundation | legacy namespace  | DataStack      | Data             | merge then bridge-only or delete |
| components/Database       | legacy root owner | DataStack      | Database         | move/merge then bridge or delete |
| components/Persistence    | legacy root owner | DataStack      | Persistence      | move/merge then bridge or delete |
| components/Router         | legacy root owner | HTTP           | Router           | move/merge then bridge or delete |
| components/Commands       | legacy root owner | CLI            | Console          | move/merge then bridge or delete |
| components/Events         | legacy root owner | Operations     | Events           | move/merge then bridge or delete |
| components/Logging        | legacy root owner | Operations     | Logging          | move/merge then bridge or delete |
| components/Mail           | legacy root owner | Operations     | Mail             | move/merge then bridge or delete |
| components/Queue          | legacy root owner | Operations     | Queue            | move/merge then bridge or delete |
| components/View           | legacy root owner | Presentation   | View             | move/merge then bridge or delete |
| components/DumpDebugger   | legacy root owner | DeveloperTools | DumpDebugger     | move/merge then bridge or delete |

## Namespace inventory (fact)

### Forbidden: `namespace components\...` in canonical source

- `components/Application/Container/DI/Capabilities/Composition/Compilation/CompileContainer.php` contains a generated
  namespace: `components\Container\Capabilities\Composition\Compilation\Generated`

### Legacy: `namespace Avax\DataFoundation\...`

- `components/DataFoundation/Arrhae.php`
- `components/DataFoundation/Collection.php`
- `components/DataFoundation/ObjectHandling/DTO/AbstractDTO.php`
- `components/DataFoundation/Validation/Attributes/Rules/IntegerRule.php`

### Legacy: `namespace Avax\DataLayer\...`

- Only present in aggregated dumps (`avax.txt`) at the repo root. No real `components/DataLayer/` folder exists.

## Phase 0 Done

- [x] Component tree inventory
- [x] Top-level component classification
- [x] Target suite mapping

## Next

Phase 2: Move DataStack (Data, Database, Persistence) into `components/DataStack/*`, then delete/bridge legacy roots.

*Updated: 2026-04-28*