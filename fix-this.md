# fix-this.md — AvaX Enterprise Cleanup: Exact Changes

## How to Use

- Each item is a checkbox. Check it only when the change is made, tested, and committed.
- `[ ]` = not done
- `[x]` = done and validated
- Each item has: file → exact change → validation command

---

## EXECUTION RULES

1. Execute this file **phase by phase, in order**.
2. Do not jump to a later phase while an earlier phase is RED.
3. Do not continue past a RED phase unless the next phase is explicitly required to fix it.
4. After every phase:
    - update evidence files
    - run targeted validation
    - commit only validated phase changes
5. If a phase cannot be made GREEN, stop and return `YELLOW_WITH_EXACT_BLOCKERS`.
6. If any phase remains YELLOW/RED, final status cannot be `FULL_GREEN_READY_FOR_V5_9`.

---

## GLOBAL RULES (apply to every phase)

### GR-1: No Cosmetic GREEN

Do not mark GREEN because:

- files exist
- class exists
- method exists
- gate scanned something
- docs say done
- issue is pre-existing
- issue is out of scope without owner
- tests pass but target proof is missing

GREEN requires ALL of:

- behavior proof
- validation
- gate PASS
- evidence written
- truth reconciled

### GR-2: AI Copy Safety

Any active production code that remains as:

- scaffold
- fake success
- direct runtime construction
- placeholder public API
- empty concrete class
- broad allowlist
- ambiguous status

must be fixed, isolated, or marked non-production.

Active code must be safe for AI to copy as canonical style.

### GR-3: Skipped Work Hard Block

No FULL_GREEN_READY_FOR_V5_9 if:

- skipped-work-ledger has any V5.9 blocker
- any TBD remains in any ledger
- accepted exception has no owner
- accepted exception has no expiry
- governance-gap-report has current-stage blocking gap
- follow-up-work-ledger has blocker before V5.9

### GR-4: Gate Result Semantics

Every gate command result must be one of:

- `PASS`
- `FAIL`
- `NOT_FOUND`
- `UNAVAILABLE_DUE_TO_SANDBOX`
- `UNAVAILABLE_DUE_TO_MISSING_DEPENDENCY`
- `SKIPPED_WITH_REASON`

Rules:

- `NOT_FOUND` is NOT PASS
- `UNAVAILABLE` is NOT PASS
- exit 0 with RED content is NOT PASS
- warning-only gate cannot close a blocker
- mandatory gate must exist and PASS

---

## WORKTREE HYGIENE

Before any phase, verify worktree state:

- [x] **WH-1** Run:
  ```bash
  git status --short > EVIDENCE/cleanup/logs/worktree-pre.txt
  git diff --stat > EVIDENCE/cleanup/logs/worktree-diffstat.txt
  git diff --name-only > EVIDENCE/cleanup/logs/worktree-diff-files.txt
  ```

- [x] **WH-2** Record in `EVIDENCE/cleanup/worktree-hygiene.md`:
    - pre-existing dirty files (with classification)
    - files changed by this pass
    - files that must not be touched (protected)
    - untracked files
    - `.qoder/worktrees/**` status (evidence-only vs active)
    - whether dirty files are: evidence, generated, local tool state, or source

- [x] **WH-3** Rule:
  No commit may include unrelated dirty work unless explicitly classified in `worktree-hygiene.md`.

---

## ACTIVE SCOPE ISOLATION

Before any phase, verify scope boundaries:

- [x] **SI-1** Verify `EVIDENCE/**` is NOT in production autoload:
  ```bash
  grep -r "EVIDENCE" composer.json
  ```

- [x] **SI-2** Verify `recovery-staging/**` is NOT in production autoload:
  ```bash
  ls recovery-staging/ 2>/dev/null && grep -r "recovery-staging" composer.json || echo "No recovery-staging dir"
  ```

- [x] **SI-3** Verify `labs/**` are NOT production claims unless explicitly marked:
  ```bash
  grep -r "labs" composer.json
  ```

- [x] **SI-4** Verify generated/cache files are NOT treated as hand-maintained truth:
  ```bash
  ls var/cache/ 2>/dev/null && echo "WARNING: var/cache/ exists" || true
  ```

- [x] **SI-5** Record scope isolation findings in `EVIDENCE/cleanup/scope-isolation.md`

- [x] **SI-6** Rule: Historical/evidence code may exist, but it must NOT poison active runtime, gates, autoload, or AI
  examples.

---

## Phase 0: Preflight + Baseline Capture

- [x] **0.1** Run full initial validation:
  ```bash
  composer validate --no-check-publish
  composer dump-autoload -o
  vendor/bin/phpunit --no-coverage 2>&1 | tail -5
  vendor/bin/phpstan analyse framework components tests labs/SystemDesignKit --memory-limit=1G --error-format=raw 2>&1 | tail -10
  ```

- [x] **0.2** Record worktree hygiene (see Worktree Hygiene section above)

- [x] **0.3** Record scope isolation (see Active Scope Isolation section above)

- [x] **0.4** Update `EVIDENCE/cleanup/00-cleanup-control-lock.md` with fresh branch, commit, dates

- [x] **0.5** Run all gates:
  ```bash
  for gate in \
    tooling/security/check-security-blockers.php \
    tooling/governance/check-component-adoption.php \
    tooling/refactor/check-component-canonical-shape.php \
    tooling/refactor/check-namespace-drift.php \
    tooling/refactor/check-public-surface.php \
    tooling/refactor/check-runtime-leaks.php \
    tooling/refactor/check-advanced-pattern-folder-violations.php \
    tooling/refactor/check-component-suite-structure.php \
    tooling/refactor/check-duplicate-owners.php \
    tooling/refactor/check-raw-file-operations.php \
    tooling/components/check-component-status-lock-coverage.php \
    tooling/components/check-no-unclassified-scaffolding.php \
    tooling/components/check-hollow-public-surfaces.php \
    tooling/components/check-component-runtime-assembly.php \
    tooling/components/check-component-static-state-safety.php \
    tooling/components/check-component-health-doctor-policy.php \
    tooling/components/check-health-proof-map.php \
    tooling/refactor/check-broken-reference-semantics.php \
    tooling/refactor/check-empty-production-classes.php \
    tooling/governance/check-truth-consistency.php \
    tooling/testing/check-nonzero-target-assertions.php \
  ; do
    status="PASS"
    if [ ! -f "$gate" ]; then
      status="NOT_FOUND"
    else
      php "$gate" 2>&1 || status="FAIL"
    fi
    echo "=== $gate : $status ==="
  done
  ```

- [x] **0.6** Save all gate outputs with result classification to `EVIDENCE/cleanup/logs/`

- [x] **0.7** Update `EVIDENCE/cleanup/01-baseline-validation.md` with fresh output

---

## Phase B: Component Status Lock Rebuild + Folder Structure Cleanup

### B-A: Status Lock

- [x] **B-A.1** Find active components missing from status lock:
  ```bash
  for dir in components/*/*/System; do
    name=$(echo "$dir" | sed 's|components/||; s|/System||')
    grep -q "$name" EVIDENCE/components/component-status-lock.md || echo "MISSING: $name"
  done
  ```

- [x] **B-A.2** Add missing components to `EVIDENCE/components/component-status-lock.md`:
  ```
  | Application/Cache | ACTIVE_GREEN | HEALTH_GREEN | NO |
  ```

- [x] **B-A.3** Verify status lock consistency:
  ```bash
  php tooling/components/check-component-status-lock-coverage.php
  ```

### B-B: Forbidden Folder Names Inside System/

Rule: `how-to-design-components.md` Section 6.7 — `Contracts`, `Events`, `Diagnostics`, `Commands`, `Repositories`,
`ValueObjects`, `Adapters`, `Cqrs` are forbidden as default folders inside `System/`.

**20 violations found:**

- [x] **B-B.01** `components/Application/Container/System/Capabilities/Diagnostics` → rename `Diagnostics` to
  `CheckContainerHealth` or merge into existing HealthCheck capability
- [x] **B-B.02** `components/CLI/Console/System/Capabilities/Commands` → rename `Commands` to `DefineConsoleInput` or
  `RegisterConsoleActions`
- [x] **B-B.03** `components/DataStack/Database/System/Capabilities/Connections/Contracts` → rename `Contracts` to
  `ConnectionInterfaceDefinitions` or `DatabaseConnectorContracts`
- [x] **B-B.04** `components/DataStack/Database/System/Capabilities/Connections/Pools/Contracts` → rename to
  `ConnectionPoolInterfaces`
- [x] **B-B.05** `components/DataStack/Database/System/Capabilities/Connections/ValueObjects` → rename `ValueObjects` to
  `ConnectionConfiguration` or `ConnectionParameters`
- [x] **B-B.06** `components/DataStack/Database/System/Capabilities/ORM/Repositories` → rename `Repositories` to
  `StoreEntityData` or `PersistDomainModels`
- [x] **B-B.07** `components/DataStack/Database/System/Capabilities/Query/ValueObjects` → rename to
  `QueryParameterTypes` or `QueryExpressionTypes`
- [x] **B-B.08** `components/DataStack/Database/System/Capabilities/Telemetry/Events` → rename `Events` to
  `EmitQueryTelemetryEvents` or `DispatchQueryLifecycle`
- [x] **B-B.09** `components/DataStack/Database/System/Capabilities/Telemetry/Events/Contracts` → rename to
  `QueryTelemetryContracts`
- [x] **B-B.10** `components/DataStack/Database/System/Capabilities/Transactions/Contracts` → rename to
  `TransactionContractDefinitions`
- [x] **B-B.11** `components/DataStack/Database/System/Foundation/Lifecycle/Events` → rename to
  `Foundation/Lifecycle/LifecycleNotifications` or `Foundation/Lifecycle/EmitLifetimeHooks`
- [x] **B-B.12** `components/DataStack/Persistence/System/Capabilities/Diagnostics` → rename to `CheckPersistenceHealth`
  or `DiagnosePersistenceConfiguration`
- [x] **B-B.13** `components/DataStack/Persistence/System/Capabilities/Repositories` → rename to `StorePersistentData`
  or `PersistentEntityStorage`
- [x] **B-B.14** `components/DeveloperTools/Dx/System/Capabilities/Commands` → rename to `RegisterDeveloperCommands` or
  `DefineCliActions`
- [x] **B-B.15** `components/HTTP/Session/System/Capabilities/Events` → rename to `EmitSessionLifecycleNotifications` or
  `DispatchSessionEvents`
- [x] **B-B.16** `components/Identity/Auth/System/Capabilities/Diagnostics` → rename to `CheckAuthConfiguration` or
  `DiagnoseAuthState`
- [x] **B-B.17** `components/Operations/Filesystem/System/Capabilities/Adapters` → rename to `StorageBackends` or
  `FilesystemDrivers`
- [x] **B-B.18** `components/Security/Cryptography/System/PublicSurface/Contracts` → move `StringEncrypterInterface.php`
  up to `PublicSurface/` directly, remove `Contracts/` folder
- [x] **B-B.19** `components/SystemDesign/System/Capabilities/Messaging/Cqrs` → rename `Cqrs` to
  `CommandQuerySeparation` or `HandleCommandQuery`

### B-C: Component Names on Forbidden List

Rule: `AGENTS.md` Section 8 — if a word from the forbidden list is truly domain language, it must be explicitly
justified via governance exception.

- [x] **B-C.1** `components/API/Contracts` — "Contracts" is forbidden. Either rename to `API/ApiContractManagement` or
  create governance exception in `.agents/GOVERNANCE_EXCEPTIONS.md`
- [x] **B-C.2** `components/DeveloperTools/Diagnostics` — "Diagnostics" is forbidden. Either rename to
  `DeveloperTools/HealthCheck` or create governance exception
- [x] **B-C.3** `components/Operations/Events` — "Events" is forbidden. Either rename to `Operations/EventDispatch` or
  create governance exception
- [x] **B-C.4** `components/Identity/Auth/docs/` — "Docs" is forbidden inside component. Move documentation to
  project-level `docs/`
- [x] **B-C.5** `components/Identity/Auth/tests/` — "Tests" is forbidden inside component. Move tests to central
  `tests/` tree

### B-D: Files Outside System/

Rule: `AGENTS.md` Section 7 — all production code must live inside `System/`.

- [x] **B-D.01** `components/Application/Cache/Cache.php` → move to `System/PublicSurface/Cache.php`
- [x] **B-D.02** `components/Application/Cache/CompiledCache.php` → move to `System/PublicSurface/CompiledCache.php`
- [x] **B-D.03** `components/Application/Config/AuthConfig.php` → move to `System/Configuration/AuthConfig.php`
- [x] **B-D.04** `components/Application/Config/functions.php` → move to `System/Foundation/functions.php`
- [x] **B-D.05** `components/Application/Text/functions.php` → move to `System/Foundation/functions.php`
- [x] **B-D.06** `components/DataStack/Persistence/AccessPersistentData.php` → move to
  `System/Flows/AccessPersistentData/AccessPersistentData.php`
- [x] **B-D.07** `components/DataStack/Persistence/CommitDataChanges.php` → move to
  `System/Flows/CommitDataChanges/CommitDataChanges.php`
- [x] **B-D.08** `components/DataStack/Persistence/DataLayer.php` → move to `System/PublicSurface/DataLayer.php`
- [x] **B-D.09** `components/DataStack/Persistence/DataLayerConfig.php` → move to
  `System/Configuration/DataLayerConfig.php`
- [x] **B-D.10** `components/DataStack/Persistence/RegisterDataLayerRuntime.php` → move to
  `System/Configuration/RegisterDataLayerRuntime.php`
- [x] **B-D.11** `components/HTTP/Middleware/` (8 files: IpRestrictionMiddleware, MiddlewareInterface,
  MiddlewareRegistry, RateLimiterInterface, RateLimiterMiddleware, RequestHandlerInterface, RequestLoggerMiddleware,
  SessionLifecycleMiddleware) → move each to `System/Capabilities/<CapabilityName>/`
- [x] **B-D.12** `components/HTTP/Response/ResponseFactory.php` → move to
  `System/Capabilities/ResponseBuilding/ResponseFactory.php`
- [x] **B-D.13** `components/HTTP/Client/shortcuts.php`, `components/HTTP/Context/shortcuts.php`,
  `components/HTTP/Security/shortcuts.php`, `components/Operations/Logging/shortcuts.php` → move each to
  `System/PublicSurface/shortcuts.php`
- [x] **B-D.14** `components/HTTP/Session/NullSession.php` → move to
  `System/Capabilities/SessionStorage/NullSession.php`
- [x] **B-D.15** `components/Identity/Auth/phpVersion.php`, `components/Identity/Auth/rector.php` → move to project root
  or `tooling/`
- [x] **B-D.16** `components/Presentation/View/BladeTemplateEngine.php`, `TemplateEngine.php` → move to
  `System/Capabilities/BladeRendering/`
- [x] **B-D.17** `components/HTTP/Configuration.php` → move to `components/HTTP/System/Configuration/Configuration.php`
- [x] **B-D.18** `components/compat.php` → evaluate necessity, move to `framework/System/Foundation/compat.php` or
  remove

### B-E: Subdirectories Outside System/ With PHP Content

- [x] **B-E.1** `components/Application/Cache/Examples/`, `examples/`, `Providers/` → move content into `System/`
- [x] **B-E.2** `components/Application/Config/Configurator/` → move into `System/Configuration/`
- [x] **B-E.3** `components/Application/Container/tools/` → move into `System/Foundation/tools/` or remove
- [x] **B-E.4** `components/Application/Filesystem/Configuration/` → move into `System/Configuration/`
- [x] **B-E.5** `components/DataStack/Persistence/AccessPersistentData/`, `CommitDataChanges/`, `ConfigureDataLayer/` →
  move each into `System/Flows/`
- [x] **B-E.6** `components/DeveloperTools/Documentation/Api/` → move into `System/Capabilities/`
- [x] **B-E.7** `components/HTTP/Request/ServerRequest/` → move into `System/Capabilities/`
- [x] **B-E.8** `components/Identity/Auth/Integrations/` → move into `System/Capabilities/Integrations/`
- [x] **B-E.9** `components/Identity/Auth/examples/` → move to project-level `examples/`
- [x] **B-E.10** `components/Identity/Auth/tests/` → move to central `tests/` tree

### B-F: framework/Foundation/ Outside System/

- [x] **B-F.1** `framework/Foundation/Exception/NotImplementedException.php` → move to
  `framework/System/Foundation/Exception/NotImplementedException.php`

### B-G: Validate

- [x] **B-G.1** Verify all moves preserved autoload integrity:
  ```bash
  composer dump-autoload -o
  ```
- [x] **B-G.2** Verify all moves pass PHPStan:
  ```bash
  vendor/bin/phpstan analyse framework components --memory-limit=1G --error-format=raw
  ```
- [x] **B-G.3** Verify advanced folder gate passes:
  ```bash
  php tooling/refactor/check-advanced-pattern-folder-violations.php
  php tooling/refactor/check-component-canonical-shape.php
  php tooling/refactor/check-namespace-drift.php
  ```

- [x] **B-G.4** Commit:
  ```bash
  git add -A && git commit -m "cleanup: phase B — status lock rebuild, forbidden folder renames, files moved into System/ hierarchy"
  ```

---

## Phase C: DI / Runtime Assembly Cleanup

### C-A: BLOCKER Violations — `new ResponseFactory`, `new Filesystem`, `new SystemClock`

These are the most dangerous: infrastructure dependencies instantiated inline in runtime execution paths.

**`new ResponseFactory()` violations:**

- [x] **C-A.01** `framework/System/Flows/HandleIncomingHttp/ConfiguredRoutesHttpHandler.php:87` —
  `responseFactory: new ResponseFactory()`. Fix: inject `ResponseFactory` via constructor, remove static `fromCache()`
  factory.
- [x] **C-A.02** `framework/System/Flows/HandleIncomingHttp/ConfiguredRoutesHttpHandler.php:98` —
  `responseFactory: new ResponseFactory()`. Fix: same — remove `fromRouteDefinitions()` static factory or make it
  delegate to DI-resolved builder.
- [x] **C-A.03** `framework/System/Capabilities/Runtime/Worker/WorkerLoop.php:78` —
  `responseFactory: new ResponseFactory()`. Fix: inject `ResponseFactory` into `WorkerLoop` constructor.
- [x] **C-A.04** `framework/System/PublicSurface/App.php:316` — `new ResponseFactory()`. Fix: inject via constructor.
- [x] **C-A.05** `framework/System/PublicSurface/Avax.php:81` — `new ResponseFactory()`. Fix: inject via constructor.

**`new Filesystem()` violations:**

- [x] **C-A.06** `framework/System/Capabilities/Routing/RegisterRouteCommands.php:40` —
  `new CacheRouteTable(new Filesystem())`. Fix: inject `Filesystem` through closure's `use` or register proper command
  classes.
- [x] **C-A.07** `framework/System/Capabilities/Routing/RegisterRouteCommands.php:60` —
  `new LoadCachedRoutes(new Filesystem())`. Fix: same pattern.
- [x] **C-A.08** `components/DeveloperTools/CodeGeneration/System/Flows/GenerateCode/GenerateCode.php:22` —
  `$fs = $filesystem ?? new Filesystem()`. Fix: make `Filesystem` required constructor parameter.
- [x] **C-A.09**
  `components/DeveloperTools/CodeGeneration/System/Capabilities/Generators/CodeGenerator.php:38,86,107,120` —
  `$this->filesystem ?? new Filesystem()` (4x). Fix: make `Filesystem` required constructor parameter, remove all 4
  fallbacks.
- [x] **C-A.10** `components/SystemDesign/System/Capabilities/SchemaValidation/NativeYamlParser.php:42` —
  `$fs = $this->filesystem ?? new Filesystem()`. Fix: make `Filesystem` required constructor parameter.
- [x] **C-A.11** `components/DataStack/Database/System/PublicSurface/Migrations.php:152` (verifikovati) —
  `new Filesystem()`. Fix: inject through constructor.

**`new SystemClock()` violations:**

- [x] **C-A.12** `framework/System/Flows/CreateApplication/CreateApplication.php:42` — `$clock = new SystemClock()`.
  Fix: inject `Clock` through constructor.
- [x] **C-A.13**
  `components/Application/Cache/System/Capabilities/Lifecycle/ReplaceCachedValues/LeastFrequentlyUsedReplacement/LeastFrequentlyUsedReplacement.php:20` —
  `$clock ?? new SystemClock()`. Fix: make `Clock` required constructor parameter.
- [x] **C-A.14** `components/Application/Cache/System/Capabilities/Compilation/CompiledCacheFreshness.php:33` —
  `$clock ?? new SystemClock()`. Fix: make `Clock` required.
- [x] **C-A.15**
  `components/Application/Cache/System/Capabilities/Distribution/UseCacheTiers/L2DistributedCache.php:29` —
  `$clock ?? new SystemClock()`. Fix: make `Clock` required.
- [x] **C-A.16**
  `components/Application/Cache/System/Capabilities/CompiledCache/DistributedCompiledCache/CompiledCacheManifest.php:38,48` —
  `$clock ?? new SystemClock()` (2x). Fix: make `Clock` required.
- [x] **C-A.17**
  `components/Application/Cache/System/Capabilities/Storage/StoreCachedValues/CacheHealthDetector.php:213` —
  `$clock ?? new SystemClock()`. Fix: make `Clock` required.
- [x] **C-A.18** `components/Application/Cache/System/Capabilities/Storage/StoreCachedValues/L1MemoryCache.php:26` —
  `$clock ?? new SystemClock()`. Fix: make `Clock` required.

### C-B: framework/System/Flows/ — Direct `new` With Default Constructor Values

Rule: `how-to-dependency-injection.md` Section 5.1 — `= new ClassName()` as a constructor default is the
`?? new Fallback` anti-pattern. Dependencies MUST be required constructor parameters.

- [x] **C-B.01** `framework/System/Flows/HandleRuntimeFailure/HandleRuntimeFailure.php:17` —
  `private ConvertPhpErrorToThrowable $convertPhpErrorToThrowable = new ConvertPhpErrorToThrowable()`. Fix: make
  required constructor parameter.
- [x] **C-B.02** `framework/System/Flows/HandleRuntimeFailure/HandleRuntimeFailure.php:18` —
  `private ReportRuntimeFailure $reportRuntimeFailure = new ReportRuntimeFailure()`. Fix: required.
- [x] **C-B.03** `framework/System/Flows/HandleRuntimeFailure/HandleRuntimeFailure.php:19` —
  `private RenderRuntimeFailure $renderRuntimeFailure = new RenderRuntimeFailure()`. Fix: required.
- [x] **C-B.04** `framework/System/Flows/ValidateConfig/ValidateConfig.php:14` —
  `private ConfigValidator $configValidator = new ConfigValidator()`. Fix: required.
- [x] **C-B.05** `framework/System/Flows/CheckRuntimeIsolation/CheckRuntimeIsolation.php:12` —
  `private RuntimeIsolationGuard $runtimeIsolationGuard = new RuntimeIsolationGuard()`. Fix: required.
- [x] **C-B.06** `framework/System/Flows/DiscoverComponents/DiscoverComponents.php:12` —
  `private ComponentDiscovery $componentDiscovery = new ComponentDiscovery()`. Fix: required.
- [x] **C-B.07** `framework/System/Flows/ListComponents/ListComponents.php:13` —
  `private ComponentDiscovery $componentDiscovery = new ComponentDiscovery()`. Fix: required.
- [x] **C-B.08** `framework/System/Flows/ExplainConfig/ExplainConfig.php:13` —
  `private ConfigExplainer $configExplainer = new ConfigExplainer()`. Fix: required.
- [x] **C-B.09** `framework/System/Flows/ListRoutes/ListRoutes.php:13` —
  `private RouteAnalyzer $routeAnalyzer = new RouteAnalyzer()`. Fix: required.
- [x] **C-B.10** `framework/System/Flows/ExplainRouteMatch/ExplainRouteMatch.php:13` —
  `private RouteAnalyzer $routeAnalyzer = new RouteAnalyzer()`. Fix: required.
- [x] **C-B.11** `framework/System/Flows/DetectRouteConflict/DetectRouteConflict.php:13` —
  `private RouteAnalyzer $routeAnalyzer = new RouteAnalyzer()`. Fix: required.
- [x] **C-B.12** `framework/System/Flows/BootApplication/BootApplication.php:14` —
  `private BuildApplicationState $buildApplicationState = new BuildApplicationState()`. Fix: required.
- [x] **C-B.13**
  `framework/System/Capabilities/FailureBoundary/Capabilities/CleanupAfterFailure/CleanupAfterFailure.php:24` —
  `private FailureCleanupRegistry $registry = new FailureCleanupRegistry()`. Fix: required.
- [x] **C-B.14** `framework/System/Capabilities/FailureBoundary/Flows/RunProtectedAction/RunProtectedAction.php:26` —
  `private EnforceTimeout $enforceTimeout = new EnforceTimeout()`. Fix: required.

### C-C: framework/System/Flows/ — Composition Root in Flow (Architecture Violation)

Rule: `how-to-dependency-injection.md` Section 3.4 — composition belongs in ServiceProvider/Configuration/Build*, not in
Flows. These flows act as hidden composition roots.

- [x] **C-C.01** `framework/System/Flows/CreateApplication/CreateApplication.php` — 11 `new` calls constructing the
  entire application (ProjectPath, SystemClock, ComponentRegistry, RequestScopeStore, RuntimeContext,
  StateResetRegistry, RuntimeState, Runtime, App, ResetApplicationState). Fix: extract into `BuildApplication` builder
  or Configuration flow.
- [x] **C-C.02** `framework/System/Flows/BootApplication/BuildApplicationState.php` — 6 `new` calls (ComponentRegistry,
  RequestScopeStore, RuntimeContext, StateResetRegistry, RuntimeState, Runtime). Fix: inject these as dependencies or
  use a builder.
- [x] **C-C.03** `framework/System/Flows/HandleIncomingHttp/ConfiguredRoutesHttpHandler.php:35-46` — 8 `new` calls (
  RouteFacadeContainer, ResolveCallable, ControllerResolver, ArgumentResolver, ReadIncomingHttpRequest, MatchHttpRoute,
  RunHttpRoute). Fix: inject all via constructor.
- [x] **C-C.04** `framework/System/Flows/RunApplication/RunApplication.php:60-66` — 5 `new` calls (
  ReadIncomingHttpRequest, MatchHttpRoute, RouteFacadeContainer, ControllerResolver, ArgumentResolver). Fix: inject all
  via constructor.
- [x] **C-C.05** `framework/System/Flows/HandleIncomingHttp/HandleIncomingHttp.php:34-38` — 2 `new` calls (
  OpenHttpRequestScope, CloseHttpRequestScope). Fix: inject via constructor.
- [x] **C-C.06** `framework/System/Flows/RunConsoleCommand/RunConsoleCommand.php:152-155` — 2 `new` calls (
  PreCommitConfig, PreCommit). Fix: inject `PreCommit` as constructor dependency.
- [x] **C-C.07** `framework/System/Flows/ExplainContainerResolution/ExplainContainerResolution.php:39-59` — 3
  `new ContainerAnalyzer(...)`. Fix: inject `ContainerAnalyzer` in constructor.
- [x] **C-C.08** `framework/System/Flows/ExplainContainerService/ExplainContainerService.php:39-59` — 3
  `new ContainerAnalyzer(...)`. Fix: inject in constructor.
- [x] **C-C.09** `framework/System/Flows/AuditContainerScope/AuditContainerScope.php:48` — 1
  `new ContainerAnalyzer(...)`. Fix: inject in constructor.

### C-D: framework/System/Capabilities/ — Direct `new` in Closures/Commands

- [x] **C-D.01** `framework/System/Capabilities/Configuration/RegisterConfigCommands.php` — 6 `new` calls inside runtime
  closures: `(new LoadApplicationConfiguration())->load()`, `(new LoadRuntimeConfiguration())->load()`,
  `(new ValidateApplicationConfiguration())`, `(new ValidateRuntimeConfiguration())`. Fix: register proper command
  classes with injected dependencies.
- [x] **C-D.02** `framework/System/Capabilities/MetadataWarmup/RegisterMetadataWarmCommands.php:54` —
  `$compiler = new CompileClassAttributes(...)` inside runtime closure. Fix: inject via constructor.
- [x] **C-D.03** `framework/System/Capabilities/PreCommit/PreCommit.php:56` —
  `$this->preCommitConfig = $preCommitConfig ?? new PreCommitConfig()`. Fix: required constructor parameter.
- [x] **C-D.04** `framework/System/Capabilities/TracingTimeline/Tracing.php:16` —
  `$this->runtimeTimeline = new RuntimeTimeline()`. Fix: inject via constructor.
- [x] **C-D.05** `framework/System/Capabilities/Runtime/Worker/WorkerLoop.php:77` —
  `$handleIncomingHttp = new HandleIncomingHttp(...)`. Fix: inject `HandleIncomingHttp` in constructor.

### C-E: components/*/System/PublicSurface/ — Static Facades Instantiating Services

Rule: `how-to-dependency-injection.md` Section 3.4 — `new Class()` in PublicSurface is FORBIDDEN. These are the worst
pattern: every method creates new service instances.

- [x] **C-E.01** `components/API/GraphQL/System/PublicSurface/GraphQL.php` — 6 static methods, all `new Service()`. Fix:
  convert to instance class with constructor DI. Inject `BuildGraphQLSchema`, `GraphQLExecutor`,
  `ValidateGraphQLOperation`, `GraphQLConfiguration`, `DataLoader` via constructor.
- [x] **C-E.02** `components/API/OpenAPI/System/PublicSurface/OpenAPI.php` — 5 static methods, all `new Service()`. Fix:
  inject `ExportOpenApiDocument`, `BuildOpenApiDocument`, `ValidateOpenApiDocument`, `CompareOpenApiDocuments`,
  `RenderOpenApiJson`, `RenderOpenApiYaml`, `OpenApiConfiguration` via constructor.
- [x] **C-E.03** `components/API/Contracts/System/PublicSurface/ApiContracts.php` — 6 static methods, all
  `new Service()`. Fix: inject `EndpointRegistry`, `DeprecationTracker`, `DetectBreakingChange`, `CompatibilityChecker`,
  `ValidateApiContract`, `RegisterApiVersion`, `ApiContractsConfiguration` via constructor.
- [x] **C-E.04** `components/Operations/Tasks/System/PublicSurface/Tasks.php` — 7 static methods, all `new Service()`.
  Fix: inject `TaskRunner`, `TaskQueue`, `TaskScheduler`, `ExecuteTask`, `ScheduleTask`, `RetryTask`, `CancelTask`,
  `TaskRetryPolicy` via constructor.
- [x] **C-E.05** `components/Application/Storage/System/PublicSurface/Storage.php` — 8 methods with `new Flow()`. Fix:
  inject `WriteStoredObject`, `ReadStoredObject`, `CheckStoredObject`, `DeleteStoredObject`, `CopyStoredObject`,
  `MoveStoredObject`, `GenerateStoredObjectUrl`, `GenerateTemporaryStoredObjectUrl`, `RegisteredDisks` via constructor.
- [x] **C-E.06** `components/DataStack/Database/System/PublicSurface/Database.php` — 7 `new` in class body or methods.
  Fix: inject `Query`, `Entities`, `Schema`, `Migrations`, `Transactions`, `Telemetry`, `DatabaseBuilder` via
  constructor.
- [x] **C-E.07** `components/DataStack/Database/System/PublicSurface/EntityManager.php:27-35` — 5 `new` (
  AttributeMetadataReader, IdentityMap, Hydrator, EntityPersister, OrmEntityManager). Fix: inject `OrmEntityManager` via
  constructor.
- [x] **C-E.08** `components/Security/Redaction/System/PublicSurface/Redaction.php` — 8 static methods, all
  `new Service()`. Fix: inject `PolicyEngine`, `DataClassifier`, `PatternMatcher`, `RedactionEngine`, `RedactLogData`,
  `ApplyRedactionPolicy`, `ClassifySensitiveData` via constructor.
- [x] **C-E.09** `components/Operations/Events/System/PublicSurface/Events.php:31-32` — `new ListenerRegistry()`,
  `new EventDispatcher()`. Fix: inject via constructor.
- [x] **C-E.10** `components/Operations/MessageBus/System/PublicSurface/MessageBus.php:32-55` — `new CommandBus()`,
  `new QueryBus()`, `new EventBus()`. Fix: inject via constructor.
- [x] **C-E.11** `components/Operations/Concurrency/System/PublicSurface/Concurrency.php` — 4 `new Flow()`. Fix: inject
  `RunConcurrentTasks`, `RaceTasks`, `StartTask`, `WaitForTask` via constructor.
- [x] **C-E.12** `components/Operations/Resilience/System/PublicSurface/Resilience.php:15,20` — `new RetryBuilder()`,
  `new CircuitBreaker()`. Fix: inject via constructor.
- [x] **C-E.13** `components/Operations/Scheduler/System/PublicSurface/Scheduler.php:19-45` — `new ScheduledTask()`,
  `new TaskRunner()`. Fix: inject via constructor.
- [x] **C-E.14** `components/HTTP/SecureRequest/System/PublicSurface/SecureRequest.php:80-102` —
  `new CreateDataObject()`, `new DataTransferViolations()`, `new ValidationContext()`. Fix: inject `CreateDataObject`
  via constructor.
- [x] **C-E.15** `components/Foundation/CallableSerialization/System/PublicSurface/CallableSerialization.php:28-64` — 3
  `new BuildCallableSerialization()`. Fix: inject via constructor.
- [x] **C-E.16** `components/HTTP/AfterResponse/System/PublicSurface/AfterResponse.php:17,23` —
  `new AfterResponseTask()`, `new AfterResponseQueue()`. Fix: inject via constructor.
- [x] **C-E.17** `components/Application/FeatureFlags/System/PublicSurface/FeatureFlags.php:26` —
  `new InMemoryFlagStore()`. Fix: inject via static setter only, no fallback in PublicSurface.
- [x] **C-E.18** `components/Application/Pipeline/System/PublicSurface/Pipeline.php:21` — `new HookRegistry()`. Fix:
  inject via static setter.
- [x] **C-E.19** `components/Operations/Mail/System/PublicSurface/Mailer.php:15,31` — `new Envelope(...)` as default,
  `new RawMailBuilder(...)`. Fix: remove default `new`, inject via constructor.
- [x] **C-E.20** `framework/System/PublicSurface/App.php` — 15+ `new` calls constructing entire application. Fix:
  refactor into `Configuration/BuildApp.php` or inject all via constructor.
- [x] **C-E.21** `framework/System/PublicSurface/Avax.php:74-94` — 8 `new` calls (BootApplication,
  BuildApplicationState, HttpKernel, HandleIncomingHttp, ResponseFactory, ConsoleKernel, RunConsoleCommand,
  RuntimeKernel, ResetApplicationState). Fix: inject via constructor.

### C-F: components/*/System/Flows/ and Capabilities/ — `?? new` Fallback Pattern

- [x] **C-F.01** `components/Application/Cache/System/Flows/Lifecycle/RememberCachedValue/RememberCachedValue.php:31` —
  `$this->readCachedValue ?? new ReadCachedValue(...)`. Fix: make `ReadCachedValue` required constructor parameter.
- [x] **C-F.02** `components/Application/Cache/System/Flows/Lifecycle/RememberCachedValue/RememberCachedValue.php:32` —
  `$this->storeCachedValue ?? new StoreCachedValue(...)`. Fix: make `StoreCachedValue` required.
- [x] **C-F.03** `components/Application/Cache/System/Capabilities/Storage/StoreCachedValues/FileCacheStore.php:36` —
  `$this->jsonCacheSerializer = $jsonCacheSerializer ?? new JsonCacheSerializer(...)`. Fix: make required.
- [x] **C-F.04** `components/DataStack/Persistence/System/Flows/ExplainDataQuery/ExplainDataQuery.php:25` —
  `$this->compileDataQuery = $compileDataQuery ?? new CompileDataQuery()`. Fix: make required.
- [x] **C-F.05** `components/SystemDesign/System/Flows/ValidateCapacitySchema/ValidateCapacitySchema.php:22` —
  `$this->validator = $validator ?? new SchemaValidator()`. Fix: make required.
- [x] **C-F.06** `components/SystemDesign/System/Flows/ValidateCapacityModel/ValidateCapacityModel.php:25` —
  `$this->schemaValidator = $schemaValidator ?? new SchemaValidator()`. Fix: make required.
- [x] **C-F.07** `components/SystemDesign/System/Flows/ValidateCapacityModel/ValidateCapacityModel.php:27` —
  `$this->yamlParser = $yamlParser ?? new NativeYamlParser()`. Fix: make required.
- [x] **C-F.08** `components/SystemDesign/System/Flows/RunArchitectureTests/RunArchitectureTests.php:23` —
  `$this->yamlParser = $yamlParser ?? new NativeYamlParser()`. Fix: make required.
- [x] **C-F.09** `components/SystemDesign/System/Flows/RunScenarios/RunScenarios.php:22` —
  `$this->yamlParser = $yamlParser ?? new NativeYamlParser()`. Fix: make required.
- [x] **C-F.10** `components/SystemDesign/System/Flows/ValidateScenariosSchema/ValidateScenariosSchema.php:22` —
  `$this->validator = $validator ?? new SchemaValidator()`. Fix: make required.
- [x] **C-F.11**
  `components/SystemDesign/System/Flows/ValidateArchitectureTestsSchema/ValidateArchitectureTestsSchema.php:22` —
  `$this->validator = $validator ?? new SchemaValidator()`. Fix: make required.
- [x] **C-F.12** `components/SystemDesign/System/Capabilities/SchemaValidation/SchemaValidator.php:24` —
  `$this->yamlParser = $yamlParser ?? new NativeYamlParser()`. Fix: make required.
- [x] **C-F.13** `components/HTTP/Session/System/PublicSurface/Session.php:225` —
  `return $this->sessionEventBus ?? new SessionEventBus()`. Fix: make `SessionEventBus` required constructor parameter.
- [x] **C-F.14** `components/DataStack/Database/System/Capabilities/Transactions/DeadlockDetector.php:115` —
  `$this->deadlockDetectorConfig = $deadlockDetectorConfig ?? new DeadlockDetectorConfig()`. Fix: make required.
- [x] **C-F.15** `components/DataStack/Database/System/Capabilities/Query/IR/IRBuilder.php:19` —
  `$this->queryNode = $queryNode ?? new QueryNode()`. Fix: make required.
- [x] **C-F.16** `components/DataStack/Database/System/Capabilities/Query/Execution/QueryOrchestrator.php:258,287` —
  `$resolver = new ResolveCallable()` (2x). Fix: inject `ResolveCallable` in constructor.
- [x] **C-F.17** `components/DataStack/Database/System/Capabilities/ORM/Persisters/EntityPersister.php:592` —
  `$resolver = new ResolveCallable()`. Fix: inject in constructor.
- [x] **C-F.18** `components/DataStack/Database/System/Capabilities/Transactions/RunTransaction/Transaction.php:503` —
  `$resolver = new ResolveCallable()`. Fix: inject in constructor.
- [x] **C-F.19** `components/Operations/Queue/System/Capabilities/Queue/State/QueueState.php:84` —
  `return $this->failedJobsStore ??= new InMemoryFailedJobsStore()`. Fix: inject `FailedJobsStore` via constructor.

### C-G: AuthBuilder — 32 `?? new` Fallbacks (Largest Single Concentration)

File: `components/Identity/Auth/System/Configuration/AuthBuilder.php`

- [x] **C-G.01** `AuthBuilder.php:665` — `$passwordHasher = $this->passwordHasher ?? new PasswordHasher()`. Fix:
  required parameter.
- [x] **C-G.02** `AuthBuilder.php:666` — `$auditLog = $this->auditLog ?? new NullAuditLog()`. Fix: required.
- [x] **C-G.03** `AuthBuilder.php:675` — `$clock = $this->clock ?? new Clock()`. Fix: required.
- [x] **C-G.04** `AuthBuilder.php:676` —
  `$oAuthClientRegistry = $this->oAuthClientRegistry ?? new InMemoryOAuthClientRegistry(...)`. Fix: required.
- [x] **C-G.05** `AuthBuilder.php:677` —
  `$authorizationCodeStore = $this->authorizationCodeStore ?? new InMemoryAuthorizationCodeStore()`. Fix: required.
- [x] **C-G.06** `AuthBuilder.php:678` — `$lifecycleStore = $this->lifecycleStore ?? new InMemoryLifecycleStore()`. Fix:
  required.
- [x] **C-G.07** `AuthBuilder.php:679` —
  `$adminElevationStore = $this->adminElevationStore ?? new InMemoryAdminElevationStore()`. Fix: required.
- [x] **C-G.08** `AuthBuilder.php:680-684` —
  `$riskEngine = $this->deterministicRiskEngine ?? new DeterministicRiskEngine(...)`. Fix: required.
- [x] **C-G.09** `AuthBuilder.php:685` —
  `$passkeyCredentialStore = $this->passkeyCredentialStore ?? new InMemoryPasskeyCredentialStore()`. Fix: required.
- [x] **C-G.10** `AuthBuilder.php:686` —
  `$passkeyChallengeStore = $this->passkeyChallengeStore ?? new InMemoryPasskeyChallengeStore()`. Fix: required.
- [x] **C-G.11** `AuthBuilder.php:687` —
  `$federationConnectionStore = $this->federationConnectionStore ?? new InMemoryFederationConnectionStore()`. Fix:
  required.
- [x] **C-G.12** `AuthBuilder.php:688` —
  `$federatedIdentityLinkStore = $this->federatedIdentityLinkStore ?? new InMemoryFederatedIdentityLinkStore()`. Fix:
  required.
- [x] **C-G.13** `AuthBuilder.php:690` —
  `$passwordResetStore = $this->passwordResetStore ?? new InMemoryPasswordResetStore()`. Fix: required.
- [x] **C-G.14** `AuthBuilder.php:691` —
  `$emailVerificationStore = $this->emailVerificationStore ?? new InMemoryEmailVerificationStore()`. Fix: required.
- [x] **C-G.15** `AuthBuilder.php:692` —
  `$emailChangeStore = $this->emailChangeStore ?? new InMemoryEmailChangeStore()`. Fix: required.
- [x] **C-G.16** `AuthBuilder.php:693` —
  `$emailVerificationState = $this->emailVerificationStateStore ?? new InMemoryEmailVerificationStateStore()`. Fix:
  required.
- [x] **C-G.17** `AuthBuilder.php:694` — `$mfaStore = $this->mfaStore ?? new InMemoryMfaStore()`. Fix: required.
- [x] **C-G.18** `AuthBuilder.php:695` —
  `$mfaChallengeStore = $this->mfaChallengeStore ?? new InMemoryMfaChallengeStore()`. Fix: required.
- [x] **C-G.19** `AuthBuilder.php:696` — `$totp = $this->totp ?? new Totp()`. Fix: required.
- [x] **C-G.20** `AuthBuilder.php:697-700` — `$mfaAttemptLimit = $this->limitMfaAttempts ?? new LimitMfaAttempts(...)`.
  Fix: required.
- [x] **C-G.21** `AuthBuilder.php:701-706` —
  `$passwordResetThrottle = $this->passwordResetThrottle ?? new AttemptThrottle(...)`. Fix: required.
- [x] **C-G.22** `AuthBuilder.php:707-712` —
  `$mfaRecoveryThrottle = $this->mfaRecoveryThrottle ?? new AttemptThrottle(...)`. Fix: required.
- [x] **C-G.23** `AuthBuilder.php:713-718` — `$scimThrottle = $this->scimThrottle ?? new AttemptThrottle(...)`. Fix:
  required.
- [x] **C-G.24** `AuthBuilder.php:802` — `$this->oidcRequestObjectStore ?? new InMemoryOidcRequestObjectStore()`. Fix:
  required.
- [x] **C-G.25** `AuthBuilder.php:902` —
  `$scimDirectoryStore = $this->scimDirectoryStore ?? new InMemoryScimDirectoryStore(...)`. Fix: required.
- [x] **C-G.26** `AuthBuilder.php:903` —
  `$scimProvisionedIdentityStore = $this->scimProvisionedIdentityStore ?? new InMemoryScimProvisionedIdentityStore()`.
  Fix: required.
- [x] **C-G.27** `AuthBuilder.php:904` — `$tenantStore = $this->tenantStore ?? new InMemoryTenantStore()`. Fix:
  required.
- [x] **C-G.28** `AuthBuilder.php:905` —
  `$tenantSecurityConfigurationStore = $this->tenantSecurityConfigurationStore ?? new InMemoryTenantSecurityConfigurationStore()`.
  Fix: required.
- [x] **C-G.29** `AuthBuilder.php:906` —
  `$tenantSecurityChangeRequestStore = $this->tenantSecurityChangeRequestStore ?? new InMemoryTenantSecurityChangeRequestStore()`.
  Fix: required.
- [x] **C-G.30** `AuthBuilder.php:979,1119,1461` — `idGenerator: $this->idGenerator ?? new IdGenerator()` (3x). Fix:
  make `IdGenerator` required.

### C-H: HttpClientProvider `?? new`

- [x] **C-H.01** `components/HTTP/Client/System/Configuration/HttpClientProvider.php:171` —
  `$resolvedTransport = $httpTransport ?? new CurlTransport()`. Fix: make `HttpTransportInterface` required parameter.

### C-I: Validate

- [x] **C-I.1** Run DI assembly gate:
  ```bash
  php tooling/components/check-component-runtime-assembly.php
  ```
- [x] **C-I.2** Run PHPStan:
  ```bash
  vendor/bin/phpstan analyse --memory-limit=1G --level=max
  ```
- [x] **C-I.3** Run PHPUnit:
  ```bash
  vendor/bin/phpunit --no-coverage
  ```
- [x] **C-I.4** Update `EVIDENCE/cleanup/04-di-runtime-assembly-cleanup.md` with results
- [x] **C-I.5** Update `skipped-work-ledger.md`: mark SW-0005, SW-0006, SW-0008, SW-0012 as FIXED_NOW
- [x] **C-I.6** Commit:
  ```bash
  git add -A && git commit -m "cleanup: phase C — remove 190+ runtime infrastructure new-construction leaks, fix ?? new fallbacks, convert static facades to DI"
  ```

---

## ServiceProvider / Assembly Map

After DI cleanup, map every ACTIVE_GREEN core component assembly:

- [x] **AM-1** For each component, answer:
    - ServiceProvider exists?
    - Configuration/Build* assembly exists?
    - Container bindings exist?
    - PublicSurface assembly path exists?
    - Does runtime execution instantiate dependencies manually?

- [x] **AM-2** Required components to map:
    - Application/Container
    - DataStack/Database
    - HTTP/Router
    - HTTP/Dispatcher
    - Operations/Events
    - Application/Cache
    - Application/Filesystem
    - Operations/Logging
    - Operations/Observability (if active)
    - Security/Cryptography
    - Security/Redaction
    - Integration/ObjectStorage (if active)
    - Operations/Queue (if active)
    - Framework/FailureBoundary

- [x] **AM-3** Record map in `EVIDENCE/cleanup/service-provider-assembly-map.md`

---

## Phase D: PublicSurface Hollow Cleanup

### D-A: Hollow PublicSurface Classes

Rule: `AGENTS.md` Section 2 — hollow shells with no real behavior must be removed or marked SCAFFOLD.

- [x] **D-A.01** `components/DataStack/Database/System/PublicSurface/SchemaBuilder.php` — jedini metod `hasTable()` uvek
  vraća `false`. Fix: ili implementirati pravu proveru kroz konekciju, ili obrisati klasu.
- [x] **D-A.02** `components/DeveloperTools/Testing/System/PublicSurface/Testing.php` — `verifyContracts()` samo vraća
  input bez verifikacije. Fix: implementirati pravu contract verification ili dokumentovati kao placeholder.
- [x] **D-A.03** `components/Operations/MessageBus/System/PublicSurface/Command.php` — potpuno prazan marker interface.
  Fix: dodati dokumentaciju šta marker znači, ili spojiti sa interfejsom koji proširuje.
- [x] **D-A.04** `components/Operations/MessageBus/System/PublicSurface/DomainEvent.php` — potpuno prazan marker
  interface. Fix: isto.
- [x] **D-A.05** `components/Operations/MessageBus/System/PublicSurface/Query.php` — potpuno prazan marker interface.
  Fix: isto.
- [x] **D-A.06** `components/HTTP/Response/System/PublicSurface/ResponseInterface.php` — prazan marker koji samo
  extend-uje PSR interfejs bez dodavanja. Fix: dokumentovati ili ukloniti.
- [x] **D-A.07** `components/Application/Cache/System/PublicSurface/Read/CacheReadTarget.php` — prazan marker interface.
  Fix: dokumentovati ili ukloniti.
- [x] **D-A.08** `components/Application/Cache/System/PublicSurface/CacheFacade.php` — prazan re-export (
  `extends Facade\CacheFacade {}`, 0 dodato). Fix: ukloniti i koristiti `Facade\CacheFacade` direktno.
- [x] **D-A.09** `components/Application/Cache/System/PublicSurface/CacheRegistry.php` — prazan re-export. Fix:
  ukloniti.
- [x] **D-A.10** `components/Application/Cache/System/PublicSurface/CompiledCacheTarget.php` — prazan re-export. Fix:
  ukloniti.
- [x] **D-A.11** `components/Application/Cache/System/PublicSurface/ReadFromCache.php` — prazan re-export. Fix:
  ukloniti.
- [x] **D-A.12** `components/Application/Cache/System/PublicSurface/RuntimeCacheTarget.php` — prazan re-export. Fix:
  ukloniti.
- [x] **D-A.13** `components/HTTP/Middleware/System/PublicSurface/Middleware.php` — jedini metod je `abstract`, ne
  dodaje vrednost naspram `MiddlewareInterface`. Fix: ukloniti klasu.

### D-B: PublicSurface Sadrži Poslovnu Logiku

Rule: `how-to-design-components.md` Section 6.2 — PublicSurface RECEIVES and DELEGATES. Ne sme da sadrži petlje,
kondicionalnu biznis logiku, ili orchestration.

- [x] **D-B.01** `components/HTTP/Router/System/PublicSurface/Router.php` — sadrži:
    - Anonymous class sa kompletnom router implementacijom (linije 106-201)
    - `preg_replace_callback` za URL parametre (234-265)
    - `foreach` middleware pipeline assembly (322-338)
    - Response normalization sa conditional type checks (352-367)
      Fix: izvući anonimnu klasu u imenovanu `Capabilities/RouterCore/` klasu, URL building u
      `Capabilities/UrlBuilder/`, middleware pipeline u `Capabilities/Pipeline/`.

- [x] **D-B.02** `components/API/GraphQL/System/PublicSurface/GraphQLSchema.php` — sadrži:
    - `foreach` petlju za field definitions building (118-125)
    - `match` izraze za operation type routing (132-148)
    - Nested `array_map` u `toArray()` (153-185)
      Fix: schema assembly izvući u `Capabilities/SchemaAssembly/`. PublicSurface neka sadrži samo immutable accessor-e.

- [x] **D-B.03** `components/Operations/ApplicationWorkflow/System/PublicSurface/Workflow.php` — sadrži:
    - `foreach` petlju kroz saga steps sa try/catch compensation (36-48)
    - `resume()` sa state checking i re-execution (80-98)
    - Saga lookup i compensation dispatch u `cancel()` (109-120)
      Fix: saga orchestration izvući u `Capabilities/SagaOrchestrator/`.

### D-C: `throw new` sa NotImplemented/TODO

- [x] **D-C.1** Scan for `throw new` with `NotImplemented`, `TODO`, placeholder, fake unsupported behavior:
  ```bash
  grep -Rn "throw new.*NotImplemented\|throw new.*TODO\|throw new.*NotSupported\|// TODO throw" \
    components/*/System/PublicSurface/ framework/System/PublicSurface/ --include="*.php"
  ```
- [x] **D-C.2** Ako nađeno — fix: zameniti sa realnim exception-om ili dokumentovanim domain exception-om.

### D-D: Classify PublicSurface Changes

- [x] **D-D.1** Classify every changed PublicSurface file:
    - `INTERNAL_ONLY` — not part of public contract
    - `PUBLIC_COMPATIBLE` — changed but backward compatible
    - `PUBLIC_BREAKING` — breaking change (document migration)
    - `DEPRECATED_COMPATIBILITY_SHIM` — deprecated with shim
    - `ROADMAP_NOT_ACTIVE` — not yet stabilized

### D-E: Validate

- [x] **D-E.1** Run hollow public surface gate:
  ```bash
  php tooling/components/check-hollow-public-surfaces.php
  php tooling/refactor/check-public-surface.php
  ```
- [x] **D-E.2** Run full test suite:
  ```bash
  vendor/bin/phpunit --no-coverage
  ```
- [x] **D-E.3** Run PHPStan:
  ```bash
  vendor/bin/phpstan analyse framework components --memory-limit=1G
  ```
- [x] **D-E.4** Update `EVIDENCE/cleanup/05-public-surface-hollow-cleanup.md`
- [x] **D-E.5** Update `skipped-work-ledger.md`: add classification for any hollow class that cannot be removed now
- [x] **D-E.6** Commit:
  ```bash
  git add -A && git commit -m "cleanup: phase D — remove hollow public surfaces, extract business logic from PublicSurface into Capabilities"
  ```

---

## Phase E: Static State Worker Safety

### E-A: Static Mutable State Scan

- [x] **E.A.1** Grep for all static mutable state in framework and components:
  ```bash
  grep -Rn "static \$" framework/System/ components/*/System/ --include="*.php" | \
    grep -v "tests/" | grep -v "Configuration/"
  ```

### E-B: Known Violations — Fix or Classify

- [x] **E-B.01** `components/Application/Container/System/PublicSurface/shortcuts.php:18` — `static $container = null`.
  Ovo je globalni container state koji curi između request-a u long-lived runtime-ima (RoadRunner, Swoole). Iako
  `appInstance()` dozvoljava setovanje, nema eksplicitnog reset-a između request-a.
  Fix: dodati `reset()` u state reset pipeline koji postavlja `static $container = null`. Dokumentovati kao
  `STATIC_STATE_RESETTABLE`.
- [x] **E-B.02** `components/Application/Facade/System/Foundation/Facade.php:19` —
  `protected static array $resolvedInstances = []`. Keširane instance facade-a perzistiraju između request-a.
  `clearAllResolvedInstances()` postoji ali mora biti pozvan.
  Fix: osigurati da request scope reset pipeline poziva `clearAllResolvedInstances()`. Dokumentovati kao
  `FACADE_DELEGATE` sa obaveznim reset-om.
- [x] **E-B.03** `components/Application/Container/System/Container.php:24` —
  `private static ?ContainerInterface $container = null`. Ima `resetState()` metod. Proveriti da li je u reset
  pipeline-u.
  Fix: verifikovati da `resetState()` biva pozvan. Ako nije — dodati u reset pipeline.

### E-C: Classify All Other Findings

- [x] **E-C.1** Svaki static $ finding klasifikovati kao:
    - `BOOT_TIME_DECLARATION` — allowed ako je resettable/frozen
    - `RESETTABLE_SAFE` — allowed ako test dokazuje reset
    - `REQUEST_SCOPED_UNSAFE` — MUST FIX (prebaciti u instancu)
    - `FACADE_DELEGATE` — allowed ako delegira resettable owner-u
    - `CACHE_STATE` — allowed ako je keš request-safe
    - `TEST_ONLY` — allowed

### E-D: Validate

- [x] **E-D.1** Run static state safety gate:
  ```bash
  php tooling/components/check-component-static-state-safety.php
  ```
- [x] **E-D.2** Run full test suite:
  ```bash
  vendor/bin/phpunit --no-coverage
  ```
- [x] **E-D.3** Run PHPStan:
  ```bash
  vendor/bin/phpstan analyse --memory-limit=1G
  ```
- [x] **E-D.4** Update `EVIDENCE/cleanup/06-static-state-worker-safety.md`
- [x] **E-D.5** Commit:
  ```bash
  git add -A && git commit -m "cleanup: phase E — classify static mutable state, add reset for container/facade static caches"
  ```

---

## Phase F: Router / HTTP Runtime Stability

- [x] **F.1** Run all Router and HTTP tests:
  ```bash
  vendor/bin/phpunit --no-coverage components/HTTP/Router/tests/
  vendor/bin/phpunit --no-coverage components/HTTP/Request/tests/
  vendor/bin/phpunit --no-coverage components/HTTP/Response/tests/
  vendor/bin/phpunit --no-coverage components/HTTP/Dispatcher/tests/
  vendor/bin/phpunit --no-coverage framework/tests/
  ```

- [x] **F.2** Verify nonzero test/assertion count for each filter:
  ```bash
  vendor/bin/phpunit --no-coverage --filter Router 2>&1 | grep -E "Tests:|Assertions:"
  ```

- [x] **F.3** Fix any failing tests

- [x] **F.4** Check for hardcoded `localhost`:
  ```bash
  grep -Rn "localhost\|127\.0\.0\.1\|::1" framework/ components/*/System/ --include="*.php" | \
    grep -v "tests/" | grep -v "Configuration/" | grep -v "\.md"
  ```

- [x] **F.5** If hardcoded localhost found in runtime code → move to configuration

- [x] **F.6** Verify Router/Dispatcher is stateless (request passed as argument, not stored)

- [x] **F.7** Validate:
  ```bash
  vendor/bin/phpunit --no-coverage
  ```

- [x] **F.8** Update `EVIDENCE/cleanup/07-router-http-runtime-stability.md`

- [x] **F.9** Commit:
  ```bash
  git add -A && git commit -m "cleanup: stabilize router/http runtime"
  ```

---

## Phase G: PHPStan Type System Closure

- [x] **G.1** Read `phpstan.neon` — find all `ignoreErrors`:
  ```bash
  grep -A5 "ignoreErrors" phpstan.neon | head -40
  ```

- [x] **G.2** Prefer fixing PHPStan errors over adding `ignoreErrors`

- [x] **G.3** `ignoreErrors`/baseline is allowed ONLY when:
    - false positive is proven, OR
    - temporary debt is accepted with owner, reason, expiry, and non-blocking proof

- [x] **G.4** Every ignored error must have:
    - exact `message:` pattern
    - exact `path:` file
    - `comment:` with:
        - owner
        - expiry/review date
        - why not fixed now
        - whether it blocks V5.9

- [x] **G.5** Validate:
  ```bash
  vendor/bin/phpstan analyse framework components tests labs/SystemDesignKit --memory-limit=1G --error-format=raw
  ```

- [x] **G.6** Update `EVIDENCE/cleanup/08-phpstan-type-system-closure.md`

- [x] **G.7** Update `skipped-work-ledger.md`: mark SW-0004, SW-0007 as FIXED_NOW

- [x] **G.8** Commit:
  ```bash
  git add phpstan.neon && git commit -m "cleanup: add owner/expiry to all phpstan baseline entries"
  ```

---

## Phase H: Health / Doctor Checks for Core Components

### H-A: Components With NO Health Check (3 — MUST FIX)

- [x] **H-A.01** `Application/Cache` — Ima `CacheHealthStatus` value object (bogat sa `isHealthy()`, `isDegraded()`,
  `isMemoryCritical()`) ALI nema aktivan `check(): HealthReport` metod. Fix: kreirati
  `Capabilities/HealthCheck/CheckCacheHealth.php` koji koristi `CacheHealthStatus` i proverava store connectivity.
- [x] **H-A.02** `Application/Container` — Nema HealthCheck uopšte. Fix: kreirati
  `Capabilities/HealthCheck/CheckContainerHealth.php` koji proverava: da li je container konfigurisan, da li su bindings
  resolvable, da li ima circular dependencies.
- [x] **H-A.03** `Application/Filesystem` — Nema HealthCheck uopšte. Fix: kreirati
  `Capabilities/HealthCheck/CheckFilesystemHealth.php` koji proverava: da li su konfigurisani diskovi accessible, da li
  je temp directory writable.

### H-B: FAKE Always-Green Health Checks (2 — BLOCKER, MUST FIX)

- [ ] **H-B.01** `components/Operations/Observability/System/Capabilities/Health/ObservabilityHealthCheck.php` — vraća
  hardcoded
  `['healthy' => true, 'drivers' => ['metrics' => true, 'tracing' => true, 'logging' => true], 'issues' => []]` bez
  stvarne provere driver-a. Fix: implementirati stvarnu proveru — da li su metrics/tracing/logging backends reachable.
  Ako je komponenta SCAFFOLD, označiti kao exempt.
- [ ] **H-B.02** `components/Operations/RuntimeSupervision/System/Capabilities/Health/SupervisorHealthCheck.php` — vraća
  hardcoded `['healthy' => true, 'status' => 'running', 'issues' => []]`. Fix: implementirati stvarnu proveru supervisor
  process-a. Ako je SCAFFOLD, označiti kao exempt.

### H-C: HealthCheck Dokazuje Samo Autoloading (5 — MEDIUM, Treba Ojačati)

- [ ] **H-C.01** `components/HTTP/Router/System/Capabilities/HealthCheck/CheckRouterHealth.php` — samo
  `class_exists(RouteCollection::class)`. Fix: dodati stvarnu proveru — da li su rute definisane? Da li middleware
  pipeline radi?
- [ ] **H-C.02** `components/Operations/Events/System/Capabilities/HealthCheck/CheckEventsHealth.php` — samo
  `class_exists()` na 3 klase. Fix: dodati proveru — da li je dispatcher konfigurisan? Da li su listener-i registrovani?
- [ ] **H-C.03** `components/Operations/Logging/System/Capabilities/HealthCheck/CheckLoggingHealth.php` — samo
  `class_exists(Logger::class)`. Fix: dodati proveru — da li je log direktorijum writable? Da li su channels
  konfigurisani?
- [ ] **H-C.04** `components/Security/Redaction/System/Capabilities/HealthCheck/CheckRedactionHealth.php` — samo
  `class_exists()` na 2 klase. Fix: dodati proveru — da li su redaction rules konfigurisane? Da li engine može da se
  instancira?
- [ ] **H-C.05**
  `framework/System/Capabilities/FailureBoundary/Capabilities/HealthCheck/CheckFailureBoundaryHealth.php` — samo
  `class_exists()` na 3 klase. Fix: dodati proveru — da li su failure policy-e compiled? Da li handlers postoje?

### H-D: HealthReport Ne Koristi Kanonski Tip (7 — MEDIUM, Treba Uskladiti)

Sve komponente koriste custom `*HealthReport` sa `bool $healthy` umesto `HealthStatus::Green|Yellow|Red`. Kanonski tip
postoji na `framework/System/Capabilities/Health/Foundation/HealthReport.php`.

- [ ] **H-D.01** `DataStack/Database` — `DatabaseHealthReport` → prebaciti na `HealthReport`
- [ ] **H-D.02** `HTTP/Router` — `RouterHealthReport` → prebaciti na `HealthReport`
- [ ] **H-D.03** `Operations/Events` — `EventsHealthReport` → prebaciti na `HealthReport`
- [ ] **H-D.04** `Operations/Logging` — `LoggingHealthReport` → prebaciti na `HealthReport`
- [ ] **H-D.05** `Security/Redaction` — `RedactionHealthReport` → prebaciti na `HealthReport`
- [ ] **H-D.06** `Security/Cryptography` — `CryptographyHealthReport` → prebaciti na `HealthReport`
- [ ] **H-D.07** `FailureBoundary` — `FailureBoundaryHealthReport` → prebaciti na `HealthReport`

### H-E: Health Checks With Real Invariants (2 — KEEP As-Is)

- [ ] **H-E.01** `DataStack/Database/System/Capabilities/HealthCheck/CheckDatabaseHealth.php` — proverava runtime state
  kroz `GlobalDatabaseLifecycleState::registry()`, `CompiledDatabaseLifecycleRegistry`, `DatabaseConnection`. Jedini
  pravi health check. Samo konvertovati na `HealthReport`.
- [ ] **H-E.02** `Security/Cryptography/System/Capabilities/HealthCheck/CheckCryptographyHealth.php` — proverava
  `extension_loaded('openssl')` (stvarna invarijanta). Samo konvertovati na `HealthReport`.

### H-F: Zero Health Check Tests (Systemic Gap)

- [ ] **H-F.01** — Za SVIH 7+ HealthCheck komponenti napisati testove:
  ```bash
  # Kreirati tests/Unit/HealthCheck/{Component}HealthTest.php za svaki:
  # - DatabaseHealthTest
  # - RouterHealthTest
  # - EventsHealthTest
  # - LoggingHealthTest
  # - RedactionHealthTest
  # - CryptographyHealthTest
  # - FailureBoundaryHealthTest
  ```

### H-G: Missing ServiceProviders (7)

Rule: Container's ServiceProvider interface: "Every component MUST have exactly one ServiceProvider."

- [ ] **H-G.01** `DataStack/Database` — nema ServiceProvider. Fix: kreirati
  `System/Configuration/DatabaseServiceProvider.php`.
- [ ] **H-G.02** `Operations/Events` — nema ServiceProvider. Fix: kreirati
  `System/Configuration/EventsServiceProvider.php`.
- [ ] **H-G.03** `Operations/Logging` — nema ServiceProvider. Fix: kreirati
  `System/Configuration/LoggingServiceProvider.php`.
- [ ] **H-G.04** `Security/Redaction` — nema ServiceProvider. Fix: kreirati
  `System/Configuration/RedactionServiceProvider.php`.
- [ ] **H-G.05** `Security/Cryptography` — nema ServiceProvider. Fix: kreirati
  `System/Configuration/CryptographyServiceProvider.php`.
- [ ] **H-G.06** `Application/Filesystem` — nema ServiceProvider. Fix: kreirati
  `System/Configuration/FilesystemServiceProvider.php`.
- [ ] **H-G.07** `Application/Container` — nema ServiceProvider za sam container. Fix: kreirati
  `System/Configuration/ContainerServiceProvider.php`.

### H-H: Validate

- [ ] **H-H.1** Run health policy gate:
  ```bash
  php tooling/components/check-component-health-doctor-policy.php
  php tooling/components/check-health-proof-map.php
  ```
- [ ] **H-H.2** Run full test suite:
  ```bash
  vendor/bin/phpunit --no-coverage
  ```
- [ ] **H-H.3** Run PHPStan:
  ```bash
  vendor/bin/phpstan analyse framework components tests --memory-limit=1G
  ```
- [ ] **H-H.4** Update `EVIDENCE/cleanup/09-core-health-doctor-checks.md`
- [ ] **H-H.5** Update `skipped-work-ledger.md`: mark SW-0017 as FIXED_NOW
- [ ] **H-H.6** Commit:
  ```bash
  git add -A && git commit -m "cleanup: phase H — add real health checks for all runtime-critical components, fix fake always-green checks, add 7 ServiceProviders"
  ```

---

## Phase I: Component Maturity Gates

- [ ] **I.1** Create `tooling/governance/check-truth-consistency.php`:
  Verify: CURRENT_TRUTH.md exists, EXECUTION.md exists, component-status-lock.md current, skipped-work-ledger has no
  unclassified blockers

- [ ] **I.2** Create `tooling/refactor/check-empty-production-classes.php`:
  Scan all `components/*/*/System/PublicSurface/*.php`, fail if class has no real behavior (only TODO/return null)

- [ ] **I.3** Create `tooling/runtime/check-callable-resolution.php`:
  Test ResolveCallable with class-string::method, [object, method], Closure

- [ ] **I.4** Fix all gates to require NONZERO target assertions:
  ```php
  if ($scannedFiles === 0) {
      fwrite(STDERR, "Gate scanned 0 files — target set is empty or gate mapping is stale\n");
      exit(1);
  }
  ```

  Rule: Gate scripts must NOT rely on PHP `assert()` for enforcement. Use explicit condition + STDERR + non-zero exit
  code.

- [ ] **I.5** For PHPUnit filtered runs, verify nonzero counts:
  ```bash
  vendor/bin/phpunit --no-coverage --filter Router 2>&1 | grep -E "Tests:|Assertions:"
  ```
  Zero-test targeted pass is fake proof.

- [ ] **I.6** Validate all gates:
  ```bash
  for gate in tooling/components/*.php tooling/governance/*.php tooling/refactor/*.php tooling/runtime/*.php tooling/testing/*.php; do
    status="PASS"
    if [ ! -f "$gate" ]; then status="NOT_FOUND"
    else php "$gate" 2>&1 || status="FAIL"
    fi
    echo "=== $gate : $status ==="
  done
  ```

- [ ] **I.7** Update `EVIDENCE/cleanup/10-component-maturity-gates.md`

- [ ] **I.8** Update `skipped-work-ledger.md`: mark SW-0010, SW-0012, SW-0013, SW-0014, SW-0020 as FIXED_NOW

- [ ] **I.9** Update `governance-gap-report.md`: mark GG-0003, GG-0005 as addressed

- [ ] **I.10** Commit:
  ```bash
  git add tooling/ && git commit -m "cleanup: create missing maturity gates with nonzero assertions"
  ```

---

## Phase J: Naming, Duplicates, Skeletons

### J-A: *Manager/*Service/*Handler Files (18 — Preimenovati)

Rule: `AGENTS.md` Section 8 — `Manager`, `Service`, `Handler` su zabranjeni kao imena fajlova jer opisuju tehničku
kategoriju, ne odgovornost.

**Manager fajlovi (5):**

- [ ] **J-A.01** `components/DataStack/Database/System/Capabilities/ORM/EntityManager.php` → preimenovati u
  `ManageEntityPersistence.php` ili `OrchestrateEntityLifecycle.php`
- [ ] **J-A.02** `components/DataStack/Database/System/PublicSurface/EntityManager.php` → isto
- [ ] **J-A.03** `components/Security/DataProtection/System/Capabilities/KeyManager/KeyManager.php` → preimenovati u
  `RotateEncryptionKeys.php` ili `ManageKeyStore.php`
- [ ] **J-A.04** `components/Security/Privacy/System/Capabilities/RetentionPolicyManager/RetentionPolicyManager.php` →
  preimenovati u `EnforceRetentionPolicy.php` ili `ApplyDataRetentionRules.php`
- [ ] **J-A.05** `framework/System/Capabilities/Runtime/StateResetManager.php` → preimenovati u
  `ResetApplicationState.php`

**Service fajlovi (6):**

- [ ] **J-A.06** `components/Application/Container/System/Capabilities/Execution/BuildService.php` → preimenovati u
  `AssembleServiceContainer.php`
- [ ] **J-A.07** `components/Application/Container/System/Flows/ExplainService/ExplainService.php` → preimenovati u
  `DescribeContainerService.php`
- [ ] **J-A.08** `components/Application/Container/System/Flows/ResolveService/ResolveService.php` → preimenovati u
  `ResolveContainerService.php`
- [ ] **J-A.09** `components/Security/DataProtection/System/Capabilities/EncryptionService/EncryptionService.php` →
  preimenovati u `EncryptDataPayload.php`
- [ ] **J-A.10** `framework/System/Flows/ExplainContainerService/ExplainContainerService.php` → preimenovati u
  `DescribeContainerServiceResolution.php`
- [ ] **J-A.11** `framework/System/Flows/ExplainContainerService/ExplainContainerService.php` (folder) → folder
  preimenovati u `DescribeContainerServiceResolution/`

**Handler fajlovi (7):**

- [ ] **J-A.12** `components/API/ApiBlueprint/System/Capabilities/RestApi/FilterHandler.php` → preimenovati u
  `ApplyRestApiFilter.php`
- [ ] **J-A.13** `components/API/ApiBlueprint/System/Capabilities/RestApi/PaginationHandler.php` → preimenovati u
  `PaginateRestApiResponse.php`
- [ ] **J-A.14** `components/API/ApiBlueprint/System/Capabilities/RestApi/SortHandler.php` → preimenovati u
  `SortRestApiCollection.php`
- [ ] **J-A.15** `components/Operations/Logging/System/Capabilities/ErrorHandling/GlobalErrorHandler.php` → preimenovati
  u `CaptureUnhandledErrors.php`
- [ ] **J-A.16** `components/Operations/Logging/System/Capabilities/ErrorHandling/ShutdownErrorHandler.php` →
  preimenovati u `CaptureShutdownFailures.php`
- [ ] **J-A.17** `components/Operations/Queue/System/Capabilities/Job/JobHandler.php` → preimenovati u
  `ExecuteQueuedJob.php`
- [ ] **J-A.18** `framework/System/Flows/HandleIncomingHttp/ConfiguredRoutesHttpHandler.php` → preimenovati u
  `DispatchConfiguredRoute.php`

### J-B: Duplicate Names / Overlapping Classes

- [ ] **J-B.1** Proveriti duplicate Database/Query/Connection klase:
  ```bash
  find components/ -name "*Database*" -o -name "*Query*" -o -name "*Connection*" | sort
  ```
- [ ] **J-B.2** Proveriti da li postoje klase sa istim imenom u različitim komponentama:
  ```bash
  find components/ -name "*.php" -exec basename {} \; | sort | uniq -d
  ```

### J-C: Empty Production Classes (Skeletons)

- [ ] **J-C.1** Run empty production class gate:
  ```bash
  php tooling/refactor/check-empty-production-classes.php
  ```
- [ ] **J-C.2** Pregledati ručno:
    - `components/Operations/MessageBus/System/PublicSurface/Command.php` — prazan marker, dokuementovati ili obrisati
    - `components/Operations/MessageBus/System/PublicSurface/DomainEvent.php` — prazan marker
    - `components/Operations/MessageBus/System/PublicSurface/Query.php` — prazan marker
    - `components/Application/Cache/System/PublicSurface/CacheFacade.php` — re-export bez dodate vrednosti
    - (ostali su pokriveni u Phase D-A)

### J-D: Files Outside System/ (Cross-Check)

- [ ] **J-D.1** Proveriti da li fajlovi iz B-D faze još uvek postoje van System/:
  ```bash
  find components/*/*/ -maxdepth 1 -name "*.php" | grep -v "/System/"
  ```
  Ako ih ima — premestiti.

### J-E: Validate

- [ ] **J-E.1** Run naming gates:
  ```bash
  php tooling/refactor/check-advanced-pattern-folder-violations.php
  php tooling/refactor/check-duplicate-owners.php
  php tooling/refactor/check-component-canonical-shape.php
  ```
- [ ] **J-E.2** Run full test suite:
  ```bash
  vendor/bin/phpunit --no-coverage
  ```
- [ ] **J-E.3** Run PHPStan:
  ```bash
  vendor/bin/phpstan analyse framework components --memory-limit=1G
  ```
- [ ] **J-E.4** Update `EVIDENCE/cleanup/11-naming-duplicates-skeletons.md`
- [ ] **J-E.5** Update `skipped-work-ledger.md`
- [ ] **J-E.6** Update `governance-gap-report.md`
- [ ] **J-E.7** Commit:
  ```bash
  git add -A && git commit -m "cleanup: phase J — rename 18 Manager/Service/Handler files, remove skeleton classes"
  ```

---

## Phase K: Truth, Evidence, Governance Reconciliation

- [ ] **K.1** Update `CURRENT_TRUTH.md`:
  ```
  Branch: main
  Active Phase: Cleanup Program — Final Stages
  Status: YELLOW_UNTIL_FINAL_AUDIT
  ```

- [ ] **K.2** Update `EVIDENCE/EXECUTION.md`:
  ```markdown
  # Execution Plan — Cleanup Program

  ## Active Stage
  Cleanup Program Phase K/L: Truth + Final Audit

  ## Completed
  - [x] Worktree hygiene
  - [x] Scope isolation verified
  - [x] Phase A: Validation baseline closure
  - [x] Phase B: Component status lock rebuild
  - [x] Phase C: DI/runtime assembly cleanup
  - [x] ServiceProvider / Assembly map completed
  - [x] Phase D: PublicSurface hollow cleanup
  - [x] Phase E: Static state classification
  - [x] Phase F: Router/HTTP stability
  - [x] Phase G: PHPStan type closure
  - [x] Phase H: Health checks implemented
  - [x] Phase I: Maturity gates created
  - [x] Phase J: Naming/duplicates/skeletons

  ## Remaining
  - [ ] Phase K: Truth reconciled
  - [ ] Phase L: Final audit
  - [ ] Phase M: Independent review
  ```

- [ ] **K.3** Update `.agents/management/ACTIVE.md`:
  ```markdown
  Current: Cleanup Program — Phase K: Truth + Governance Reconciliation
  Previous: Phase J: Naming/Duplicates/Skeletons
  Next: Phase L: Final Whole-System Acceptance Audit
  Status: IN_PROGRESS
  ```

- [ ] **K.4** Update `.agents/management/TODO.md` — move completed phases to DONE

- [ ] **K.5** Update `.agents/management/BUGS.md`:
    - If open bugs → link to them
    - If none → "No open bugs identified during cleanup"

- [ ] **K.6** Finalize `EVIDENCE/cleanup/skipped-work-ledger.md`:
    - Change all completed `DEFERRED_WITH_OWNER` to `FIXED_NOW`
    - Leave only genuinely unresolved items
    - Verify NO TBD remains
    - Verify every ACCEPTED_EXCEPTION has owner + expiry

- [ ] **K.7** Finalize `EVIDENCE/cleanup/governance-gap-report.md`:
    - Add any new gaps discovered during execution
    - Verify no current-stage blocking gap remains

- [ ] **K.8** Finalize `EVIDENCE/cleanup/accepted-exceptions-ledger.md`:
    - Verify every entry has owner + expiry

- [ ] **K.9** Finalize `EVIDENCE/cleanup/follow-up-work-ledger.md`:
    - Verify no blocker remains that blocks V5.9

- [ ] **K.10** Create fresh `EVIDENCE/cleanup/governance-coverage-proof.md` with full table

- [ ] **K.11** Create fresh `EVIDENCE/cleanup/how-to-coverage-gaps.md`

- [ ] **K.12** Commit:
  ```bash
  git add -A && git commit -m "cleanup: reconcile truth, evidence, governance ledgers"
  ```

---

## Phase L: Final Whole-System Acceptance Audit

### L-A: Runtime Entry Matrix

Before running validation, complete the runtime entry matrix:

| Runtime entry               | FailureBoundary? | Resolver? | Health check? | Static reset? | Tests? |
|-----------------------------|-----------------:|----------:|--------------:|--------------:|-------:|
| HTTP request                |                  |           |               |               |        |
| Console command             |                  |           |               |               |        |
| Event dispatch              |                  |           |               |               |        |
| Queue job                   |                  |           |               |               |        |
| Database lifecycle listener |                  |           |               |               |        |
| Boot/provider failure       |                  |           |               |               |        |

- [ ] **L-A.1** Fill matrix in `EVIDENCE/cleanup/runtime-entry-matrix.md`
- [ ] **L-A.2** Rule: V5.9 Boot DSL cannot be READY if main runtime entries have unknown assembly/failure/reset posture

### L-B: Full Validation

- [ ] **L-B.1** Full final validation:
  ```bash
  echo "=== COMPOSER ===" && composer validate --no-check-publish
  echo "=== AUTOLOAD ===" && composer dump-autoload -o
  echo "=== PHPUNIT ===" && vendor/bin/phpunit --no-coverage 2>&1 | tail -5
  echo "=== PHPSTAN ===" && vendor/bin/phpstan analyse framework components tests labs/SystemDesignKit --memory-limit=1G --error-format=raw 2>&1 | tail -5
  ```

- [ ] **L-B.2** Run all gates (same command as 0.3), classify each result:
  ```
  tooling/security/check-security-blockers.php: PASS
  tooling/governance/check-truth-consistency.php: NOT_FOUND → FAIL (must exist now)
  ```

- [ ] **L-B.3** Run broken reference audit:
  ```bash
  php tooling/audit_broken_refs.php
  ```
  If exit 0 with RED content → classify as FAIL, not PASS.

- [ ] **L-B.4** Run runtime doctor:
  ```bash
  php avax runtime:doctor
  ```

- [ ] **L-B.5** Create `EVIDENCE/cleanup/13-final-whole-system-acceptance-audit.md`

- [ ] **L-B.6** Update `EVIDENCE/cleanup/00-cleanup-control-lock.md` with final status

- [ ] **L-B.7** Final commit:
  ```bash
  git add EVIDENCE/cleanup/13-final-whole-system-acceptance-audit.md EVIDENCE/cleanup/00-cleanup-control-lock.md
  git commit -m "cleanup: final whole-system acceptance audit — <FINAL STATUS>"
  ```

---

## Phase M: Independent Read-Only Review

- [ ] **M.1** No code changes in this phase — read-only verification only.

- [ ] **M.2** Verify:
    - [ ] Final validation output (composer, PHPUnit, PHPStan) — all GREEN
    - [ ] All gates — PASS (no NOT_FOUND treated as PASS)
    - [ ] Component status lock — current, no missing components
    - [ ] Health checks — exist, NOT fake, produce structured HealthReport
    - [ ] ServiceProvider/Assembly map — complete for all active core components
    - [ ] Runtime entry matrix — complete, no unknown posture
    - [ ] DI assembly — no runtime `new` infrastructure violations
    - [ ] PublicSurface — no hollow APIs, no fake scaffold
    - [ ] Static state — no request-scoped unsafe state
    - [ ] PHPStan baseline — every entry has owner + expiry
    - [ ] Scope isolation — EVIDENCE/labs/recovery are NOT in production autoload
    - [ ] Worktree hygiene — no unclassified dirty files in commits
    - [ ] skipped-work-ledger — no V5.9 blockers, no TBD, no ownerless exceptions
    - [ ] governance-gap-report — no current-stage blocking gaps
    - [ ] accepted-exceptions-ledger — every entry has owner + expiry
    - [ ] follow-up-work-ledger — no blocker before V5.9
    - [ ] governance-coverage-proof — ALL how-to documents applied or gap-documented
    - [ ] Truth files — CURRENT_TRUTH.md, EXECUTION.md, ACTIVE.md, TODO.md all updated

- [ ] **M.3** Final review decision (one of):
    - `APPROVE_V5_9_READY` — all checks pass, V5.9 can proceed
    - `REJECT_YELLOW_WITH_EXACT_BLOCKERS` — specific blockers prevent V5.9
    - `REJECT_RED_RUNTIME_OR_GOVERNANCE_BROKEN` — serious failure

- [ ] **M.4** Record review decision in `EVIDENCE/cleanup/14-independent-review.md`

- [ ] **M.5** Final commit:
  ```bash
  git add EVIDENCE/cleanup/14-independent-review.md
  git commit -m "cleanup: independent review — <DECISION>"
  ```

---

## BACKWARD COMPATIBILITY / PUBLIC API CHANGES

- [ ] **BC-1** Any change in PublicSurface must be classified:
    - `INTERNAL_ONLY` — not part of public contract
    - `PUBLIC_COMPATIBLE` — backward compatible
    - `PUBLIC_BREAKING` — breaking change (document migration)
    - `DEPRECATED_COMPATIBILITY_SHIM` — deprecated with shim
    - `ROADMAP_NOT_ACTIVE` — not yet stabilized

- [ ] **BC-2** If public API changes:
    - update tests
    - update truth/evidence
    - document migration or reason in commit message

- [ ] **BC-3** Rule: PublicSurface changes are compatibility decisions.

---

## ROLLBACK / REVERT SAFETY

- [ ] **RB-1** For each commit created during cleanup, record:
    - what changed (summary)
    - how to revert: `git revert <hash>`
    - what validation proves the change is safe
    - whether it changed public API (BC classification)
    - whether it changed runtime behavior

- [ ] **RB-2** Record rollback map in `EVIDENCE/cleanup/rollback-map.md`

- [ ] **RB-3** Rule: Enterprise cleanup must be fully reversible.

---

## VALIDATION COMMANDS — QUICK REFERENCE

```bash
# Core
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests labs/SystemDesignKit --memory-limit=1G

# Gates — Refactor (classify each result PASS/FAIL/NOT_FOUND)
php tooling/refactor/check-component-canonical-shape.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
php tooling/refactor/check-advanced-pattern-folder-violations.php
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-raw-file-operations.php
php tooling/refactor/check-broken-reference-semantics.php
php tooling/refactor/check-empty-production-classes.php

# Gates — Security
php tooling/security/check-security-blockers.php

# Gates — Governance
php tooling/governance/check-component-adoption.php
php tooling/governance/check-truth-consistency.php

# Gates — Components
php tooling/components/check-component-status-lock-coverage.php
php tooling/components/check-no-unclassified-scaffolding.php
php tooling/components/check-hollow-public-surfaces.php
php tooling/components/check-component-runtime-assembly.php
php tooling/components/check-component-static-state-safety.php
php tooling/components/check-component-health-doctor-policy.php
php tooling/components/check-health-proof-map.php

# Gates — Runtime
php tooling/runtime/check-callable-resolution.php

# Gates — Testing
php tooling/testing/check-nonzero-target-assertions.php

# Evidence / Runtime
php tooling/audit_broken_refs.php
php avax runtime:doctor
```

---

## PHASE DEPENDENCY GRAPH

```
Worktree Hygiene + Scope Isolation (before anything)
  │
  ▼
Phase 0 (Preflight + Baseline)
  │
  ▼
Phase B (Status Lock) ──────────────────────────────────────────┐
  │                                                              │
  ▼                                                              │
Phase C (DI/Runtime Assembly) ───► ServiceProvider/Assembly Map ─┤
  │                                                              │
  ▼                                                              │
Phase D (PublicSurface) ─────────► Phase H (Health Checks) ─────┤
  │                                                              │
  ▼                                                              │
Phase E (Static State) ──────────► Phase I (Maturity Gates) ────┤
  │                                                              │
  ▼                                                              │
Phase F (Router/HTTP) ───────────► Phase J (Naming/Dupes) ──────┤
  │                                                              │
  ▼                                                              │
Phase G (PHPStan) ───────────────► Phase K (Truth/Governance) ──┤
  │                                                              │
  ▼                                                              │
Phase L (Final Audit) ◄──────────► Phase M (Review) ◄───────────┘
```

---

## OUTPUT CONTRACT

When ALL phases are complete (including Phase M), final status must be one of:

- **FULL_GREEN_READY_FOR_V5_9** — all pass, no blockers
- **YELLOW_WITH_EXACT_BLOCKERS** — specific blockers listed
- **RED_RUNTIME_OR_GOVERNANCE_BROKEN** — serious failure

### FULL_GREEN_READY_FOR_V5_9 requires:

- [ ] composer validate — GREEN
- [ ] composer dump-autoload — 0 warnings
- [ ] PHPUnit — 0 failures, 0 errors (nonzero tests + assertions)
- [ ] PHPStan — 0 errors or formally accepted non-blocking baseline with owner + expiry
- [ ] All gates — PASS (NOT_FOUND is NOT PASS)
- [ ] Scope isolation — EVIDENCE/labs/recovery NOT in production autoload
- [ ] No scaffold ambiguity (all scaffold marked honestly)
- [ ] No hollow active PublicSurface
- [ ] No unsafe static state (no request-scoped unsafe)
- [ ] No runtime infrastructure assembly leaks
- [ ] Router/HTTP stable (all tests pass, no hardcoded localhost)
- [ ] Component status lock current and complete
- [ ] ServiceProvider/Assembly map complete for all active core components
- [ ] Runtime entry matrix complete (no unknown assembly/failure/reset posture)
- [ ] Health/doctor checks for all active core components (real invariants, structured output)
- [ ] Health checks are NOT fake always-green
- [ ] DI gate allows by path/context, NOT by class name
- [ ] PHPStan baseline — every entry has owner + expiry
- [ ] Skipped-work ledger — no V5.9 blockers, no TBD
- [ ] Accepted exceptions — every entry has owner + expiry
- [ ] Governance gaps — documented and non-blocking
- [ ] Follow-up work — no blocker before V5.9
- [ ] Governance coverage proof — ALL how-to documents applied or gap-documented
- [ ] How-to coverage gaps — incomplete rules documented with safety proof
- [ ] Truth files updated (CURRENT_TRUTH.md, EXECUTION.md, ACTIVE.md, TODO.md, BUGS.md)
- [ ] Worktree hygiene recorded
- [ ] Rollback map exists
- [ ] Independent review decision = APPROVE_V5_9_READY
- [ ] Global Rules GR-1 through GR-4 satisfied
