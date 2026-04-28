# Component Suite Migration Truth

## Status
Phase 0: Inventory - In Progress

## Current Top-Level Components

```
components/
├── ApplicationWorkflow/  # TBD: Application suite
├── Auth/                # TBD: Identity/Auth
├── Cache/               # → Application/Cache
├── Commands/            # → CLI/Console
├── Config/              # → Application/Config
├── Container/          # → Application/Container
├── Data/               # → DataStack/Data
├── DataFoundation/     # → DataStack/Data (merge then bridge/delete)
├── DataLayer/         # DOES NOT EXIST
├── Database/          # → DataStack/Database
├── DateTime/          # → Application/DateTime
├── DumpDebugger/      # → DeveloperTools/DumpDebugger
├── Events/           # → Operations/Events
├── Filesystem/        # → Application/Filesystem
├── HTTP/             # KEEP AS SUITE
├── Logging/          # → Operations/Logging
├── Mail/            # → Operations/Mail
├── Persistence/      # → DataStack/Persistence
├── Queue/           # → Operations/Queue
├── Router/          # → HTTP/Router (under HTTP suite)
├── Security/       # TBD: Identity/Security or HTTP/Security
├── Session/         # DELETED (empty)
├── Middleware/      # DELETED (empty)
├── Text/            # → Application/Text
├── Validation/      # → Application/Validation
└── View/           # → Presentation/View
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

| Current Path | Current Role | Target Suite | Target Component | Action |
|---|---|---|---|---|
| components/Config | real owner | Application | Config | move |
| components/Container | real owner | Application | Container | move |
| components/Cache | real owner | Application | Cache | move |
| components/Filesystem | real owner | Application | Filesystem | move |
| components/Validation | real owner | Application | Validation | move |
| components/Text | real owner | Application | Text | move |
| components/DateTime | real owner | Application | DateTime | move |
| components/HTTP | suite owner | HTTP | HTTP | keep |
| components/Router | real owner | HTTP | Router | move under HTTP |
| components/Session | deleted | HTTP | Session | already deleted |
| components/Middleware | deleted | HTTP | Middleware | already deleted |
| components/Data | partial owner | DataStack | Data | move |
| components/DataFoundation | legacy real | DataStack | Data | merge then bridge/delete |
| components/Database | real owner | DataStack | Database | move |
| components/Persistence | real owner | DataStack | Persistence | move |
| components/Auth | real owner | Identity | Auth | move |
| components/Security | real owner | Identity | Security | move |
| components/Events | real owner | Operations | Events | move |
| components/Logging | real owner | Operations | Logging | move |
| components/Mail | real owner | Operations | Mail | move |
| components/Queue | real owner | Operations | Queue | move |
| components/View | real owner | Presentation | View | move |
| components/DumpDebugger | real owner | DeveloperTools | DumpDebugger | move |
| components/Commands | real owner | CLI | Console | move |

## Phase 0 Done

- [x] Component tree inventory
- [x] Top-level component classification
- [x] Target suite mapping

## Next

Phase 1: Create suite skeleton folders

*Updated: 2026-04-28*