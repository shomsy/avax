# Gate Reality Proof: check-runtime-composition-leaks.php

## Gate Before (Weak)

- 10 broad regex patterns
- No severity classification — all violations equal
- Broad `/Foundation/` exclude — too wide, Foundation can have services
- Broad `$compositionRoots` allowlist — 4 files, no per-line tracking
- No distinction between `class_exists()` (HIGH) and `new SomeFactory` (MEDIUM)
- No `??= new` lazy composition detection

## Gate After (Strong)

### Severity Classification

- **HIGH**: `class_exists`, `interface_exists`, `new Build*`, `->build()`, `?? new`, `??= new`
- **MEDIUM**: `new *Middleware`, `new *Handler`, `new *Dispatcher`, `new *Resolver`, `new *Factory`, `new *Service`,
  `new *Provider`, `new *Engine`, `new *Manager`, `new *Registry`, `new *Repository`, `new *Collector`

### Context Awareness

- Removed broad `/Foundation/` exclude
- Narrowed allowed contexts to: Configuration, Configuration/Builders, ServiceProvider, tests, tooling
- Per-file known allowances with explicit reasons (not broad file excludes)

### Detection Precision

- Constructor default detection: catches `= new Dependency` patterns
- Lazy composition: `??= new`, `?? new`
- Line text capture for debugging
- Comment skipping (no false positives from docblocks)

### Validation

- 0 violations in all Pass 1 changed files
- 150+ pre-existing violations detected in other components (expected, out of scope)
- Known allowances correctly suppress false positives for:
    - App.php: OpenHttpRequestScope/CloseHttpRequestScope (thin wrappers)
    - MatchHttpRoute.php: RouteCollection (value object)
    - HandleIncomingHttp.php: scope objects (from RuntimeInterface)
    - Concurrency.php: StartTask/WaitForTask (stateless Flows)
    - Avax.php: composition root assembly

## Gate Status: OPERATIONAL
