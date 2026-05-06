# Missing Features Report (Old vs New Architecture)

This report compares the components backed up in `avax.txt` with the current `components/` directory.

## Completely Missing Components / Suites

- **Avax.php** (Found in avax.txt, not found in new architecture)
- **Commands** (Found in avax.txt, not found in new architecture)
- **DataFoundation** (Found in avax.txt, not found in new architecture)
- **DataLayer** (Found in avax.txt, not found in new architecture)
- **Facade** (Found in avax.txt, not found in new architecture)
- **Middlewares** (Found in avax.txt, not found in new architecture)
- **new-component.md** (Found in avax.txt, not found in new architecture)
- **tests** (Found in avax.txt, not found in new architecture)
- **compat.php** (Found in avax.txt, not found in new architecture)

## Detailed Component Analysis

### ApplicationWorkflow

- **Missing sub-components/folders:**
    - `Saga`

### Auth

- **Missing sub-components/folders:**
    - `.agents`
    - `.codex`
    - `.cursorrules`
    - `.github`
    - `.gitignore`
    - `AGENTS.md`
    - `Auth.txt`
    - `CHANGES_SUMMARY.txt`
    - `CLAUDE.md`
  - `EVIDENCE`
    - `GEMINI.md`
    - `README.md`
    - `REFAKTOR.md`
    - `TODO.md`
    - `complete-this.md`
    - `composer.json`
    - `composer.lock`
    - `infection.json.dist`
    - `merge-files.sh`
    - `phpstan.neon`
    - `phpstan.strict.neon`
    - `phpstan.stubs`
    - `phpunit.xml.dist`
    - `tests`
    - `tooling`

### Avax.php

- *Component is entirely missing.*

### Cache

- **Missing sub-components/folders:**
    - `AGENTS.md`
    - `Cache.txt`
    - `README.md`
    - `composer.json`
    - `composer.lock`
    - `docs`
    - `improvements.md`
    - `merge-files.sh`
    - `phpstan.neon`
    - `phpunit.xml`
    - `refactor.md`

### Commands

- *Component is entirely missing.*
- Lost features include: `App`, `CommandDefinitions.php`, `System`

### Config

- **Missing sub-components/folders:**
    - `Architecture`
    - `Service`

### Container

- **Missing sub-components/folders:**
    - `.agents`
    - `.markdownlint.json`
    - `.markdownlintignore`
    - `AGENTS.md`
    - `Container.txt`
    - `DI`
    - `REFACTOR.md`
    - `how-to-code-review.md`
    - `how-to-coding-standards.md`
    - `how-to-document.md`
    - `merge-files.sh`

### DataFoundation

- *Component is entirely missing.*
- Lost features include: `Arrhae.php`, `Collection.php`, `Collections`, `Composites`, `Contracts`

### DataLayer

- *Component is entirely missing.*
- Lost features include: `AccelerateDataReads`, `AccessPersistentData`, `CommitDataChanges`, `ConfigureDataLayer`,
  `CoordinateDataConsistency`

### Database

- **Missing sub-components/folders:**
    - `Database.txt`
    - `TODO.md`
    - `how-this-works.md`
    - `merge-files.sh`
    - `refactor.md`

### DumpDebugger

- *Fully migrated or structure matched.*

### Facade

- *Component is entirely missing.*
- Lost features include: `BaseFacade.php`, `Facades`

### Filesystem

- **Missing sub-components/folders:**
    - `EVIDENCE`
    - `Configuration`
    - `Directories`
    - `Disks`
    - `Files`
    - `Paths`
    - `docs`
    - `refactor.md`
    - `tooling`

### HTTP

- **Missing sub-components/folders:**
    - `EVIDENCE`
    - `Context`
    - `Dispatcher`
    - `Enums`
    - `Security`
    - `URI`
    - `how-this-works.md`
    - `refactor.md`

### Logging

- **Missing sub-components/folders:**
    - `Logging.txt`
    - `Writers`

### Middlewares

- *Component is entirely missing.*
- Lost features include: `MiddlewareInterface.php`

### Security

- *Fully migrated or structure matched.*

### Text

- **Missing sub-components/folders:**
    - `README.md`

### Validation

- *Fully migrated or structure matched.*

### View

- *Fully migrated or structure matched.*

### new-component.md

- *Component is entirely missing.*

### tests

- *Component is entirely missing.*
- Lost features include: `DataModeling`

### Data

- *Fully migrated or structure matched.*

### DateTime

- *Fully migrated or structure matched.*

### Events

- *Fully migrated or structure matched.*

### Mail

- *Fully migrated or structure matched.*

### Middleware

- *Fully migrated or structure matched.*

### Persistence

- *Fully migrated or structure matched.*

### Queue

- *Fully migrated or structure matched.*

### Request

- *Fully migrated or structure matched.*

### Response

- *Fully migrated or structure matched.*

### Router

- **Missing sub-components/folders:**
    - `tests`

### Session

- *Fully migrated or structure matched.*

### compat.php

- *Component is entirely missing.*

