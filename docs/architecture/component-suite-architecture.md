# Component Suite Architecture

## Overview

Avax uses a Component-Suite Architecture that groups related components into suites.

## Suites

| Suite | Purpose | Components |
|-------|---------|-----------|
| **Application** | Generic services | Config, Container, Cache, Filesystem, Validation, Text, DateTime |
| **HTTP** | Web protocol | Request, Response, Router, Middleware, Session, Security, URI |
| **CLI** | Console | Console |
| **DataStack** | Data handling | Data, Database, Persistence |
| **Identity** | Authentication/AuthZ | Auth, Access, Security, Tokens |
| **Operations** | Background ops | Events, Logging, Mail, Queue, Notifications |
| **Presentation** | Templating | View |
| **DeveloperTools** | Development | Diagnostics, DumpDebugger |

## Component Shape

Each component follows this structure:

```
components/<Suite>/<Component>/System/
  PublicSurface/   → Public API
  Flows/        → Behavior/Use cases
  Capabilities/  → Reusable mechanisms
  Configuration/ → Builders/Providers
  Foundation/   → Primitives/Failures
```

## Namespaces

- Framework: `Avax\Framework\System\...`
- Components: `Avax\Components\<Suite>\<Component>\System\...`

## Bridges

Legacy namespaces are bridged in:
- `components/DataFoundation/` → `components/DataStack/Data/System/...`
- `components/compat.php` → Various compatibility aliases