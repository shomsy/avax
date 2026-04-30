# Component Suite Migration Truth

## Status

Phase 3-7: Namespace normalization and cleanup (2026-04-28)

## Current `components/` root folders (fact)

```
components/
├── Application/       # suite - Config, Container, Cache, Filesystem, Validation, Text, DateTime
├── HTTP/              # suite - Request, Response, Router, Middleware, Session, Security, URI, Cookies
├── CLI/               # suite - Console
├── DataStack/         # suite - Data, Database, Persistence
├── Identity/          # suite - Auth, Security, Access, Tokens
├── Operations/        # suite - Events, Logging, Mail, Queue, Notifications, ApplicationWorkflow
├── Presentation/      # suite - View
├── DeveloperTools/    # suite - DumpDebugger, Diagnostics
├── DataFoundation/    # bridge-only (@deprecated, scheduled for removal)
│
├── compat.php         # temporary compatibility file autoloaded by composer
├── new-component.md   # documentation/helper (non-production)
```

## Classification Table

| Current Path              | Current Role              | Target Suite | Target Component | Action                |
|---------------------------|---------------------------|--------------|------------------|-----------------------|
| components/DataFoundation | bridge-only (@deprecated) | DataStack    | Data             | scheduled for removal |

All other legacy root owners (Auth, Cache, Commands, Container, Data, Database, DateTime, DumpDebugger, Events,
Filesystem, Logging, Mail, Persistence, Queue, Router, Security, Session, Middleware, Text, Validation, View,
ApplicationWorkflow) have been **moved into their target suites**.

## Namespace normalization results

- All canonical source now uses `Avax\Components\<Suite>\<Component>\...` namespace
- Framework uses `Avax\Framework\...` namespace
- No `namespace components\...` in canonical source
- No `Avax\Cache\...`, `Avax\Container\...`, `Avax\Config\...` etc. in canonical source
- DataFoundation bridge files still use `Avax\DataFoundation\...` (deprecated, scheduled for removal)
- compat.php maps legacy short namespaces to suite namespaces

## Composer autoload

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

Removed: `Avax\\` and `components\\` PSR-4 mappings.

## Architecture checker results

| Checker                         | Result                                                        |
|---------------------------------|---------------------------------------------------------------|
| check-component-suite-structure | PASS                                                          |
| check-namespace-drift           | PASS (0 violations)                                           |
| check-duplicate-owners          | PASS                                                          |
| check-forbidden-folders         | PASS                                                          |
| check-public-surface            | FAIL (3 known issues - SQL/IO in PublicSurface)               |
| check-docs-mirror               | FAIL (obsolete Foundation docs cleaned, remaining minor refs) |

## Bridge inventory

| Bridge                                                                | Status                                           | Removal Phase |
|-----------------------------------------------------------------------|--------------------------------------------------|---------------|
| components/DataFoundation/Arrhae.php                                  | @deprecated, delegates to DataStack/Data         | Next major    |
| components/DataFoundation/Collection.php                              | @deprecated, delegates to DataStack/Data         | Next major    |
| components/DataFoundation/ObjectHandling/DTO/AbstractDTO.php          | @deprecated, delegates to DataStack/Data         | Next major    |
| components/DataFoundation/Validation/Attributes/Rules/IntegerRule.php | @deprecated, delegates to Application/Validation | Next major    |
| components/compat.php                                                 | Temporary alias file                             | Next major    |

## Deleted items

- components/storage/ (duplicate cache files, already in var/cache/)
- components/tests/ (doc files moved to docs/)
- docs/Foundation/ (entire obsolete docs tree)
- components/Presentation/DevTools/ (empty directory)

## Remaining known issues

1. PublicSurface violations (3): HttpMethod.php (SQL), functions.php (loop), Filesystem.php (IO)
2. Identity/Auth has lowercase directory names (examples/, integrations/) with PascalCase namespaces - PSR-4 casing
   mismatch
3. DataFoundation bridge needs removal after compatibility window
4. compat.php needs removal after all external code migrates to suite namespaces

*Updated: 2026-04-28*
