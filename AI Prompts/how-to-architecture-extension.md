Here is the polished **enterprise-grade** version for `how-to-architecture-extension.md`.

````md
# PublicSurface Architecture Extension

## 1. Status of This Rule

This document is an architecture extension to the main architecture governance.

It defines when and how a framework or component system root may expose a `PublicSurface/` folder.

This rule is not a style preference.
This rule is not a facade convention.
This rule is not a place to hide unclear design.

It is a boundary rule for stable public API ownership.

---

## 2. Purpose

`PublicSurface/` is the explicit home for stable public API entrypoints.

Its purpose is to separate:

- what external users are allowed to touch
- what the framework or component owns internally
- what may change freely
- what must remain stable and versioned carefully

A public API is a contract with the outside world.

Internal structure may evolve aggressively.
Public surface must evolve deliberately.

`PublicSurface/` exists to make that boundary visible in the filesystem.

---

## 3. Core Rule

Every framework or component system root may have `PublicSurface/` when it exposes stable public API units.

However, `PublicSurface/` must be justified.

A system root must not create `PublicSurface/` automatically, mechanically, or decoratively.

Use `PublicSurface/` only when it improves:

- API clarity
- package usability
- boundary safety
- external discoverability
- long-term compatibility
- documentation quality

If a folder has no stable external API, it must not have `PublicSurface/`.

---

## 4. Ownership Rule

`PublicSurface/` owns external entrypoints only.

It may receive the first user call.

It may normalize simple public input.

It may delegate to internal flows and capabilities.

It must not own the real behavior of the system.

The correct responsibility is:

```text
PublicSurface/ receives public calls.
Flows/ execute behavior.
Capabilities/ provide reusable mechanisms.
Configuration/ assembles the system.
Foundation/ provides tiny neutral primitives.
````

`PublicSurface/` is a doorway.
It is not the engine.

---

## 5. Mandatory Delegation Rule

Every public surface unit must delegate real work to `Flows/`, `Capabilities/`, or `Configuration/`.

A public surface class may coordinate the first call, but it must not implement the full behavior.

Good:

```text
PublicSurface/
  Cache.php
    -> delegates to Flows/ReadCachedValue
    -> delegates to Flows/StoreCachedValue
    -> delegates to Flows/RememberCachedValue
```

Bad:

```text
PublicSurface/
  Cache.php
    -> contains storage logic
    -> serializes values directly
    -> calculates TTL internals directly
    -> talks directly to the filesystem
    -> manages driver behavior internally
```

If the public surface starts doing the work, the boundary has failed.

---

## 6. Allowed Contents

`PublicSurface/` may contain:

* facade classes
* root public API classes
* public contracts
* package entrypoints
* stable aliases
* public DTOs that are part of the external API
* public value objects that are part of the external API
* public factories that protect users from internal construction details
* public kernel interfaces when they are part of the external framework API

Examples:

```text
framework/
  System/
    PublicSurface/
      Avax.php
      AvaxInterface.php

      Http/
        HttpKernel.php
        HttpKernelInterface.php

      Console/
        ConsoleKernel.php
        ConsoleKernelInterface.php

      Runtime/
        RuntimeKernel.php
        RuntimeKernelInterface.php

      Facades/
        App.php
        Route.php
        Cache.php
```

Component example:

```text
components/
  Cache/
    System/
      PublicSurface/
        Cache.php
        CacheInterface.php

        Facades/
          CacheFacade.php

        Contracts/
          CacheStoreInterface.php
```

---

## 7. Forbidden Contents

`PublicSurface/` must not contain:

* business logic
* runtime machinery
* flow implementation
* adapter-specific code
* infrastructure details
* request-scoped mutable state
* service registration internals
* internal registries unless they are intentionally part of the public API
* random helper classes
* generic utility classes
* dumping-ground abstractions
* framework internals hidden behind public-looking names

Bad examples:

```text
PublicSurface/
  HandleIncomingHttp.php
  SwooleRequestAdapter.php
  BuildContainer.php
  ResolveDependencies.php
  CacheHelper.php
  InternalRegistry.php
  RuntimeStateStore.php
  WorkerLoop.php
```

These belong elsewhere:

```text
Flows/
  HandleIncomingHttp/

Capabilities/
  Runtime/
    Adapters/
      Swoole/

Configuration/
  BuildApplication/

Foundation/
  Time/
  Paths/
  Failure/
```

---

## 8. Stability Rule

Changing `PublicSurface/` is a public API decision.

Any breaking change inside `PublicSurface/` must be treated as a versioned compatibility change.

This includes:

* renaming public classes
* removing public methods
* changing method signatures
* changing return types
* changing exception behavior
* changing lifecycle guarantees
* changing facade behavior
* changing public DTO or value object shape

Internal folders may change more freely.

`PublicSurface/` must change carefully because users build code against it.

---

## 9. Small Surface Rule

`PublicSurface/` must stay small.

A large public surface is a design warning.

If too many files are needed in `PublicSurface/`, ask:

```text
Is the public API too broad?
Are internals leaking?
Are too many concepts exposed?
Is this component doing too much?
Should some APIs be moved behind a smaller facade?
Should some contracts remain internal?
```

A strong public API is usually small, boring, predictable, and easy to document.

---

## 10. No Runtime Leakage Rule

Runtime-specific APIs must not leak into `PublicSurface/`.

Framework users may choose a runtime.

Core public APIs must not force users to know about runtime internals unless the public API is explicitly about runtime
integration.

Forbidden:

```text
PublicSurface/
  SwooleRequest.php
  RoadRunnerWorker.php
  FrankenPhpResponse.php
```

Allowed when explicitly scoped:

```text
PublicSurface/
  Runtime/
    RuntimeKernel.php
    RuntimeKernelInterface.php
```

Runtime-specific implementation belongs in:

```text
Capabilities/
  Runtime/
    Adapters/
      Swoole/
      RoadRunner/
      FrankenPhp/
      Workerman/
```

The public API may expose Avax runtime abstractions.
It must not expose server-specific implementation details.

---

## 11. No Request State Rule

`PublicSurface/` must not hold request-scoped mutable state.

This is critical for long-lived runtimes.

A public surface class must not store:

* current request
* current response
* current user
* current session state
* current route match
* current correlation context
* per-request cache
* temporary runtime state

Request-specific state must live in explicit request scope ownership.

This protects Avax from leaking state between requests in worker runtimes such as FrankenPHP, RoadRunner, Swoole, and
Workerman.

---

## 12. Placement Rule

Use `PublicSurface/` inside a framework or component system root.

Framework example:

```text
framework/
  System/
    PublicSurface/
    Flows/
    Capabilities/
    Configuration/
    Foundation/
```

Component example:

```text
components/
  Router/
    System/
      PublicSurface/
      Flows/
      Capabilities/
      Configuration/
      Foundation/
```

Do not create a top-level repository `PublicSurface/` folder unless the repository itself is a single package with one
system root.

For Avax, the preferred model is:

```text
avax/
  framework/
    System/
      PublicSurface/

  components/
    Cache/
      System/
        PublicSurface/

    Router/
      System/
        PublicSurface/
```

---

## 13. Naming Rule

Files inside `PublicSurface/` must use public API names.

Good:

```text
Avax.php
HttpKernel.php
ConsoleKernel.php
RuntimeKernel.php
Cache.php
Router.php
Database.php
```

Weak:

```text
ApiManager.php
PublicHelper.php
MainService.php
FacadeThing.php
Processor.php
InternalBridge.php
```

Names must be obvious to a user who has not opened the implementation.

A public API name must explain what the user is touching.

---

## 14. Documentation Rule

Every `PublicSurface/` folder must be documented.

The documentation must answer:

```text
What is the public API here?
Who is allowed to use it?
What is stable?
What is intentionally hidden?
Which internal flows does it delegate to?
What must not be used directly?
What counts as a breaking change?
```

If the public surface cannot be documented simply, the API is probably too broad or too unclear.

---

## 15. Review Checklist

A `PublicSurface/` folder passes architecture review only if all of the following are true:

* every file is externally useful
* every file has a stable reason to exist
* every file is easy to explain to users
* every file delegates instead of implementing internals
* no business logic lives inside it
* no flow implementation lives inside it
* no runtime adapter leaks into it
* no request-scoped mutable state lives inside it
* no service registration internals live inside it
* no random helpers live inside it
* no generic dumping-ground abstractions live inside it
* the public API is small enough to document clearly
* changing it would clearly be understood as a public API change
* removing the folder would make the public API harder to understand

If any item fails, the design must be corrected before the architecture is accepted.

---

## 16. Final Law

`PublicSurface/` is allowed only when it protects a real public API boundary.

It must make the system easier to use from the outside and safer to evolve from the inside.

It is not a decoration.
It is not a convenience folder.
It is not a place for internals.

Public surface receives.
Flows execute.
Capabilities power.
Configuration assembles.
Foundation supports.

```
```
