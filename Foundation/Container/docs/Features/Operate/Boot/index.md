# Features/Operate/Boot

## What This Folder Represents

This folder previously contained boot-time orchestration for the container.

With the new deterministic boot flow, the canonical entrypoint is `Core/AppFactory::http()` plus the HTTP runtime in
`Http/`. Legacy builders (`ApplicationBuilder`, `ContainerBootstrap`, `Application`) have been removed in favor of a
single, explicit factory.

### For Humans: What This Means (Represent)

History only: the new startup sequence lives in `Core/AppFactory.php` and `Http/`.

## What Belongs Here

- Application bootstrappers and builders.
- Interfaces for boot integration (environment loader, error handler, bootstrap contracts).
- Kernel/bootstrap orchestration classes.

### For Humans: What This Means (Belongs)

If you’re asking “how does the container get started in my app?”, use `Core/AppFactory::http()` and see
`docs/Core/AppFactory.md` plus `docs/Http/`.

## What Does NOT Belong Here

- Runtime resolution pipeline steps (those are `Core/Kernel`).
- Definition models and builders (those are `Features/Define`).

### For Humans: What This Means (Not Belongs)

Boot is “setup”, not “build services”.

## How Files Collaborate

Boot classes typically coordinate: load config → register service providers → build container → run optional
warmup/compile steps → return an application/container façade ready for runtime.

### For Humans: What This Means (Collaboration)

Legacy checklist documented here; new flow: `AppFactory::http()` builds container, registers providers
deterministically, loads routes, returns `HttpApplication` to run.
