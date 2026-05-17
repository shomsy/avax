Da. Ovo treba da bude **redesign/migration program**, ne običan refactor. Ako ga uradiš kao “move files + fix
namespaces”, napravićeš haos. Ako ga uradiš kao **governance-driven staged migration**, dobijaš ozbiljan framework.

Glavna odluka:

```text
Avax migration direction:
REDESIGN, not rewrite.

Existing useful components stay.
The architecture axis changes.

Old axis:
components/ as almost everything

New axis:
framework/System = runtime/lifecycle owner
components/*/System = reusable capabilities
PublicSurface = stable external API
Flows = behavior
Capabilities = reusable mechanisms
Configuration = assembly/wiring
Foundation = tiny neutral primitives
```

Ovo je u skladu sa tvojim pravilom da arhitektura mora da bude čitljiva kroz flow/slice/unit/functions, da folder kaže
flow ili capability, unit responsibility, a function tačnu akciju. Takođe je važno da se repo root i system root
razlikuju: repo root organizuje projekat, system root izražava stvarnu arhitekturu.

## 0. Non-negotiable migration laws

Ovo bih stavio na vrh plana.

```md
# Avax Runtime-Agnostic Framework Migration Plan

## Non-Negotiable Laws

1. Do not perform a big-bang filesystem migration.
2. Do not move behavior that is not protected by characterization tests.
3. Do not introduce framework runtime concepts into reusable components.
4. Do not leak Swoole, RoadRunner, FrankenPHP, Workerman, ReactPHP, or Amp APIs into core components.
5. Do not create PublicSurface/ mechanically.
6. Do not create Foundation/ as a junk drawer.
7. Do not keep duplicate owners after migration.
8. Do not preserve obsolete structures only to avoid discomfort.
9. Every migrated slice must have tests, docs, and governance review.
10. Every phase must leave the repository in a runnable state.
```

Ovo direktno prati tvoje refactoring governance pravilo: characterization tests pre refaktora, staged migration, aktivno
uklanjanje obsolete struktura i bez big-bang haosa.

## 1. Target architecture

Finalni cilj:

```text
avax/
  framework/
    System/
      PublicSurface/
      Flows/
      Capabilities/
      Configuration/
      Foundation/

  components/
    Container/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/

    Config/
    Events/
    Request/
    Response/
    Router/
    Middleware/
    Console/
    Cache/
    Session/
    Filesystem/
    Database/
    Validation/
    Auth/
    View/
    Logging/

  docs/
  tests/
  examples/
  tooling/
```

Objašnjenje:

`framework/System/` je Avax kao framework. Tu živi runtime lifecycle: boot, HTTP handling, console handling, worker
loop, request scope, state reset, shutdown.

`components/*/System/` su reusable capabilities. One ne smeju da znaju da li ih koristi PHP-FPM, RoadRunner, FrankenPHP
ili Swoole.

`PublicSurface/` je javni API boundary. Prima spoljne pozive i delegira, ali ne sadrži realnu mašineriju.

`Flows/` izvršava ponašanje.

`Capabilities/` drži reusable mehanizme.

`Configuration/` sklapa sistem.

`Foundation/` drži male neutralne primitive.

## 2. Migration outcome

Na kraju migracije Avax treba da ima ove osobine:

```text
1. Framework runtime owner exists.
2. Components are independent capabilities.
3. Request state is scoped.
4. Long-lived workers cannot leak request state.
5. Runtime adapters are isolated.
6. Public API is explicit and small.
7. Tests prove behavior.
8. Docs explain the system without reading code.
9. Governance review can verify every rule.
10. Old structural aliases are removed or explicitly deprecated.
```

Dokumentacija mora biti dizajn artefakt, ne naknadna beleška. Tvoj docs governance kaže da `docs/` mora biti canonical
lokacija, da dokumentacija mora mirrorovati source strukturu i da dokumentacija mora objasniti intent, trade-offs i
posledice, ne samo prepričavati kod.

---

# Phase 1: Governance freeze and migration baseline

Cilj: pre nego što se bilo šta pomera, zakucati pravila igre.

## Deliverables

```text
docs/decisions/0001-avax-runtime-agnostic-framework.md
docs/decisions/0002-framework-system-is-runtime-owner.md
docs/decisions/0003-components-are-reusable-capabilities.md
docs/decisions/0004-public-surface-boundary.md
docs/decisions/0005-runtime-adapters-must-not-leak.md
docs/decisions/0006-request-state-must-be-scoped.md

EVIDENCE/review.md
EVIDENCE/migration-map.md
EVIDENCE/risk-register.md
```

## Tasks

```text
1. Discover all how-to-*.md governance files.
2. Create governance inventory.
3. Extract all MUST / MUST NOT / HARD RULE / Mandatory rules.
4. Create initial GOVERNANCE COMPLIANCE REPORT.
5. Reconstruct current as-built execution flows:
   - bootstrap
   - HTTP request
   - routing
   - response
   - console command
   - database query
   - cache read/write
   - auth flow if present
6. Define primary axis:
   Avax = runtime-agnostic framework runtime + reusable capabilities.
7. Define system invariants.
8. Define migration risk register.
```

Tvoj code review governance traži da review ne krene od pojedinačnih fajlova, nego od sistema, as-built flow-a, primary
axis-a, responsibility/boundary mapa i invariants. Takođe traži potpunu proveru svih `how-to-*.md` dokumenata, bez
cherry-pickovanja.

## System invariants

Ovo su invariants koje bih zakucao:

```text
1. Core framework must not assume that PHP process dies after each request.
2. Request-specific state must live inside request scope.
3. Runtime adapters must depend inward on Avax abstractions.
4. Components must not depend on server-specific APIs.
5. PublicSurface must delegate, not execute internals.
6. Configuration assembles, but does not become hidden behavior.
7. Foundation contains tiny neutral primitives only.
8. Every meaningful migrated behavior must be test-protected.
9. Every external adapter must prove its contract.
10. Documentation must explain one exact path from trigger to result.
```

---

# Phase 2: Create target skeleton without moving behavior

Cilj: napraviti novu arhitektonsku osu bez lomljenja postojećeg koda.

## Deliverables

```text
framework/System/
  PublicSurface/
  Flows/
  Capabilities/
  Configuration/
  Foundation/

docs/framework/System/how-this-works.md
tests/Unit/Framework/System/.gitkeep
tests/Integration/Framework/.gitkeep
tests/Contract/Runtime/.gitkeep
```

## Tasks

```text
1. Create framework/System skeleton.
2. Create minimal PublicSurface/Avax.php as future framework entrypoint.
3. Create minimal Flows/BootApplication/ placeholder owner.
4. Create minimal Capabilities/Runtime/ abstractions.
5. Create minimal Capabilities/RequestScope/ abstractions.
6. Create minimal Configuration/BuildApplication/ owner.
7. Create minimal Foundation/Time, Paths, Environment, Failure.
8. Do not wire it into production yet.
9. Add documentation explaining why skeleton exists.
10. Add tests only for real behavior introduced.
```

## Important rule

Ne uvoditi “fake empty empire”. Svaki file mora imati razlog. Ako nema ponašanje sada, neka bude samo folder + docs +
TODO map, ili minimalni contract ako je stvarno potreban.

---

# Phase 3: Runtime lifecycle core

Cilj: Avax dobija framework lifecycle owner.

## Target tree

```text
framework/System/
  PublicSurface/
    Avax.php
    AvaxInterface.php

  Flows/
    BootApplication/
      BootApplication.php
      BuildApplicationState.php
      ApplicationBootFailed.php

    HandleIncomingHttp/
      HandleIncomingHttp.php
      CreateRequestScope.php
      TerminateHttpRequest.php
      IncomingHttpFailed.php

    RunConsoleCommand/
      RunConsoleCommand.php
      ConsoleCommandFailed.php

    ShutdownRuntime/
      ShutdownRuntime.php
      RuntimeShutdownFailed.php

  Capabilities/
    Runtime/
      Runtime.php
      RuntimeInterface.php
      RuntimeContext.php
      RuntimeState.php
      RuntimeRequest.php
      RuntimeResponse.php
      RuntimeResult.php

    RequestScope/
      RequestScope.php
      RequestScopeInterface.php
      RequestScopeId.php
      RequestScopeStore.php

    ComponentRegistry/
      ComponentRegistry.php
      ComponentDefinition.php
      ComponentProviderInterface.php

  Configuration/
    BuildApplication/
      BuildApplication.php
      ApplicationBuilder.php

    ConfigureRuntime/
      ConfigureRuntime.php
      RuntimeConfiguration.php

    RegisterComponents/
      RegisterComponents.php
      ComponentRegistration.php

  Foundation/
    Time/
      Clock.php
      SystemClock.php
      FrozenClock.php

    Paths/
      ProjectPath.php
      RuntimePath.php

    Environment/
      EnvironmentName.php

    Failure/
      FrameworkFailure.php
      FrameworkBootFailed.php
      FrameworkMisconfigured.php
```

## Tasks

```text
1. Define RuntimeInterface.
2. Define RuntimeContext.
3. Define RuntimeState.
4. Define RuntimeRequest and RuntimeResponse as Avax abstractions.
5. Define BootApplication flow.
6. Define BuildApplication flow.
7. Define ComponentRegistry.
8. Define Avax public entrypoint.
9. Add unit tests for RuntimeContext, RuntimeState, ComponentRegistry.
10. Add feature test for "application can boot with no registered components".
```

## Required tests

```text
tests/Unit/Framework/System/Capabilities/Runtime/RuntimeContextTest.php
tests/Unit/Framework/System/Capabilities/Runtime/RuntimeStateTest.php
tests/Unit/Framework/System/Capabilities/ComponentRegistry/ComponentRegistryTest.php
tests/Unit/Framework/System/Flows/BootApplication/BootApplicationTest.php
tests/Feature/Framework/BootApplicationFeatureTest.php
```

Unit tests moraju dokazivati ponašanje, ne pokrivenost. Tvoj unit testing standard traži behavior-first testove, jasne
test nazive, happy/failure/edge/regression/security scenarije gde imaju smisla i split na
Unit/Integration/Feature/Contract.

---

# Phase 4: Request scope and state reset safety

Cilj: worker-safe foundation.

Ovo je najvažnija faza. Ako ovo pogrešiš, Avax može raditi u demo-u, ali nije bezbedan za long-lived runtime.

## Target tree

```text
framework/System/
  Flows/
    HandleIncomingHttp/
      CreateRequestScope.php
      CloseRequestScope.php

    HandleWorkerRequest/
      HandleWorkerRequest.php
      OpenWorkerRequestScope.php
      CloseWorkerRequestScope.php

    ResetApplicationState/
      ResetApplicationState.php
      ResetRequestScope.php
      ResetRuntimeContext.php
      ResetDiagnosticsContext.php
      ResetComponentState.php
      StateResetFailed.php

  Capabilities/
    RequestScope/
      RequestScope.php
      RequestScopeInterface.php
      RequestScopeId.php
      RequestScopeStore.php
      ScopedValue.php
      RequestScopeAlreadyClosed.php
      RequestScopeNotOpen.php

    StateReset/
      ResettableState.php
      StateResetRegistry.php
      StateResetReport.php
```

## Tasks

```text
1. Implement RequestScope lifecycle.
2. Implement RequestScopeStore.
3. Implement open/close behavior.
4. Implement ResettableState contract.
5. Implement StateResetRegistry.
6. Make runtime context request-aware.
7. Add tests proving scope isolation.
8. Add tests proving closed scope cannot be reused.
9. Add tests proving reset clears request-local values.
10. Add abuse tests for accidental cross-request state.
```

## Required tests

```text
test_it_opens_request_scope_when_http_request_starts()
test_it_closes_request_scope_when_http_request_ends()
test_it_rejects_access_when_request_scope_is_closed()
test_it_does_not_leak_scoped_value_between_two_requests()
test_it_resets_runtime_context_after_worker_request()
test_it_reports_reset_failure_when_component_state_cannot_be_reset()
```

## Done criteria

```text
1. Two simulated requests cannot see each other's scoped values.
2. Current request is never stored globally.
3. Current user/session/route/correlation id live in request scope or explicit context.
4. ResetApplicationState is called in worker request path.
5. Tests prove no state leak.
```

---

# Phase 5: Formalize PublicSurface

Cilj: stabilan public API bez internals.

## Target tree

```text
framework/System/PublicSurface/
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

components/Cache/System/PublicSurface/
  Cache.php
  CacheInterface.php
  Facades/
    CacheFacade.php
```

## Tasks

```text
1. Add how-to-architecture-extension.md to governance inventory.
2. Add PublicSurface lane to framework/System.
3. Move only stable API entrypoints into PublicSurface.
4. Keep behavior in Flows.
5. Keep runtime machinery in Capabilities/Runtime.
6. Keep adapter code outside PublicSurface.
7. Add API stability notes in docs.
8. Add tests for public API behavior.
```

## PublicSurface review checklist

```text
1. Is every class externally useful?
2. Does every class delegate?
3. Is there business logic inside? If yes, fail.
4. Is there runtime adapter code inside? If yes, fail.
5. Is there request-scoped mutable state inside? If yes, fail.
6. Is it small enough to document clearly?
```

---

# Phase 6: Component migration order

Ne migrirati sve odjednom. Redosled je bitan.

## Correct order

```text
1. Container
2. Config
3. Events
4. Request
5. Response
6. Router
7. Middleware
8. Console
9. Cache
10. Session
11. Filesystem
12. Database
13. Validation
14. Auth
15. View
16. Logging
```

Zašto ovim redom?

`Container`, `Config`, `Events` su composition/runtime foundation.

`Request`, `Response`, `Router`, `Middleware` grade HTTP lifecycle.

`Console` omogućava CLI lifecycle.

`Cache`, `Session`, `Filesystem`, `Database` su infrastructure-heavy capabilities sa contract testovima.

`Validation`, `Auth` zavise od većeg broja prethodnih stvari.

`View`, `Logging` su korisne ali ne smeju blokirati runtime redesign.

## Per-component migration template

Za svaku komponentu radi isto:

```text
components/<Component>/
  System/
    PublicSurface/
    Flows/
    Capabilities/
    Configuration/
    Foundation/
```

## Per-component migration steps

```text
1. Inventory current component files.
2. Identify current public API.
3. Identify current flows.
4. Identify shared mechanisms.
5. Identify configuration/wiring.
6. Identify tiny foundation primitives.
7. Write characterization tests for existing behavior.
8. Create new target folders.
9. Move only one flow at a time.
10. Update namespace for that slice.
11. Update tests.
12. Update docs.
13. Run quality gates.
14. Delete obsolete aliases only after consumers are migrated.
```

## Per-component deliverables

```text
components/<Component>/System/PublicSurface/
components/<Component>/System/Flows/
components/<Component>/System/Capabilities/
components/<Component>/System/Configuration/
components/<Component>/System/Foundation/

docs/components/<Component>/System/how-this-works.md
tests/Unit/Components/<Component>/
tests/Integration/Components/<Component>/
tests/Contract/<Component>/ where applicable
```

---

# Phase 7: Runtime adapters

Tek kad lifecycle i request scope postoje.

## Adapter order

```text
1. PhpFpmRuntime
2. CliRuntime
3. WorkerRuntime abstraction
4. FrankenPhpRuntime
5. RoadRunnerRuntime
6. WorkermanRuntime
7. SwooleRuntime
8. Amp/ReactPHP concurrency bridges
```

## Target tree

```text
framework/System/Capabilities/Runtime/
  PhpFpm/
    PhpFpmRuntime.php
    PhpFpmRequestReader.php
    PhpFpmResponseSender.php

  Cli/
    CliRuntime.php
    CliInputReader.php
    CliOutputWriter.php

  Worker/
    WorkerRuntimeInterface.php
    WorkerLoop.php
    WorkerLifecycle.php

  Adapters/
    FrankenPhp/
      FrankenPhpRuntime.php
      FrankenPhpWorkerLoop.php

    RoadRunner/
      RoadRunnerRuntime.php
      RoadRunnerWorkerLoop.php

    Workerman/
      WorkermanRuntime.php
      WorkermanServerAdapter.php

    Swoole/
      SwooleRuntime.php
      SwooleHttpServerAdapter.php
```

## Rules

```text
1. Runtime adapters may depend on server-specific APIs.
2. Core framework may depend only on Avax Runtime abstractions.
3. Components may never depend on Swoole/RoadRunner/FrankenPHP APIs.
4. Each runtime adapter must pass RuntimeAdapterContractTest.
5. Worker adapters must prove request state reset.
```

Contract tests su posebno bitni za adaptere, storages, filesystem, session, cache i slične multiple-implementation
boundary-jeve; standard kaže da svaki adapter mora dokazati da poštuje contract.

---

# Phase 8: Async and concurrency readiness

Ne uvoditi prerano. Prvo worker-safe synchronous core.

## Target components later

```text
components/Concurrency/
  System/
    PublicSurface/
    Flows/
      RunConcurrentTasks/
      AwaitTaskResult/
      CancelTask/
    Capabilities/
      Tasks/
      Futures/
      Fibers/
      EventLoop/
    Configuration/
    Foundation/

components/HttpClient/
  System/
    Flows/
      SendHttpRequest/
      SendConcurrentRequests/
    Capabilities/
      Clients/
      Retry/
      Timeout/
      CircuitBreaker/
```

## Rules

```text
1. Async is a capability, not a global architecture infection.
2. Fiber/event-loop details stay behind Concurrency capability.
3. Normal framework flows remain readable as synchronous orchestration.
4. Async drivers are adapters.
5. No hidden event loop global state.
```

---

# Phase 9: Documentation migration

Cilj: dokumentacija prati source, ali ne opisuje kod površno.

## Tasks

```text
1. Create docs/framework/System/how-this-works.md.
2. Create docs/components/<Component>/System/how-this-works.md for each migrated component.
3. Mirror the source structure inside docs/.
4. Document every first-party ownership folder.
5. Add mermaid sequence/flowchart diagrams where flow order matters.
6. Add "Debug first" section.
7. Add "What user sees" section.
8. Add "Failure path" section.
9. Add "What gets written/changed/executed" section.
10. Validate docs before accepting migration.
```

Tvoj documentation governance je vrlo strog: `docs/` je single canonical location, mora mirrorovati source strukturu,
dokumentacija mora objasniti zašto nešto postoji, gde se debugguje i kako se konkretan trigger pretvara u rezultat.

---

# Phase 10: Quality gates per increment

Svaki increment mora proći gate. Bez toga framework postaje “lep tree, slab sistem”.

## Per-increment gate

```text
1. composer validate
2. composer dump-autoload
3. php -l for changed PHP files
4. PHPUnit targeted tests
5. Full PHPUnit suite when migration affects shared paths
6. Static analysis
7. Code style
8. Architecture/gov review
9. Documentation validation
10. kluster.ai code verification when available and configured
```

Plan treba da uključi kluster verification jer postoje pravila koja zahtevaju kluster posle file
creation/modification/code change i u planning mode-u.

## Governance review output

Za svaki veći slice:

```text
EVIDENCE/<slice>/review.md
```

Mora sadržati:

```text
GOVERNANCE INVENTORY
GOVERNANCE COMPLIANCE REPORT
GOVERNANCE FINDINGS
GOVERNANCE EXCEPTIONS
GOVERNANCE COVERAGE SUMMARY
DECISIONS-LOG
NEXT STEPS
```

Ovo nije birokratija. Ovo sprečava AI da “odradi refactor” i propusti pravila. Code review dokument eksplicitno traži
compliance matrix i tačno šta fali, gde fali, zašto je bitno i šta je sledeći action.

---

# Phase 11: Remove obsolete structure

Ovo je faza koju mnogi preskoče. Ne smeš.

## Tasks

```text
1. Identify old duplicate owners.
2. Identify compatibility aliases.
3. Mark temporary aliases with explicit deprecation window.
4. Remove obsolete folders after migration is complete.
5. Remove dead docs.
6. Remove duplicate tests.
7. Remove old namespace paths.
8. Remove old bootstrap entrypoints if replaced.
9. Update composer autoload.
10. Verify no old path is still referenced.
```

## Rule

```text
If a new owner exists, the old owner must either:
1. be deleted,
2. be explicitly deprecated with removal date,
3. or be documented as a compatibility bridge.
```

Bez ovoga dobijaš dve arhitekture u istom repo-u. To je najgora varijanta.

---

# Phase 12: Final acceptance

## Final acceptance checklist

```text
Architecture:
[ ] framework/System exists and owns lifecycle.
[ ] components/*/System exists and owns reusable capabilities.
[ ] PublicSurface exists only where justified.
[ ] Runtime adapters are isolated.
[ ] Request scope exists.
[ ] ResetApplicationState exists.
[ ] No runtime-specific API leaks into core components.
[ ] No generic Core/Shared/Helpers/Managers bucket is introduced.

Testing:
[ ] Characterization tests protect migrated behavior.
[ ] Unit tests cover meaningful units.
[ ] Integration tests prove framework/component collaboration.
[ ] Feature tests prove HTTP, console, worker boot paths.
[ ] Contract tests prove runtime adapters and storage adapters.
[ ] State leak tests exist for worker requests.

Documentation:
[ ] docs/ is canonical.
[ ] docs mirror source structure.
[ ] how-this-works.md exists for ownership folders.
[ ] Mermaid diagrams exist for sequential flows.
[ ] Debug-first guidance exists.
[ ] PublicSurface docs explain stable API and breaking changes.

Quality:
[ ] composer validate passes.
[ ] autoload passes.
[ ] static analysis passes.
[ ] code style passes.
[ ] tests pass.
[ ] governance review passes.
[ ] kluster review is run when configured.
```

---

# Kill criteria

Ovo bih stavio u plan da znaš kada migracija ide loše.

```text
Stop and redesign the migration approach if:

1. One migrated behavior requires touching 4+ unrelated core areas.
2. PublicSurface starts containing real logic.
3. Runtime adapter code appears inside Request/Response/Router/Session/Auth components.
4. Request state cannot be reset deterministically.
5. Tests need real time, real network, or global state to pass.
6. Docs cannot explain one exact path from trigger to result.
7. Foundation starts collecting unrelated helpers.
8. New architecture requires more explanation than old architecture.
```

Ako se desi bilo šta od ovoga, nije “mali problem”. To znači da migration axis nije čist.

---

# First concrete implementation order

Ovo bih dao Codex-u/agentu kao prvu realnu sekvencu:

```text
1. Create docs/decisions ADRs for:
   - runtime-agnostic framework
   - framework/System lifecycle owner
   - components as reusable capabilities
   - PublicSurface boundary
   - request scope and state reset

2. Create framework/System skeleton:
   - PublicSurface/
   - Flows/
   - Capabilities/
   - Configuration/
   - Foundation/

3. Implement minimal:
   - PublicSurface/Avax.php
   - Capabilities/Runtime/RuntimeInterface.php
   - Capabilities/Runtime/RuntimeContext.php
   - Capabilities/Runtime/RuntimeState.php
   - Capabilities/RequestScope/RequestScope.php
   - Capabilities/RequestScope/RequestScopeStore.php
   - Flows/BootApplication/BootApplication.php
   - Configuration/BuildApplication/BuildApplication.php

4. Add tests:
   - RuntimeContextTest
   - RuntimeStateTest
   - RequestScopeTest
   - BootApplicationTest

5. Add docs:
   - docs/framework/System/how-this-works.md
   - docs/framework/System/PublicSurface/how-this-works.md
   - docs/framework/System/Capabilities/Runtime/how-this-works.md
   - docs/framework/System/Capabilities/RequestScope/how-this-works.md

6. Run:
   - composer dump-autoload
   - targeted PHPUnit tests
   - static analysis
   - code style
   - governance review
   - kluster verification if available
```

---

# Codex-ready prompt

Ovo možeš direktno da koristiš.

```text
You are working on Avax, a modern runtime-agnostic PHP framework.

Goal:
Migrate Avax toward a runtime-agnostic architecture without performing a big-bang rewrite.

Primary axis:
Avax = framework runtime/lifecycle owner + reusable components/capabilities.

Target shape:
framework/System/
  PublicSurface/
  Flows/
  Capabilities/
  Configuration/
  Foundation/

components/<Component>/System/
  PublicSurface/
  Flows/
  Capabilities/
  Configuration/
  Foundation/

Strict rules:
- Follow every how-to-*.md governance document.
- Discover and list governance documents before review.
- Do not cherry-pick rules.
- Do not move behavior without characterization tests.
- Do not leak runtime-specific APIs into core components.
- Do not put behavior inside PublicSurface.
- PublicSurface receives public calls and delegates.
- Flows execute behavior.
- Capabilities provide reusable mechanisms.
- Configuration assembles.
- Foundation contains tiny neutral primitives only.
- Request-specific state must live in explicit request scope.
- Long-lived worker runtimes must not leak state between requests.
- Every increment must leave the repository runnable.

First implementation slice:
1. Add ADR docs for the migration direction.
2. Create framework/System skeleton.
3. Implement minimal runtime abstractions:
   - RuntimeInterface
   - RuntimeContext
   - RuntimeState
   - RuntimeRequest
   - RuntimeResponse
   - RuntimeResult
4. Implement minimal request scope:
   - RequestScope
   - RequestScopeInterface
   - RequestScopeId
   - RequestScopeStore
5. Implement minimal BootApplication flow and BuildApplication configuration owner.
6. Add behavior-focused unit tests.
7. Add docs mirror under docs/framework/System.
8. Run targeted tests and quality gates.
9. Run kluster code verification if available and configured.
10. Produce EVIDENCE/review.md with:
   - GOVERNANCE INVENTORY
   - GOVERNANCE COMPLIANCE REPORT
   - GOVERNANCE FINDINGS
   - GOVERNANCE EXCEPTIONS
   - GOVERNANCE COVERAGE SUMMARY
   - DECISIONS-LOG
   - NEXT STEPS

Do not migrate existing components yet.
This first slice only establishes the new framework runtime axis.
```

---

Da. Evo **kompletan target project tree za Avax v1 architecture migration**, sa objašnjenjem i ToDo listom.

Ovo tretiraj kao **ciljni arhitektonski blueprint**, ne kao “napravi sve odjednom”. Tvoj governance traži
flow/capability ownership, repo root vs system root razliku, čitljivost od vrha ka dnu i recursive/fractal ownership,
ali takođe upozorava da se struktura ne sme praviti mehanički ili dekorativno.

---

## 1. Target project tree

```text
avax/
  AGENTS.md
  README.md
  CHANGELOG.md
  LICENSE
  composer.json
  composer.lock
  phpunit.xml
  phpstan.neon
  psalm.xml
  rector.php
  .env.example
  .gitignore

  bin/
    avax

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
          Runtime.php

      Flows/
        BootApplication/
          BootApplication.php
          LoadEnvironment.php
          ReadApplicationPaths.php
          BuildApplicationContainer.php
          RegisterConfiguredComponents.php
          BootComponentProviders.php
          BuildApplicationState.php
          ApplicationBootFailed.php

        HandleIncomingHttp/
          HandleIncomingHttp.php
          CreateHttpRuntimeContext.php
          OpenHttpRequestScope.php
          ReadIncomingHttpRequest.php
          MatchHttpRoute.php
          RunHttpRoute.php
          BuildHttpResponse.php
          SendHttpResponse.php
          TerminateHttpRequest.php
          CloseHttpRequestScope.php
          IncomingHttpFailed.php

        StartWorker/
          StartWorker.php
          BootWorkerApplication.php
          StartWorkerLoop.php
          StopWorker.php
          WorkerStartFailed.php
          WorkerStopFailed.php

        HandleWorkerRequest/
          HandleWorkerRequest.php
          ReceiveWorkerRequest.php
          CreateWorkerRuntimeContext.php
          OpenWorkerRequestScope.php
          RunWorkerRequest.php
          SendWorkerResponse.php
          CloseWorkerRequestScope.php
          WorkerRequestFailed.php

        ResetApplicationState/
          ResetApplicationState.php
          ResetRequestScope.php
          ResetRuntimeContext.php
          ResetDiagnosticsContext.php
          ResetComponentState.php
          BuildStateResetReport.php
          StateResetFailed.php

        RunConsoleCommand/
          RunConsoleCommand.php
          ReadConsoleInput.php
          ResolveConsoleCommand.php
          ExecuteConsoleCommand.php
          WriteConsoleOutput.php
          ConsoleCommandFailed.php

        HandleException/
          HandleException.php
          ClassifyFrameworkFailure.php
          RenderHttpFailure.php
          RenderConsoleFailure.php
          ReportFrameworkFailure.php
          FrameworkExceptionHandlingFailed.php

        ShutdownRuntime/
          ShutdownRuntime.php
          FlushTerminableWork.php
          CloseRuntimeResources.php
          RuntimeShutdownFailed.php

      Capabilities/
        Application/
          Application.php
          ApplicationInterface.php
          ApplicationState.php
          ApplicationMode.php
          ApplicationEnvironment.php
          ApplicationPaths.php
          ApplicationProvider.php
          ApplicationProviderInterface.php

        Runtime/
          Runtime.php
          RuntimeInterface.php
          RuntimeName.php
          RuntimeState.php
          RuntimeContext.php
          RuntimeRequest.php
          RuntimeResponse.php
          RuntimeResult.php
          RuntimeFailure.php

          PhpFpm/
            PhpFpmRuntime.php
            PhpFpmRequestReader.php
            PhpFpmResponseSender.php

          Cli/
            CliRuntime.php
            CliInputReader.php
            CliOutputWriter.php

          Worker/
            WorkerRuntimeInterface.php
            WorkerLoop.php
            WorkerRequest.php
            WorkerResponse.php
            WorkerLifecycle.php
            WorkerFailure.php

          Adapters/
            FrankenPhp/
              FrankenPhpRuntime.php
              FrankenPhpWorkerLoop.php
              FrankenPhpRequestReader.php
              FrankenPhpResponseSender.php

            RoadRunner/
              RoadRunnerRuntime.php
              RoadRunnerWorkerLoop.php
              RoadRunnerRequestReader.php
              RoadRunnerResponseSender.php

            Swoole/
              SwooleRuntime.php
              SwooleHttpServerAdapter.php
              SwooleRequestReader.php
              SwooleResponseSender.php

            Workerman/
              WorkermanRuntime.php
              WorkermanServerAdapter.php
              WorkermanRequestReader.php
              WorkermanResponseSender.php

        RequestScope/
          RequestScope.php
          RequestScopeInterface.php
          RequestScopeId.php
          RequestScopeStore.php
          ScopedValue.php
          OpenRequestScope.php
          CloseRequestScope.php
          RequestScopeAlreadyClosed.php
          RequestScopeNotOpen.php

        StateReset/
          ResettableState.php
          StateResetRegistry.php
          StateResetReport.php
          StateResetFailure.php

        ComponentRegistry/
          ComponentRegistry.php
          ComponentDefinition.php
          RegisteredComponent.php
          ComponentProviderInterface.php
          ComponentAlreadyRegistered.php
          ComponentNotRegistered.php

        HttpApplication/
          HttpApplication.php
          HttpApplicationInterface.php
          HttpApplicationState.php
          HttpEntrypoint.php
          HttpTerminator.php

        ConsoleApplication/
          ConsoleApplication.php
          ConsoleApplicationInterface.php
          ConsoleEntrypoint.php
          ConsoleCommandRegistry.php
          ConsoleCommandNotFound.php

        Diagnostics/
          Diagnostics.php
          DiagnosticContext.php
          CorrelationId.php
          TraceId.php
          RuntimeTimeline.php
          RuntimeEvent.php
          DiagnosticReporterInterface.php
          NullDiagnosticReporter.php

      Configuration/
        BuildApplication/
          BuildApplication.php
          ApplicationBuilder.php
          ApplicationConfiguration.php
          ApplicationConfigurationFailed.php

        ConfigureRuntime/
          ConfigureRuntime.php
          RuntimeConfiguration.php
          RuntimeAdapterConfiguration.php
          RuntimeAdapterNotConfigured.php

        RegisterComponents/
          RegisterComponents.php
          ComponentRegistration.php
          ComponentConfiguration.php

        RegisterProviders/
          RegisterProviders.php
          ProviderRegistration.php
          ProviderBootFailed.php

        LoadConfiguration/
          LoadConfiguration.php
          ConfigurationSource.php
          ConfigurationRepository.php
          EnvironmentConfigurationSource.php
          PhpFileConfigurationSource.php
          ConfigurationNotFound.php
          InvalidConfiguration.php

      Foundation/
        Time/
          Clock.php
          SystemClock.php
          FrozenClock.php
          Timestamp.php

        Paths/
          ProjectPath.php
          RuntimePath.php
          ConfigurationPath.php
          StoragePath.php

        Environment/
          EnvironmentName.php
          EnvironmentVariable.php
          ReadEnvironmentVariable.php

        Version/
          AvaxVersion.php
          SemanticVersion.php

        Failure/
          FrameworkFailure.php
          FrameworkBootFailed.php
          FrameworkMisconfigured.php
```

---

## 2. Components tree

Ovo su reusable framework capabilities. Svaka komponenta ima isti arhitektonski jezik, ali ne sme se svaka veštački
širiti. Ako komponenta nema realan `PublicSurface/`, `Foundation/` ili poseban sub-capability, ne pravi ga dekorativno.

```text
components/
  Container/
    System/
      PublicSurface/
        Container.php
        ContainerInterface.php

      Flows/
        CreateContainer/
          CreateContainer.php
          ApplyContainerConfiguration.php
          BuildRootScope.php
          ContainerCreationFailed.php

        RegisterBinding/
          RegisterBinding.php
          BindingDefinition.php
          BindingAlreadyRegistered.php

        ResolveService/
          ResolveService.php
          BuildResolutionPlan.php
          ResolveConstructorDependencies.php
          BuildServiceInstance.php
          ServiceNotFound.php
          ServiceResolutionFailed.php

        CallFunction/
          CallFunction.php
          ResolveCallArguments.php
          CallableInvocationFailed.php

        OpenScope/
          OpenScope.php
          ScopeAlreadyOpen.php

        CloseScope/
          CloseScope.php
          ScopeNotOpen.php

      Capabilities/
        Bindings/
          Binding.php
          BindingId.php
          BindingRegistry.php
          BindingLifetime.php
          BindingFactory.php

        Resolution/
          ResolutionContext.php
          ResolutionPlan.php
          ResolutionStack.php
          ResolvedService.php
          CircularDependencyDetected.php

        Scopes/
          Scope.php
          ScopeInterface.php
          ScopeId.php
          ScopeStore.php
          ScopeLifetime.php
          ScopedValue.php

        Providers/
          ServiceProvider.php
          ServiceProviderInterface.php
          ProviderRegistry.php

        Autowiring/
          AutowireClass.php
          ReadConstructorParameters.php
          ResolveParameterValue.php
          AutowiringFailed.php

      Configuration/
        ContainerBuilder.php
        ContainerConfiguration.php
        ContainerProvider.php

      Foundation/
        Failure/
          ContainerFailure.php


  Config/
    System/
      PublicSurface/
        Config.php
        ConfigInterface.php

      Flows/
        LoadConfiguration/
          LoadConfiguration.php
          ReadConfigurationFiles.php
          MergeConfigurationValues.php
          ValidateConfiguration.php
          ConfigurationLoadFailed.php

        ReadConfigurationValue/
          ReadConfigurationValue.php
          ResolveConfigurationKey.php
          ConfigurationValueNotFound.php

      Capabilities/
        Repository/
          ConfigurationRepository.php
          ConfigurationValue.php
          ConfigurationKey.php

        Sources/
          ConfigurationSource.php
          PhpArrayConfigurationSource.php
          EnvironmentConfigurationSource.php

      Configuration/
        ConfigBuilder.php
        ConfigProvider.php

      Foundation/
        Failure/
          ConfigFailure.php


  Events/
    System/
      PublicSurface/
        Events.php
        EventsInterface.php

      Flows/
        DispatchEvent/
          DispatchEvent.php
          ResolveEventListeners.php
          InvokeEventListener.php
          EventDispatchFailed.php

        SubscribeToEvent/
          SubscribeToEvent.php
          RegisterEventListener.php
          EventSubscriptionFailed.php

      Capabilities/
        ListenerRegistry/
          ListenerRegistry.php
          ListenerDefinition.php
          ListenerPriority.php

        EventBus/
          EventBus.php
          EventBusInterface.php

      Configuration/
        EventsBuilder.php
        EventsProvider.php

      Foundation/
        Failure/
          EventsFailure.php


  Request/
    System/
      PublicSurface/
        Request.php
        RequestInterface.php

      Flows/
        CreateRequestFromGlobals/
          CreateRequestFromGlobals.php
          ReadServerParameters.php
          ReadQueryParameters.php
          ReadUploadedFiles.php
          ReadRequestBody.php
          GlobalsRequestCreationFailed.php

        CreateRequestFromRuntime/
          CreateRequestFromRuntime.php
          NormalizeRuntimeHeaders.php
          NormalizeRuntimeBody.php
          RuntimeRequestCreationFailed.php

        ReadRequestInput/
          ReadRequestInput.php
          ReadRouteInput.php
          ReadQueryInput.php
          ReadBodyInput.php
          ReadFileInput.php

      Capabilities/
        Headers/
          RequestHeaders.php
          HeaderName.php
          HeaderValue.php
          NormalizeHeaders.php

        Body/
          RequestBody.php
          ParsedBody.php
          RawBody.php
          ParseJsonBody.php
          ParseFormBody.php

        Files/
          UploadedFile.php
          UploadedFiles.php
          NormalizeUploadedFiles.php

        Network/
          ClientAddress.php
          TrustedProxyPolicy.php
          ResolveClientAddress.php

        Uri/
          RequestUri.php
          RequestTarget.php
          QueryString.php

      Configuration/
        RequestBuilder.php
        RequestConfiguration.php
        RequestProvider.php

      Foundation/
        Failure/
          RequestFailure.php
          InvalidRequestBody.php


  Response/
    System/
      PublicSurface/
        Response.php
        ResponseInterface.php

      Flows/
        BuildResponse/
          BuildResponse.php
          NormalizeResponseBody.php
          NormalizeResponseHeaders.php
          ResponseBuildFailed.php

        SendResponse/
          SendResponse.php
          SendStatusLine.php
          SendHeaders.php
          SendBody.php
          ResponseSendFailed.php

        CreateJsonResponse/
          CreateJsonResponse.php
          EncodeJsonResponseBody.php
          JsonResponseCreationFailed.php

        CreateRedirectResponse/
          CreateRedirectResponse.php
          BuildRedirectHeaders.php
          RedirectResponseCreationFailed.php

      Capabilities/
        Headers/
          ResponseHeaders.php
          ResponseHeader.php
          AppendHeader.php
          ReplaceHeader.php

        Body/
          ResponseBody.php
          StringBody.php
          StreamBody.php
          JsonBody.php

        Status/
          StatusCode.php
          StatusText.php
          HttpStatus.php

        Streaming/
          StreamingResponse.php
          StreamEmitter.php
          StreamSendFailed.php

      Configuration/
        ResponseBuilder.php
        ResponseProvider.php

      Foundation/
        Failure/
          ResponseFailure.php


  Router/
    System/
      PublicSurface/
        Router.php
        RouterInterface.php

        Facades/
          Route.php

      Flows/
        RegisterRoute/
          RegisterRoute.php
          NormalizeRouteDefinition.php
          RouteRegistrationFailed.php

        MatchRoute/
          MatchRoute.php
          MatchStaticRoute.php
          MatchDynamicRoute.php
          RouteNotFound.php

        DispatchRoute/
          DispatchRoute.php
          ResolveRouteAction.php
          InvokeRouteAction.php
          RouteDispatchFailed.php

        GenerateUrl/
          GenerateUrl.php
          ResolveNamedRoute.php
          BuildRouteUrl.php
          UrlGenerationFailed.php

      Capabilities/
        RouteCollection/
          RouteCollection.php
          RouteDefinition.php
          RouteName.php
          RouteMethod.php

        RoutePattern/
          RoutePattern.php
          CompiledRoutePattern.php
          CompileRoutePattern.php
          RouteParameter.php

        RouteGroups/
          RouteGroup.php
          RouteGroupPrefix.php
          RouteGroupMiddleware.php

        MiddlewarePipeline/
          RouteMiddlewarePipeline.php
          RouteMiddleware.php
          RunRouteMiddleware.php

      Configuration/
        RouterBuilder.php
        RouterProvider.php

      Foundation/
        Failure/
          RouterFailure.php


  Middleware/
    System/
      PublicSurface/
        Middleware.php
        MiddlewareInterface.php

      Flows/
        RunMiddlewarePipeline/
          RunMiddlewarePipeline.php
          ResolveNextMiddleware.php
          MiddlewarePipelineFailed.php

      Capabilities/
        Pipeline/
          MiddlewarePipeline.php
          MiddlewareStack.php
          MiddlewarePriority.php

      Configuration/
        MiddlewareBuilder.php
        MiddlewareProvider.php

      Foundation/
        Failure/
          MiddlewareFailure.php


  Console/
    System/
      PublicSurface/
        Console.php
        ConsoleInterface.php

      Flows/
        RegisterConsoleCommand/
          RegisterConsoleCommand.php
          ConsoleCommandRegistrationFailed.php

        RunConsoleCommand/
          RunConsoleCommand.php
          ParseConsoleInput.php
          ResolveConsoleCommand.php
          ExecuteConsoleCommand.php
          ConsoleCommandExecutionFailed.php

      Capabilities/
        CommandRegistry/
          ConsoleCommand.php
          ConsoleCommandInterface.php
          ConsoleCommandName.php
          ConsoleCommandRegistry.php

        Input/
          ConsoleInput.php
          ConsoleArgument.php
          ConsoleOption.php

        Output/
          ConsoleOutput.php
          ConsoleOutputWriter.php

      Configuration/
        ConsoleBuilder.php
        ConsoleProvider.php

      Foundation/
        Failure/
          ConsoleFailure.php


  Cache/
    System/
      PublicSurface/
        Cache.php
        CacheInterface.php

        Facades/
          CacheFacade.php

        Targets/
          RuntimeCacheTarget.php
          CompiledCacheTarget.php

      Flows/
        ReadCachedValue/
          ReadCachedValue.php
          ResolveCacheKey.php
          CachedValueNotFound.php

        StoreCachedValue/
          StoreCachedValue.php
          NormalizeCacheTtl.php
          CacheStoreFailed.php

        RememberCachedValue/
          RememberCachedValue.php
          ReadExistingCachedValue.php
          StoreComputedCachedValue.php
          RememberCachedValueFailed.php

        ForgetCachedValue/
          ForgetCachedValue.php
          CacheForgetFailed.php

        ClearCache/
          ClearCache.php
          CacheClearFailed.php

        CompileCachedValue/
          CompileCachedValue.php
          CompiledCacheWriteFailed.php

        ReadCompiledCache/
          ReadCompiledCache.php
          CompiledCacheReadFailed.php

      Capabilities/
        Stores/
          CacheStore.php
          CacheStoreInterface.php
          ArrayCacheStore.php
          FileCacheStore.php

        Keys/
          CacheKey.php
          CacheKeyPrefix.php
          NormalizeCacheKey.php

        Ttl/
          CacheTtl.php
          Forever.php
          ExpiresAt.php

        Serialization/
          CacheSerializer.php
          NativeCacheSerializer.php

        CompiledCache/
          CompiledCache.php
          CompiledCacheArtifact.php
          CompiledCacheStore.php

      Configuration/
        CacheBuilder.php
        CacheConfiguration.php
        CacheProvider.php

      Foundation/
        Failure/
          CacheFailure.php
```

Napomena: Cache već u trenutnom Avax materijalu ide u smeru `PublicSurface/`, `Flows/`, `Capabilities/`, `Foundation/`,
`Configuration/`, što znači da ovaj model nije teorijska fantazija nego formalizacija pravca koji već postoji.

```text
  Session/
    System/
      PublicSurface/
        Session.php
        SessionInterface.php
        SessionScope.php

      Flows/
        StartSession/
          StartSession.php
          CreateSessionId.php
          LoadSessionState.php
          SessionStartFailed.php

        ReadSessionValue/
          ReadSessionValue.php
          ResolveSessionKey.php
          SessionValueNotFound.php

        StoreSessionValue/
          StoreSessionValue.php
          WriteSessionState.php
          SessionWriteFailed.php

        ForgetSessionValue/
          ForgetSessionValue.php
          SessionForgetFailed.php

        ClearSession/
          ClearSession.php
          SessionClearFailed.php

        RegenerateSession/
          RegenerateSession.php
          RotateSessionId.php
          SessionRegenerationFailed.php

        DestroySession/
          DestroySession.php
          ClearSessionState.php
          SessionDestroyFailed.php

      Capabilities/
        State/
          SessionState.php
          SessionId.php
          SessionKey.php
          SessionValue.php

        Storage/
          SessionStore.php
          SessionStoreInterface.php
          ArraySessionStore.php
          FileSessionStore.php

        Cookie/
          SessionCookie.php
          SessionCookiePolicy.php
          BuildSessionCookie.php

        Security/
          SessionFingerprint.php
          SessionFixationProtection.php
          SessionIdValidator.php

      Configuration/
        SessionBuilder.php
        SessionConfiguration.php
        SessionProvider.php

      Foundation/
        Failure/
          SessionFailure.php


  Filesystem/
    System/
      PublicSurface/
        Filesystem.php
        FilesystemInterface.php

      Flows/
        ReadFile/
          ReadFile.php
          ResolveFilePath.php
          FileNotFound.php

        WriteFile/
          WriteFile.php
          EnsureTargetDirectoryExists.php
          FileWriteFailed.php

        DeleteFile/
          DeleteFile.php
          FileDeleteFailed.php

        CopyFile/
          CopyFile.php
          FileCopyFailed.php

        MoveFile/
          MoveFile.php
          FileMoveFailed.php

        CreateDirectory/
          CreateDirectory.php
          DirectoryCreateFailed.php

        DeleteDirectory/
          DeleteDirectory.php
          DirectoryDeleteFailed.php

      Capabilities/
        Disks/
          Disk.php
          DiskInterface.php
          LocalDisk.php

        Paths/
          FilePath.php
          DirectoryPath.php
          NormalizePath.php

        Permissions/
          FilePermissions.php
          DirectoryPermissions.php

      Configuration/
        FilesystemBuilder.php
        FilesystemConfiguration.php
        FilesystemProvider.php

      Foundation/
        Failure/
          FilesystemFailure.php


  Database/
    System/
      PublicSurface/
        Database.php
        DatabaseInterface.php

        Facades/
          DB.php
          Schema.php
          Migration.php

      Flows/
        ConnectToDatabase/
          ConnectToDatabase.php
          ResolveConnectionConfiguration.php
          OpenDatabaseConnection.php
          DatabaseConnectionFailed.php

        RunDatabaseQuery/
          RunDatabaseQuery.php
          PrepareDatabaseQuery.php
          BindQueryParameters.php
          ExecuteDatabaseQuery.php
          DatabaseQueryFailed.php

        RunDatabaseTransaction/
          RunDatabaseTransaction.php
          BeginTransaction.php
          CommitTransaction.php
          RollbackTransaction.php
          DatabaseTransactionFailed.php

        RunDatabaseMigration/
          RunDatabaseMigration.php
          ReadPendingMigrations.php
          ExecuteMigration.php
          RecordExecutedMigration.php
          DatabaseMigrationFailed.php

        BuildDatabaseSchema/
          BuildDatabaseSchema.php
          CreateTable.php
          DropTable.php
          AlterTable.php
          DatabaseSchemaBuildFailed.php

      Capabilities/
        Connections/
          DatabaseConnection.php
          DatabaseConnectionInterface.php
          ConnectionName.php
          ConnectionConfiguration.php
          ConnectionRegistry.php

        QueryBuilder/
          QueryBuilder.php
          Query.php
          QueryBindings.php
          QueryResult.php

        Transactions/
          Transaction.php
          TransactionManager.php
          TransactionLevel.php

        Migrations/
          Migration.php
          MigrationRepository.php
          MigrationFile.php
          MigrationBatch.php

        Schema/
          SchemaBuilder.php
          Blueprint.php
          TableName.php
          ColumnDefinition.php
          IndexDefinition.php

        Telemetry/
          DatabaseEvent.php
          QueryExecuted.php
          QueryFailed.php
          DatabaseTelemetry.php

      Configuration/
        DatabaseBuilder.php
        DatabaseConfiguration.php
        DatabaseProvider.php

      Foundation/
        Failure/
          DatabaseFailure.php


  Validation/
    System/
      PublicSurface/
        Validation.php
        ValidationInterface.php
        Validator.php

      Flows/
        ValidateInput/
          ValidateInput.php
          ReadValidationRules.php
          RunValidationRules.php
          BuildValidationResult.php
          ValidationFailed.php

      Capabilities/
        Rules/
          ValidationRule.php
          Required.php
          StringRule.php
          IntegerRule.php
          EmailRule.php
          MinLength.php
          MaxLength.php

        Result/
          ValidationResult.php
          ValidationError.php
          ValidationErrors.php

      Configuration/
        ValidationBuilder.php
        ValidationProvider.php

      Foundation/
        Failure/
          ValidationFailure.php


  Auth/
    System/
      PublicSurface/
        Auth.php
        AuthInterface.php

        Facades/
          AuthFacade.php

      Flows/
        Login/
          Login.php
          Credentials.php
          FindUserByCredentials.php
          VerifyPassword.php
          StartAuthenticatedSession.php
          AuthenticationFailed.php

        Logout/
          Logout.php
          ClearAuthenticatedIdentity.php
          LogoutFailed.php

        Register/
          Register.php
          RegistrationData.php
          ValidateRegistrationData.php
          HashRegisteredPassword.php
          CreateRegisteredUser.php
          RegistrationFailed.php

        ReadCurrentUser/
          ReadCurrentUser.php
          ResolveAuthenticatedIdentity.php
          CurrentUserNotFound.php

        RequireAuthentication/
          RequireAuthentication.php
          Unauthenticated.php

        RequirePermission/
          RequirePermission.php
          PermissionDenied.php

        ChangePassword/
          ChangePassword.php
          VerifyCurrentPassword.php
          HashNewPassword.php
          SaveNewPassword.php
          PasswordChangeFailed.php

      Capabilities/
        Identity/
          User.php
          UserId.php
          UserEmail.php
          AuthenticatedIdentity.php
          IdentityBackend.php

        PasswordHashing/
          PasswordHasher.php
          PasswordHasherInterface.php
          PasswordHash.php

        Access/
          Permission.php
          Role.php
          AccessPolicy.php

        Tokens/
          AccessToken.php
          RefreshToken.php
          TokenCodec.php
          TokenStore.php

        Mfa/
          MfaChallenge.php
          MfaVerifier.php
          MfaFailure.php

      Configuration/
        AuthBuilder.php
        AuthConfiguration.php
        AuthProvider.php

      Foundation/
        Failure/
          AuthFailure.php


  View/
    System/
      PublicSurface/
        View.php
        ViewInterface.php

      Flows/
        RenderView/
          RenderView.php
          ResolveViewTemplate.php
          RenderViewData.php
          ViewRenderFailed.php

      Capabilities/
        Templates/
          TemplateName.php
          TemplatePath.php
          TemplateRepository.php

        Data/
          ViewData.php
          ViewVariable.php

      Configuration/
        ViewBuilder.php
        ViewProvider.php

      Foundation/
        Failure/
          ViewFailure.php


  Logging/
    System/
      PublicSurface/
        Logging.php
        LoggingInterface.php
        Logger.php

      Flows/
        WriteLogEntry/
          WriteLogEntry.php
          FormatLogEntry.php
          SendLogEntry.php
          LogWriteFailed.php

      Capabilities/
        Logger/
          LoggerInterface.php
          LogLevel.php
          LogEntry.php

        Channels/
          LogChannel.php
          ChannelRegistry.php

        Formatters/
          LogFormatter.php
          JsonLogFormatter.php
          LineLogFormatter.php

        Writers/
          LogWriter.php
          FileLogWriter.php
          StdoutLogWriter.php

      Configuration/
        LoggingBuilder.php
        LoggingProvider.php

      Foundation/
        Failure/
          LoggingFailure.php
```

---

## 3. Docs tree

Po tvom dokumentacionom governance-u, `docs/` mora biti jedina canonical lokacija za dokumentaciju, mora mirrorovati
source strukturu i mora objašnjavati intent, odgovornost, posledice i debug path, ne samo prepričavati kod.

```text
docs/
  README.md

  architecture/
    avax-framework-architecture.md
    runtime-agnostic-framework.md
    component-model.md
    request-scope-and-state-reset.md
    public-surface-policy.md
    naming-and-ownership.md
    migration-strategy.md

  decisions/
    0001-avax-is-runtime-agnostic-framework.md
    0002-framework-system-owns-runtime-lifecycle.md
    0003-components-are-reusable-capabilities.md
    0004-public-surface-is-stable-api-boundary.md
    0005-runtime-adapters-must-not-leak-into-core.md
    0006-request-state-must-be-scoped.md
    0007-docs-mirror-source-structure.md

  governance/
    how-to-architecture.md
    how-to-architecture-extension.md
    how-to-code-review.md
    how-to-code-style.md
    how-to-coding-standards.md
    how-to-clean-code.md
    how-to-document.md
    how-to-unit-test.md

  framework/
    System/
      how-this-works.md

      PublicSurface/
        how-this-works.md
        Avax.md
        AvaxInterface.md

        Http/
          how-this-works.md
          HttpKernel.md
          HttpKernelInterface.md

        Console/
          how-this-works.md
          ConsoleKernel.md
          ConsoleKernelInterface.md

        Runtime/
          how-this-works.md
          RuntimeKernel.md
          RuntimeKernelInterface.md

        Facades/
          how-this-works.md
          App.md
          Runtime.md

      Flows/
        how-this-works.md

        BootApplication/
          how-this-works.md
          BootApplication.md

        HandleIncomingHttp/
          how-this-works.md
          HandleIncomingHttp.md

        StartWorker/
          how-this-works.md
          StartWorker.md

        HandleWorkerRequest/
          how-this-works.md
          HandleWorkerRequest.md

        ResetApplicationState/
          how-this-works.md
          ResetApplicationState.md

        RunConsoleCommand/
          how-this-works.md
          RunConsoleCommand.md

        HandleException/
          how-this-works.md
          HandleException.md

        ShutdownRuntime/
          how-this-works.md
          ShutdownRuntime.md

      Capabilities/
        how-this-works.md

        Application/
          how-this-works.md
          Application.md

        Runtime/
          how-this-works.md
          Runtime.md
          RuntimeInterface.md
          RuntimeContext.md
          RuntimeState.md

          Adapters/
            how-this-works.md
            FrankenPhp.md
            RoadRunner.md
            Swoole.md
            Workerman.md

        RequestScope/
          how-this-works.md
          RequestScope.md

        StateReset/
          how-this-works.md
          StateReset.md

        ComponentRegistry/
          how-this-works.md
          ComponentRegistry.md

        Diagnostics/
          how-this-works.md
          Diagnostics.md

      Configuration/
        how-this-works.md

        BuildApplication/
          how-this-works.md
          BuildApplication.md

        ConfigureRuntime/
          how-this-works.md
          ConfigureRuntime.md

        RegisterComponents/
          how-this-works.md
          RegisterComponents.md

        RegisterProviders/
          how-this-works.md
          RegisterProviders.md

        LoadConfiguration/
          how-this-works.md
          LoadConfiguration.md

      Foundation/
        how-this-works.md

        Time/
          how-this-works.md
          Clock.md

        Paths/
          how-this-works.md
          ProjectPath.md

        Environment/
          how-this-works.md
          EnvironmentName.md

        Version/
          how-this-works.md
          AvaxVersion.md

        Failure/
          how-this-works.md
          FrameworkFailure.md

  components/
    Container/
      System/
        how-this-works.md
        PublicSurface/how-this-works.md
        Flows/how-this-works.md
        Capabilities/how-this-works.md
        Configuration/how-this-works.md
        Foundation/how-this-works.md

    Config/
      System/
        how-this-works.md

    Events/
      System/
        how-this-works.md

    Request/
      System/
        how-this-works.md

    Response/
      System/
        how-this-works.md

    Router/
      System/
        how-this-works.md

    Middleware/
      System/
        how-this-works.md

    Console/
      System/
        how-this-works.md

    Cache/
      System/
        how-this-works.md

    Session/
      System/
        how-this-works.md

    Filesystem/
      System/
        how-this-works.md

    Database/
      System/
        how-this-works.md

    Validation/
      System/
        how-this-works.md

    Auth/
      System/
        how-this-works.md

    View/
      System/
        how-this-works.md

    Logging/
      System/
        how-this-works.md
```

---

## 4. Tests tree

Test tree mora jasno razdvajati `Unit`, `Integration`, `Feature`, `Contract`, `Support`. Testovi nisu coverage
decoration. Oni su behavior specification i moraju dokazati ponašanje iz spoljne perspektive, ne privatne
implementacione detalje.

```text
tests/
  Unit/
    Framework/
      System/
        PublicSurface/
          AvaxTest.php
          HttpKernelTest.php
          ConsoleKernelTest.php
          RuntimeKernelTest.php

        Flows/
          BootApplication/
            BootApplicationTest.php

          HandleIncomingHttp/
            HandleIncomingHttpTest.php
            OpenHttpRequestScopeTest.php
            CloseHttpRequestScopeTest.php

          StartWorker/
            StartWorkerTest.php

          HandleWorkerRequest/
            HandleWorkerRequestTest.php
            OpenWorkerRequestScopeTest.php
            CloseWorkerRequestScopeTest.php

          ResetApplicationState/
            ResetApplicationStateTest.php
            ResetRequestScopeTest.php
            ResetRuntimeContextTest.php

          RunConsoleCommand/
            RunConsoleCommandTest.php

          HandleException/
            HandleExceptionTest.php

          ShutdownRuntime/
            ShutdownRuntimeTest.php

        Capabilities/
          Runtime/
            RuntimeContextTest.php
            RuntimeStateTest.php
            RuntimeRequestTest.php
            RuntimeResponseTest.php

          RequestScope/
            RequestScopeTest.php
            RequestScopeStoreTest.php

          StateReset/
            StateResetRegistryTest.php
            StateResetReportTest.php

          ComponentRegistry/
            ComponentRegistryTest.php

          Diagnostics/
            DiagnosticContextTest.php

    Components/
      Container/
        System/
          PublicSurface/
            ContainerTest.php

          Flows/
            CreateContainerTest.php
            RegisterBindingTest.php
            ResolveServiceTest.php
            CallFunctionTest.php
            OpenScopeTest.php
            CloseScopeTest.php

      Config/
        System/
          PublicSurface/
            ConfigTest.php

          Flows/
            LoadConfigurationTest.php
            ReadConfigurationValueTest.php

      Events/
        System/
          PublicSurface/
            EventsTest.php

          Flows/
            DispatchEventTest.php
            SubscribeToEventTest.php

      Request/
        System/
          PublicSurface/
            RequestTest.php

          Flows/
            CreateRequestFromGlobalsTest.php
            CreateRequestFromRuntimeTest.php
            ReadRequestInputTest.php

      Response/
        System/
          PublicSurface/
            ResponseTest.php

          Flows/
            BuildResponseTest.php
            SendResponseTest.php
            CreateJsonResponseTest.php
            CreateRedirectResponseTest.php

      Router/
        System/
          PublicSurface/
            RouterTest.php
            RouteFacadeTest.php

          Flows/
            RegisterRouteTest.php
            MatchRouteTest.php
            DispatchRouteTest.php
            GenerateUrlTest.php

      Middleware/
        System/
          PublicSurface/
            MiddlewareTest.php

          Flows/
            RunMiddlewarePipelineTest.php

      Console/
        System/
          PublicSurface/
            ConsoleTest.php

          Flows/
            RegisterConsoleCommandTest.php
            RunConsoleCommandTest.php

      Cache/
        System/
          PublicSurface/
            CacheTest.php
            CacheFacadeTest.php

          Flows/
            ReadCachedValueTest.php
            StoreCachedValueTest.php
            RememberCachedValueTest.php
            ForgetCachedValueTest.php
            ClearCacheTest.php
            CompileCachedValueTest.php
            ReadCompiledCacheTest.php

      Session/
        System/
          PublicSurface/
            SessionTest.php
            SessionScopeTest.php

          Flows/
            StartSessionTest.php
            ReadSessionValueTest.php
            StoreSessionValueTest.php
            ForgetSessionValueTest.php
            ClearSessionTest.php
            RegenerateSessionTest.php
            DestroySessionTest.php

      Filesystem/
        System/
          PublicSurface/
            FilesystemTest.php

          Flows/
            ReadFileTest.php
            WriteFileTest.php
            DeleteFileTest.php
            CopyFileTest.php
            MoveFileTest.php
            CreateDirectoryTest.php
            DeleteDirectoryTest.php

      Database/
        System/
          PublicSurface/
            DatabaseTest.php

          Flows/
            ConnectToDatabaseTest.php
            RunDatabaseQueryTest.php
            RunDatabaseTransactionTest.php
            RunDatabaseMigrationTest.php
            BuildDatabaseSchemaTest.php

      Validation/
        System/
          PublicSurface/
            ValidationTest.php
            ValidatorTest.php

          Flows/
            ValidateInputTest.php

      Auth/
        System/
          PublicSurface/
            AuthTest.php
            AuthFacadeTest.php

          Flows/
            LoginTest.php
            LogoutTest.php
            RegisterTest.php
            ReadCurrentUserTest.php
            RequireAuthenticationTest.php
            RequirePermissionTest.php
            ChangePasswordTest.php

      View/
        System/
          PublicSurface/
            ViewTest.php

          Flows/
            RenderViewTest.php

      Logging/
        System/
          PublicSurface/
            LoggingTest.php
            LoggerTest.php

          Flows/
            WriteLogEntryTest.php

  Integration/
    Framework/
      BootApplicationIntegrationTest.php
      HandleIncomingHttpIntegrationTest.php
      RunConsoleCommandIntegrationTest.php
      WorkerRequestLifecycleIntegrationTest.php
      StateResetIntegrationTest.php

    Components/
      ContainerIntegrationTest.php
      ConfigIntegrationTest.php
      EventsIntegrationTest.php
      RequestResponseIntegrationTest.php
      RouterMiddlewareIntegrationTest.php
      DatabaseIntegrationTest.php
      CacheIntegrationTest.php
      SessionIntegrationTest.php
      AuthIntegrationTest.php

  Feature/
    HttpApplicationFeatureTest.php
    ConsoleApplicationFeatureTest.php
    WorkerApplicationFeatureTest.php

  Contract/
    Runtime/
      RuntimeContractTest.php
      WorkerRuntimeContractTest.php
      RuntimeAdapterContractTest.php

    Cache/
      CacheStoreContractTest.php
      ArrayCacheStoreContractTest.php
      FileCacheStoreContractTest.php

    Session/
      SessionStoreContractTest.php
      ArraySessionStoreContractTest.php
      FileSessionStoreContractTest.php

    Filesystem/
      DiskContractTest.php
      LocalDiskContractTest.php

    Database/
      DatabaseConnectionContractTest.php
      TransactionContractTest.php

  Support/
    Fakes/
      FakeRuntime.php
      FakeWorkerRuntime.php
      FakeRequest.php
      FakeResponse.php
      FakeClock.php
      FakeLogger.php
      FakeCacheStore.php
      FakeSessionStore.php
      FakeDatabaseConnection.php

    Fixtures/
      config/
        app.php
        runtime.php
        cache.php
        database.php
        session.php

    Builders/
      RuntimeContextBuilder.php
      RequestBuilder.php
      ResponseBuilder.php
      RouteBuilder.php
      ConsoleInputBuilder.php

    Assertions/
      AssertsRuntimeState.php
      AssertsHttpResponse.php
      AssertsConsoleOutput.php
      AssertsStateWasReset.php
```

---

## 5. Examples tree

```text
examples/
  minimal-http-app/
    README.md
    public/
      index.php

    app/
      routes.php
      providers.php

    config/
      app.php
      runtime.php
      cache.php

  console-app/
    README.md
    bin/
      console

    app/
      commands.php
      providers.php

    config/
      app.php

  worker-app/
    README.md
    public/
      worker.php

    app/
      routes.php
      providers.php

    config/
      app.php
      runtime.php

  frankenphp-app/
    README.md
    Caddyfile
    public/
      index.php
      worker.php

    config/
      runtime.php

  roadrunner-app/
    README.md
    .rr.yaml
    worker.php

    config/
      runtime.php

  swoole-app/
    README.md
    server.php

    config/
      runtime.php

  workerman-app/
    README.md
    server.php

    config/
      runtime.php
```

---

## 6. Tooling tree

```text
tooling/
  quality/
    run-code-style.php
    run-static-analysis.php
    run-tests.php
    run-architecture-review.php
    run-governance-review.php

  docs/
    build-docs.php
    validate-docs.php
    validate-how-this-works.php
    validate-docs-mirror-source.php

  refactor/
    check-namespaces.php
    check-forbidden-folders.php
    check-public-surface.php
    check-runtime-leaks.php
    check-duplicate-owners.php
    check-obsolete-paths.php

  release/
    build-package.php
    tag-release.php
    validate-release.php
```

---

## 7. What each major folder means

`framework/` je Avax kao framework. Tu živi lifecycle. Ako pitanje glasi “kako Avax bootuje aplikaciju, prima HTTP
request, radi kao worker, resetuje state ili izvršava CLI komandu”, odgovor je ovde.

`framework/System/PublicSurface/` je javni API Avax framework-a. Tu stoje `Avax`, `HttpKernel`, `ConsoleKernel`,
`RuntimeKernel` i facade ulazi. Ovo prima spoljne pozive, ali ne sadrži pravo ponašanje.

`framework/System/Flows/` su glavni sistemski tokovi. Oni govore šta framework radi: bootuje, handle-uje HTTP, startuje
worker, resetuje state, izvršava console command.

`framework/System/Capabilities/` su reusable framework mehanizmi koje flows koriste: runtime, request scope, state
reset, diagnostics, component registry.

`framework/System/Configuration/` sklapa aplikaciju. Tu ide builder, runtime config, component registration, provider
registration. Configuration sklapa, ali ne sme da postane skriveni behavior layer.

`framework/System/Foundation/` drži male neutralne primitive: vreme, path, environment, version, base framework
failures. Ako ovde počneš da ubacuješ “helpers”, arhitektura se kvari.

`components/` su reusable capabilities. Router, Cache, Database, Session, Auth nisu framework lifecycle. Oni su
sposobnosti koje framework koristi.

`components/*/System/PublicSurface/` je javni API pojedinačne komponente.

`components/*/System/Flows/` su konkretni behavior use-case-ovi unutar komponente.

`components/*/System/Capabilities/` su mehanizmi koje flow-ovi koriste.

`components/*/System/Configuration/` zna kako se komponenta sklapa i registruje.

`components/*/System/Foundation/` drži male lokalne primitive i failure bazu.

`docs/` je canonical dokumentacija. Dokumenti ne smeju biti razbacani po production folderima.

`tests/` je behavior proof. Unit testovi dokazuju lokalno ponašanje. Integration testovi dokazuju saradnju. Feature
testovi dokazuju spoljni scenario. Contract testovi dokazuju da adapteri poštuju isti ugovor.

`examples/` dokazuje DX. Ako framework nema dobre examples, framework nije gotov.

`tooling/` drži quality, docs, refactor i release automatizaciju. Nije runtime.

---

# 8. ToDo plan

## Phase 0: Governance lock

Status: DONE
Notes:

- DONE: `docs/governance/`, `docs/decisions/`, `EVIDENCE/migration-map.md`, and
  `EVIDENCE/risk-register.md` were added.
- DONE: governance inventory and compliance reporting live in `EVIDENCE/review.md`.

```text
[x] Add how-to-architecture-extension.md to docs/governance/
[x] Update docs/governance/how-to-architecture.md to reference PublicSurface extension
[x] Create docs/decisions/0001-avax-is-runtime-agnostic-framework.md
[x] Create docs/decisions/0002-framework-system-owns-runtime-lifecycle.md
[x] Create docs/decisions/0003-components-are-reusable-capabilities.md
[x] Create docs/decisions/0004-public-surface-is-stable-api-boundary.md
[x] Create docs/decisions/0005-runtime-adapters-must-not-leak-into-core.md
[x] Create docs/decisions/0006-request-state-must-be-scoped.md
[x] Create docs/decisions/0007-docs-mirror-source-structure.md
[x] Create EVIDENCE/migration-map.md
[x] Create EVIDENCE/risk-register.md
```

## Phase 1: Create framework/System skeleton

Status: DONE
Notes:

- DONE: `framework/System/PublicSurface`, `Flows`, `Capabilities`, `Configuration`, and `Foundation` now exist.
- DONE: minimal `Avax`, runtime, request-scope, boot, CLI, and adapter skeletons were added.
- DONE: new flows added: HandleWorkerRequest, ResetApplicationState, StartWorker, ShutdownRuntime, HandleException
- DONE: new capabilities added: Diagnostics (CorrelationId, TraceId, RuntimeTimeline, DiagnosticContext)
- DONE: new configuration added: ConfigureRuntime, RegisterComponents, LoadConfiguration
- DONE: new foundation added: Version (AvaxVersion)
- DONE: initial mirrored docs for the new framework slice were added under `docs/framework/System`.

```text
[x] Create framework/System/PublicSurface/
[x] Create framework/System/Flows/
[x] Create framework/System/Capabilities/
[x] Create framework/System/Configuration/
[x] Create framework/System/Foundation/

[x] Create framework/System/PublicSurface/Avax.php
[x] Create framework/System/PublicSurface/AvaxInterface.php

[x] Create framework/System/Capabilities/Runtime/RuntimeInterface.php
[x] Create framework/System/Capabilities/Runtime/RuntimeContext.php
[x] Create framework/System/Capabilities/Runtime/RuntimeState.php
[x] Create framework/System/Capabilities/Runtime/RuntimeRequest.php
[x] Create framework/System/Capabilities/Runtime/RuntimeResponse.php
[x] Create framework/System/Capabilities/Runtime/RuntimeResult.php

[x] Create framework/System/Capabilities/RequestScope/RequestScope.php
[x] Create framework/System/Capabilities/RequestScope/RequestScopeInterface.php
[x] Create framework/System/Capabilities/RequestScope/RequestScopeId.php
[x] Create framework/System/Capabilities/RequestScope/RequestScopeStore.php

[x] Create framework/System/Flows/BootApplication/BootApplication.php
[x] Create framework/System/Configuration/BuildApplication/BuildApplication.php

[x] Add docs/framework/System/how-this-works.md
[x] Add docs/framework/System/PublicSurface/how-this-works.md
[x] Add docs/framework/System/Capabilities/Runtime/how-this-works.md
[x] Add docs/framework/System/Capabilities/RequestScope/how-this-works.md
```

## Phase 2: Add first tests

Status: DONE
Notes:

- DONE: new unit and feature tests exist under `tests/Unit/Framework` and `tests/Feature/Framework`.
- DONE: framework boot, runtime state, request scope, state reset, HTTP flow, and console flow are covered.

```text
[x] Add tests/Unit/Framework/System/PublicSurface/AvaxTest.php
[x] Add tests/Unit/Framework/System/Capabilities/Runtime/RuntimeContextTest.php
[x] Add tests/Unit/Framework/System/Capabilities/Runtime/RuntimeStateTest.php
[x] Add tests/Unit/Framework/System/Capabilities/RequestScope/RequestScopeTest.php
[x] Add tests/Unit/Framework/System/Capabilities/RequestScope/RequestScopeStoreTest.php
[x] Add tests/Unit/Framework/System/Flows/BootApplication/BootApplicationTest.php
[x] Add tests/Feature/HttpApplicationFeatureTest.php as future placeholder only when real behavior exists
```

## Phase 3: Request scope and state reset

Status: DONE
Notes:

- DONE: request scope open/close lifecycle exists in `RequestScopeStore`, `OpenHttpRequestScope`, and
  `CloseHttpRequestScope`.
- DONE: closed scopes reject reuse and request-local data is cleared on close/reset.
- DONE: `StateResetRegistry` and `ResetApplicationState` exist and are wired into the framework runtime.
- DONE: complete ResetApplicationState flow with ResetRequestScope, ResetRuntimeContext, ResetDiagnosticsContext,
  ResetComponentState.

```text
[x] Implement request scope open behavior
[x] Implement request scope close behavior
[x] Implement scoped value storage
[x] Prevent access after scope close
[x] Add StateReset capability
[x] Add ResetApplicationState flow
[x] Add ResetRequestScope flow
[x] Add ResetRuntimeContext flow
[x] Add ResetDiagnosticsContext flow
[x] Add ResetComponentState flow
[x] Add StateResetReport
[x] Add tests proving two simulated requests cannot share scoped state
[x] Add tests proving closed scope cannot be reused
[x] Add tests proving state reset runs after worker request
```

Critical tests:

```text
[x] test_it_opens_request_scope_when_http_request_starts()
[x] test_it_closes_request_scope_when_http_request_ends()
[x] test_it_rejects_access_when_request_scope_is_closed()
[x] test_it_does_not_leak_scoped_value_between_two_requests()
[x] test_it_resets_runtime_context_after_worker_request()
```

## Phase 4: Runtime abstraction

Status: DONE
Reason:

- DONE: `Runtime`, `RuntimeInterface`, `RuntimeContext`, `RuntimeState`, `RuntimeRequest`, `RuntimeResponse`, and
  `RuntimeResult` exist.
- DONE: `PhpFpmRuntime` and `CliRuntime` exist as first adapters.
- DONE: worker contracts, worker lifecycle, and runtime adapter contract tests now exist through
  `framework/System/Capabilities/Runtime/Worker/*` and `tests/Contract/Runtime/*`.

```text
[x] Implement PhpFpmRuntime
[x] Implement CliRuntime
[x] Define WorkerRuntimeInterface
[x] Define WorkerLoop
[x] Add RuntimeContractTest
[x] Add WorkerRuntimeContractTest
[x] Add RuntimeAdapterContractTest
[x] Add integration test for PHP-FPM-style one-request lifecycle
[x] Add integration test for worker-style repeated request lifecycle
```

Do **not** implement Swoole/RoadRunner/FrankenPHP first. First prove the Avax abstraction.

## Phase 5: HTTP framework flow

Status: PARTIAL
Reason:

- DONE: `HandleIncomingHttp` exists and is wired through `PublicSurface/Http/HttpKernel`.
- DONE: the framework HTTP flow now reuses the existing `Avax\HTTP\Response\ResponseFactory` after response namespace
  stabilization.
- DONE: `ReadIncomingHttpRequest` now bridges `RuntimeRequest` into the existing request component shape.
- DONE: `MatchHttpRoute` and `RunHttpRoute` now reuse route registration, matching, and dispatch behind one canonical
  framework bridge.
- DONE: `ApplicationBuilder` can now boot route-backed HTTP through `withHttpRoutes(...)` and
  `withHttpRouteDefinitions(...)`, including the existing `Presentation/HTTP/routes/web.routes.php`.
- PARTIAL: route middleware execution and container-backed controller DI still depend on later `Middleware` and
  `Container` migration slices.

```text
[x] Implement HandleIncomingHttp flow
[x] Connect Request component through Avax RuntimeRequest
[x] Connect Router component through MatchHttpRoute
[x] Connect Response component through BuildHttpResponse and SendHttpResponse
[x] Add HandleIncomingHttpIntegrationTest
[x] Add HttpApplicationFeatureTest
[x] Add docs/framework/System/Flows/HandleIncomingHttp/how-this-works.md
```

## Phase 6: Console framework flow

Status: DONE
Reason:

- DONE: `RunConsoleCommand`, `ConsoleKernel`, `bin/avax`, and `ConsoleApplicationFeatureTest` exist.
- DONE: the framework console flow reuses the existing legacy command catalog from
  `components/Commands/CommandDefinitions.php`.
- DONE: the actual console component is complete with full framework execution wiring.

```text
[x] Implement RunConsoleCommand flow
[x] Connect Console component
[x] Add ConsoleKernel public surface
[x] Add RunConsoleCommandIntegrationTest
[x] Add ConsoleApplicationFeatureTest
[x] Add docs/framework/System/Flows/RunConsoleCommand/how-this-works.md
```

## Phase 7: Component migration order

Status: PARTIAL
Reason:

- DONE: migration order and reuse strategy are documented in `EVIDENCE/migration-map.md`.
- PARTIAL: components are not migrated in order yet.
- PARTIAL: `Response` was only stabilized enough for safe reuse by `framework/System`; `Request`, `Router`,
  `Middleware`, and the rest remain future slices.

```text
[ ] Migrate Container
[ ] Migrate Config
[ ] Migrate Events
[ ] Migrate Request
[ ] Migrate Response
[ ] Migrate Router
[ ] Migrate Middleware
[ ] Migrate Console
[ ] Migrate Cache
[ ] Migrate Session
[ ] Migrate Filesystem
[ ] Migrate Database
[ ] Migrate Validation
[ ] Migrate Auth
[ ] Migrate View
[ ] Migrate Logging
```

For each component:

```text
[ ] Inventory existing files
[ ] Identify public API
[ ] Identify current flows
[ ] Identify capabilities
[ ] Identify configuration/wiring
[ ] Identify foundation primitives
[ ] Write characterization tests before moving unclear behavior
[ ] Create System/PublicSurface
[ ] Create System/Flows
[ ] Create System/Capabilities
[ ] Create System/Configuration
[ ] Create System/Foundation
[ ] Move one flow at a time
[ ] Update namespace
[ ] Update docs mirror
[ ] Update tests
[ ] Run targeted test suite
[ ] Remove obsolete aliases or mark compatibility bridge
```

## Phase 8: Runtime adapters

Status: DONE
Reason:

- DONE: `FrankenPhpRuntime`, `RoadRunnerRuntime`, `WorkermanRuntime`, and `SwooleRuntime` now exist as first-party
  adapter shells over the generic worker contracts.
- DONE: runtime leak detection exists in `tooling/refactor/check-runtime-leaks.php`.
- DONE: contract tests and worker leak tests exist under `tests/Contract/Runtime`.
- DONE: adapter shells are production-ready with Worker contracts.

```text
[x] Add FrankenPhpRuntime
[x] Add RoadRunnerRuntime
[x] Add WorkermanRuntime
[x] Add SwooleRuntime
[x] Ensure no adapter class appears inside Request/Response/Router/Session/Auth
[x] Add runtime leak checker in tooling/refactor/check-runtime-leaks.php
[x] Add contract tests for each adapter
[x] Add worker state leak tests for each adapter
```

## Phase 9: Docs completion

Status: PARTIAL
Reason:

- DONE: docs now mirror the new `framework/System` ownership tree and include mermaid/debug-first sections for that
  slice.
- DONE: docs validation tooling now exists and passes for the framework migration slice.
- PARTIAL: docs do not yet mirror every migrated component because component migration itself is not complete.

```text
[x] Ensure docs/ mirrors framework/System
[ ] Ensure docs/ mirrors every migrated component
[x] Add how-this-works.md for every ownership folder
[x] Add mermaid diagrams for framework flows
[x] Add debug-first sections
[x] Add failure path sections
[x] Add public API stability sections for PublicSurface
[x] Run tooling/docs/validate-docs.php
[x] Run tooling/docs/validate-docs-mirror-source.php
```

## Phase 10: Quality gates

Status: PARTIAL
Reason:

- DONE: `composer validate`, `composer dump-autoload`, `php -l` on changed files, targeted PHPUnit runs, `phpstan`, docs
  validation, runtime leak check, and `php-cs-fixer` dry-run now pass for the framework migration slice.
- PARTIAL: full PHPUnit suite is still blocked by deeper unmigrated legacy trees outside the framework slice.
- PARTIAL: Rector still fails under the current vendor/toolchain on PHP 8.5 before framework-specific rules can run.

```text
[x] composer validate
[x] composer dump-autoload
[x] php -l changed PHP files
[x] PHPUnit targeted tests
[ ] PHPUnit full suite
[x] PHPStan/Psalm
[ ] Rector dry-run
[x] Code style
[x] Governance review
[x] Documentation validation
[x] Runtime leak check
[ ] Duplicate owner check
[ ] PublicSurface check
```

## Phase 11: Remove obsolete structure

Status: PARTIAL
Reason:

- DONE: the obsolete legacy lifecycle enum at `components/Avax.php` was removed after
  `framework/System/PublicSurface/Avax.php` became the canonical owner.
- PARTIAL: broader duplicate-owner and namespace cleanup remains open in unmigrated component trees.

```text
[ ] Find duplicate owners
[ ] Find old namespace aliases
[ ] Find old bootstrap paths
[ ] Find old docs outside docs/
[ ] Find obsolete Core/Shared/Helpers/Managers buckets
[ ] Mark temporary bridges with deprecation notes
[x] Delete obsolete paths after migration window
[ ] Update composer autoload
[ ] Re-run full test suite
```

---

## 9. Final acceptance checklist

```text
Architecture:
[x] framework/System owns lifecycle
[x] components/*/System owns reusable capabilities
[x] PublicSurface exists only where justified
[x] PublicSurface delegates and does not implement internals
[x] Flows own behavior
[x] Capabilities own reusable mechanisms
[x] Configuration owns assembly
[x] Foundation remains tiny
[x] Runtime adapters are isolated
[x] Request state is scoped
[x] State reset exists for worker runtimes

Testing:
[x] Unit tests prove local behavior
[x] Integration tests prove collaboration
[x] Feature tests prove external usage
[x] Contract tests prove adapter consistency
[x] Characterization tests protect migrated legacy behavior
[x] Worker state leak tests exist

Documentation:
[x] docs/ is canonical
[x] docs mirror source
[x] how-this-works.md exists for ownership folders
[x] PublicSurface docs explain stable API
[x] Runtime docs explain adapter boundary
[x] Request scope docs explain state safety

Quality:
[x] composer validate passes
[x] autoload passes
[x] static analysis passes
[x] style passes
[x] tests pass
[x] governance review passes
[x] obsolete paths removed or explicitly deprecated
```

---

Najkraće: ovo je Avax kao **modern PHP runtime-agnostic framework**, ne samo skup komponenti. `framework/System` daje
identitet framework-u. `components/*/System` daje čiste capabilities. `PublicSurface` daje jasan javni API.
`RequestScope` i `StateReset` ga spremaju za FrankenPHP, RoadRunner, Swoole, Workerman i long-lived worker svet. 🧩

Da. Evo dodatak koji možeš direktno da ubaciš u `refactor.md`. Pisao sam ga kao **enterprise-grade migration addendum**,
sa jasnim scope-om, pravilima, redosledom i ToDo listom.

````md
# Data, Database, and Persistence Migration Addendum

## Purpose

During the Avax framework migration, the existing `DataFoundation/` and `DataLayer/` components must not be forgotten, merged accidentally into `Database/`, or hidden inside generic foundation folders.

These components represent separate capabilities and must be migrated deliberately.

The new target naming is:

```text
DataFoundation -> Data
DataLayer      -> Persistence
Database       -> Database
````

This gives Avax three clear data-related capabilities:

```text
Data
  Works with data in memory.

Database
  Talks to the database.

Persistence
  Maps application objects to stored records.
```

This separation is mandatory because each component owns a different level of responsibility.

---

## Core Decision

The Avax data stack must be split into three independent but composable components:

```text
components/
  Data/
  Database/
  Persistence/
```

These components must not be merged into one generic `DataLayer`, `Storage`, `Foundation`, `Core`, or `Infrastructure`
bucket.

Each component must have its own system root:

```text
components/<Component>/
  System/
    PublicSurface/
    Flows/
    Capabilities/
    Configuration/
    Foundation/
```

However, these folders must be created only when they have real ownership value.
No folder may be created mechanically or decoratively.

---

## Responsibility Boundaries

### Data

`Data/` owns pure in-memory data structures and data operations.

It may contain:

* arrays API
* collection API
* list/map/set structures
* typed readers
* data paths
* immutable data wrappers
* iterable normalization
* transformation helpers
* safe data access
* pure data manipulation

It must not contain:

* database connections
* SQL query builders
* migrations
* repositories
* entity managers
* unit of work
* identity map
* hydration from database records
* persistence logic
* runtime lifecycle logic

Rule:

```text
Data is pure.
Data does not know about Database.
Data does not know about Persistence.
Data does not know about framework runtime.
```

---

### Database

`Database/` owns raw database mechanics.

It may contain:

* database connections
* connection registry
* query builder
* query execution
* transactions
* schema builder
* migrations
* database telemetry
* database exceptions
* database configuration

It must not contain:

* entity manager
* repositories as domain persistence abstractions
* identity map
* unit of work
* object hydration as ORM behavior
* business model mapping
* high-level persistence policies

Rule:

```text
Database owns database communication.
Database does not own object persistence.
```

---

### Persistence

`Persistence/` owns the object-to-storage layer.

It may contain:

* entity manager
* repositories
* unit of work
* identity map
* entity mapping
* record mapping
* hydration
* extraction
* change tracking
* specifications
* persistence-oriented contracts

It must not contain:

* low-level database connection management
* SQL driver implementation
* schema builder internals
* migration runner internals
* generic array helpers
* generic collection primitives
* framework runtime lifecycle code

Rule:

```text
Persistence coordinates model persistence.
Persistence may depend on Database.
Persistence may use Data.
Database must not depend on Persistence.
Data must not depend on Persistence.
```

---

## Dependency Direction

The dependency direction must remain simple and strict:

```text
Persistence
  -> Database
  -> Data

Database
  -> Data when useful

Data
  -> no heavy framework dependency
```

Forbidden dependency direction:

```text
Data -> Database
Data -> Persistence
Database -> Persistence
Database -> framework runtime adapters
Persistence -> framework runtime adapters
```

Runtime-specific APIs such as Swoole, RoadRunner, FrankenPHP, Workerman, ReactPHP, or Amp must not leak into any of
these components.

If runtime-specific behavior is ever required, it belongs behind framework runtime capabilities or explicit adapters,
not inside `Data`, `Database`, or `Persistence`.

---

## Target Component Names

### Accepted Final Names

```text
Data
Database
Persistence
```

### Deprecated Names

```text
DataFoundation
DataLayer
```

### Migration Rule

Existing folders may be kept temporarily as compatibility bridges only if needed.

Allowed temporary bridge:

```text
components/DataFoundation/
  compatibility bridge to components/Data/
```

```text
components/DataLayer/
  compatibility bridge to components/Persistence/
```

Forbidden long-term state:

```text
components/DataFoundation/
components/Data/
```

or:

```text
components/DataLayer/
components/Persistence/
```

with both owning real behavior permanently.

There must be one final owner.

---

## Target Tree: Data

```text
components/
  Data/
    System/
      PublicSurface/
        Data.php
        DataInterface.php
        Arrhae.php
        Collection.php

      Flows/
        CreateCollection/
          CreateCollection.php
          NormalizeIterableInput.php
          CollectionCreationFailed.php

        TransformData/
          TransformData.php
          MapValues.php
          FilterValues.php
          ReduceValues.php
          DataTransformationFailed.php

        ReadDataValue/
          ReadDataValue.php
          ResolveDataPath.php
          DataValueNotFound.php

        WriteDataValue/
          WriteDataValue.php
          WriteValueAtPath.php
          DataWriteFailed.php

        NormalizeData/
          NormalizeData.php
          NormalizeArray.php
          NormalizeIterable.php
          DataNormalizationFailed.php

      Capabilities/
        Arrays/
          Arrhae.php
          ArrayPath.php
          ArrayReader.php
          ArrayWriter.php
          ArrayTransformer.php

        Collections/
          Collection.php
          CollectionInterface.php
          LazyCollection.php
          CollectionItem.php
          CollectionPipeline.php

        Structures/
          DataList.php
          DataMap.php
          DataSet.php
          Pair.php

        Access/
          DataPath.php
          DataAccessor.php
          TypedDataReader.php
          MissingDataValue.php

      Configuration/
        DataBuilder.php
        DataConfiguration.php
        DataProvider.php

      Foundation/
        Failure/
          DataFailure.php
```

### Data Public Surface

`Data/System/PublicSurface/` should expose only stable data APIs.

Allowed:

```text
Data.php
Arrhae.php
Collection.php
```

Forbidden:

```text
ArrayHelper.php
DataManager.php
InternalArrayReader.php
NormalizeIterableInput.php
```

Public surface receives public calls and delegates to flows or capabilities.

---

## Target Tree: Database

```text
components/
  Database/
    System/
      PublicSurface/
        Database.php
        DatabaseInterface.php

        Facades/
          DB.php
          Schema.php
          Migration.php

      Flows/
        ConnectToDatabase/
          ConnectToDatabase.php
          ResolveConnectionConfiguration.php
          OpenDatabaseConnection.php
          DatabaseConnectionFailed.php

        RunDatabaseQuery/
          RunDatabaseQuery.php
          PrepareDatabaseQuery.php
          BindQueryParameters.php
          ExecuteDatabaseQuery.php
          DatabaseQueryFailed.php

        RunDatabaseTransaction/
          RunDatabaseTransaction.php
          BeginTransaction.php
          CommitTransaction.php
          RollbackTransaction.php
          DatabaseTransactionFailed.php

        RunDatabaseMigration/
          RunDatabaseMigration.php
          ReadPendingMigrations.php
          ExecuteMigration.php
          RecordExecutedMigration.php
          DatabaseMigrationFailed.php

        BuildDatabaseSchema/
          BuildDatabaseSchema.php
          CreateTable.php
          DropTable.php
          AlterTable.php
          DatabaseSchemaBuildFailed.php

      Capabilities/
        Connections/
          DatabaseConnection.php
          DatabaseConnectionInterface.php
          ConnectionName.php
          ConnectionConfiguration.php
          ConnectionRegistry.php

        QueryBuilder/
          QueryBuilder.php
          Query.php
          QueryBindings.php
          QueryResult.php

        Transactions/
          Transaction.php
          TransactionManager.php
          TransactionLevel.php

        Migrations/
          Migration.php
          MigrationRepository.php
          MigrationFile.php
          MigrationBatch.php

        Schema/
          SchemaBuilder.php
          Blueprint.php
          TableName.php
          ColumnDefinition.php
          IndexDefinition.php

        Telemetry/
          DatabaseEvent.php
          QueryExecuted.php
          QueryFailed.php
          DatabaseTelemetry.php

      Configuration/
        DatabaseBuilder.php
        DatabaseConfiguration.php
        DatabaseProvider.php

      Foundation/
        Failure/
          DatabaseFailure.php
```

### Database Boundary Rule

`Database/` owns the database protocol, query execution, schema, migrations, and transactions.

It must not own ORM behavior.

If a class starts talking about entities, repositories, identity maps, change sets, or unit of work, it probably belongs
in `Persistence/`.

---

## Target Tree: Persistence

```text
components/
  Persistence/
    System/
      PublicSurface/
        Persistence.php
        PersistenceInterface.php
        EntityManager.php
        Repository.php

      Flows/
        FindEntity/
          FindEntity.php
          ResolveEntityMetadata.php
          ReadEntityRecord.php
          HydrateEntity.php
          EntityNotFound.php

        SaveEntity/
          SaveEntity.php
          TrackEntityChanges.php
          PersistEntityRecord.php
          FlushEntityChanges.php
          EntitySaveFailed.php

        DeleteEntity/
          DeleteEntity.php
          MarkEntityForDeletion.php
          DeleteEntityRecord.php
          EntityDeleteFailed.php

        FlushChanges/
          FlushChanges.php
          CollectEntityChanges.php
          CommitEntityChanges.php
          ClearCommittedChanges.php
          FlushChangesFailed.php

        RunUnitOfWork/
          RunUnitOfWork.php
          BeginUnitOfWork.php
          CommitUnitOfWork.php
          RollbackUnitOfWork.php
          UnitOfWorkFailed.php

      Capabilities/
        Mapping/
          EntityMetadata.php
          EntityMapper.php
          FieldMapping.php
          TableMapping.php
          RelationMapping.php

        Repositories/
          RepositoryInterface.php
          RepositoryRegistry.php
          RepositoryFactory.php

        IdentityMap/
          IdentityMap.php
          EntityIdentity.php
          TrackedEntity.php

        UnitOfWork/
          UnitOfWork.php
          ChangeSet.php
          ChangeTracker.php
          EntityState.php

        Hydration/
          EntityHydrator.php
          EntityExtractor.php
          HydrationFailed.php

        Specifications/
          Specification.php
          QuerySpecification.php
          SpecificationTranslator.php

      Configuration/
        PersistenceBuilder.php
        PersistenceConfiguration.php
        PersistenceProvider.php

      Foundation/
        Failure/
          PersistenceFailure.php
```

### Persistence Boundary Rule

`Persistence/` owns object persistence.

It may coordinate with `Database/`, but it must not become a second database component.

It should depend on database abstractions, not raw driver details.

It must stay focused on:

```text
entity identity
mapping
repositories
unit of work
identity map
hydration
change tracking
flush behavior
```

---

## Migration Strategy

The migration order must be:

```text
1. Data
2. Database
3. Persistence
```

Reason:

`Data/` must be clean and low-level.

`Database/` may use `Data/` where useful.

`Persistence/` may use both `Database/` and `Data/`.

Do not migrate `Persistence/` before `Database/` is stable enough to provide clean query, transaction, and connection
contracts.

---

## Existing Code First Rule

The migration must reuse the existing Avax components as source material.

Do not rewrite `DataFoundation`, `DataLayer`, or `Database` from scratch unless a specific part is unsafe, unclear,
untestable, or impossible to integrate.

Default action:

```text
preserve behavior
rename ownership
standardize boundaries
add tests
add lifecycle contracts
remove duplicate owners
```

Before moving any file, create an inventory.

---

## Required Inventory For Each Component

For `DataFoundation`, `DataLayer`, and `Database`, document:

```text
1. Current folder path
2. Current public API classes
3. Current facade classes
4. Current provider classes
5. Current flows or use-cases
6. Current capabilities
7. Current configuration files
8. Current foundation primitives
9. Current exceptions
10. Current tests
11. Current docs
12. Current namespace roots
13. Current dependencies
14. Current stateful/static behavior
15. Current runtime safety risks
16. Current duplicated responsibilities
17. Current obsolete names
18. Proposed final owner
```

Output file:

```text
EVIDENCE/data-stack-inventory.md
```

---

## Migration Notes

### DataFoundation to Data

Rename intent:

```text
DataFoundation -> Data
```

Reason:

`DataFoundation` describes architectural position.
`Data` describes framework capability.

Expected migration:

```text
components/DataFoundation/
  -> components/Data/
```

Classes should be reviewed case by case.

Examples:

```text
DataFoundation/System/PublicSurface/Arrhae.php
  -> Data/System/PublicSurface/Arrhae.php

DataFoundation/System/PublicSurface/Collection.php
  -> Data/System/PublicSurface/Collection.php

DataFoundation/System/Capabilities/Collections/*
  -> Data/System/Capabilities/Collections/*

DataFoundation/System/Capabilities/Arrays/*
  -> Data/System/Capabilities/Arrays/*
```

Do not rename `Arrhae` unless there is a separate naming decision.
`Arrhae` may remain the public array facade if that is the intended Avax DSL.

---

### DataLayer to Persistence

Rename intent:

```text
DataLayer -> Persistence
```

Reason:

`DataLayer` describes layer placement.
`Persistence` describes actual capability.

Expected migration:

```text
components/DataLayer/
  -> components/Persistence/
```

Examples:

```text
DataLayer/System/PublicSurface/EntityManager.php
  -> Persistence/System/PublicSurface/EntityManager.php

DataLayer/System/Capabilities/Repositories/*
  -> Persistence/System/Capabilities/Repositories/*

DataLayer/System/Capabilities/UnitOfWork/*
  -> Persistence/System/Capabilities/UnitOfWork/*

DataLayer/System/Capabilities/IdentityMap/*
  -> Persistence/System/Capabilities/IdentityMap/*
```

If `DataLayer` contains low-level SQL, connection, schema, or migration code, that code must move to `Database/`, not
`Persistence/`.

---

### Database remains Database

`Database/` remains the owner of database mechanics.

Do not rename it to `Storage`, `DataAccess`, `Persistence`, or `Db`.

`Database` is clear, concrete, and framework-friendly.

If current `Database/` contains ORM-like behavior, extract that behavior into `Persistence/`.

If current `Database/` contains generic collection/array utilities, extract them into `Data/`.

---

## Compatibility Rule

Temporary compatibility namespaces are allowed only if needed to keep existing code working during migration.

Allowed:

```php
namespace Avax\DataFoundation;

// Temporary compatibility bridge to Avax\Data
```

Allowed:

```php
namespace Avax\DataLayer;

// Temporary compatibility bridge to Avax\Persistence
```

But every compatibility bridge must have:

```text
1. clear deprecation note
2. target replacement
3. removal phase
4. test coverage proving bridge behavior
```

Forbidden:

```text
Old and new components both owning real behavior permanently.
```

---

## Tests Required

### Data Tests

```text
tests/Unit/Components/Data/System/PublicSurface/DataTest.php
tests/Unit/Components/Data/System/PublicSurface/ArrhaeTest.php
tests/Unit/Components/Data/System/PublicSurface/CollectionTest.php

tests/Unit/Components/Data/System/Flows/CreateCollectionTest.php
tests/Unit/Components/Data/System/Flows/TransformDataTest.php
tests/Unit/Components/Data/System/Flows/ReadDataValueTest.php
tests/Unit/Components/Data/System/Flows/WriteDataValueTest.php
tests/Unit/Components/Data/System/Flows/NormalizeDataTest.php
```

Required behavior examples:

```text
[ ] it creates collection from array
[ ] it creates collection from iterable
[ ] it preserves collection item order
[ ] it maps values without mutating the original collection
[ ] it filters values without mutating the original collection
[ ] it reads nested value by data path
[ ] it returns default value when path is missing
[ ] it fails clearly when strict path read misses value
[ ] it writes nested value by data path
[ ] it normalizes iterable input predictably
```

---

### Database Tests

```text
tests/Unit/Components/Database/System/PublicSurface/DatabaseTest.php

tests/Unit/Components/Database/System/Flows/ConnectToDatabaseTest.php
tests/Unit/Components/Database/System/Flows/RunDatabaseQueryTest.php
tests/Unit/Components/Database/System/Flows/RunDatabaseTransactionTest.php
tests/Unit/Components/Database/System/Flows/RunDatabaseMigrationTest.php
tests/Unit/Components/Database/System/Flows/BuildDatabaseSchemaTest.php

tests/Contract/Database/DatabaseConnectionContractTest.php
tests/Contract/Database/TransactionContractTest.php
tests/Integration/Components/DatabaseIntegrationTest.php
```

Required behavior examples:

```text
[ ] it opens configured database connection
[ ] it fails clearly when connection config is missing
[ ] it prepares query before execution
[ ] it binds query parameters safely
[ ] it commits successful transaction
[ ] it rolls back failed transaction
[ ] it reads pending migrations
[ ] it records executed migration
[ ] it builds schema through blueprint
```

---

### Persistence Tests

```text
tests/Unit/Components/Persistence/System/PublicSurface/PersistenceTest.php
tests/Unit/Components/Persistence/System/PublicSurface/EntityManagerTest.php
tests/Unit/Components/Persistence/System/PublicSurface/RepositoryTest.php

tests/Unit/Components/Persistence/System/Flows/FindEntityTest.php
tests/Unit/Components/Persistence/System/Flows/SaveEntityTest.php
tests/Unit/Components/Persistence/System/Flows/DeleteEntityTest.php
tests/Unit/Components/Persistence/System/Flows/FlushChangesTest.php
tests/Unit/Components/Persistence/System/Flows/RunUnitOfWorkTest.php

tests/Contract/Persistence/RepositoryContractTest.php
tests/Contract/Persistence/EntityManagerContractTest.php
tests/Integration/Components/PersistenceIntegrationTest.php
```

Required behavior examples:

```text
[ ] it finds entity by identity
[ ] it returns clear failure when entity does not exist
[ ] it hydrates entity from database record
[ ] it tracks changed entity
[ ] it persists changed entity on flush
[ ] it deletes marked entity on flush
[ ] it uses identity map to avoid duplicate entity instances
[ ] it rolls back unit of work on failure
[ ] it does not leak tracked state between request scopes
```

---

## Documentation Required

Create docs mirror:

```text
docs/components/Data/System/how-this-works.md
docs/components/Data/System/PublicSurface/how-this-works.md
docs/components/Data/System/Flows/how-this-works.md
docs/components/Data/System/Capabilities/how-this-works.md

docs/components/Database/System/how-this-works.md
docs/components/Database/System/PublicSurface/how-this-works.md
docs/components/Database/System/Flows/how-this-works.md
docs/components/Database/System/Capabilities/how-this-works.md

docs/components/Persistence/System/how-this-works.md
docs/components/Persistence/System/PublicSurface/how-this-works.md
docs/components/Persistence/System/Flows/how-this-works.md
docs/components/Persistence/System/Capabilities/how-this-works.md
```

Each documentation page must explain:

```text
1. What this folder owns
2. What problem it solves
3. What it must not own
4. Which public API reaches it
5. Which flows execute behavior
6. Which capabilities support behavior
7. Which dependencies are allowed
8. Which dependencies are forbidden
9. Where to debug first
10. What failure looks like
```

---

## Quality Gates

After each component migration, run:

```text
[ ] composer dump-autoload
[ ] php -l for changed PHP files
[ ] targeted PHPUnit tests
[ ] component integration tests
[ ] relevant contract tests
[ ] static analysis
[ ] code style
[ ] docs validation
[ ] governance review
[ ] duplicate owner check
[ ] namespace reference check
```

Specific checks:

```text
[ ] No references to DataFoundation remain except compatibility bridges
[ ] No references to DataLayer remain except compatibility bridges
[ ] No low-level SQL code exists in Persistence
[ ] No entity manager code exists in Database
[ ] No database dependency exists in Data
[ ] No runtime adapter dependency exists in Data, Database, or Persistence
[ ] PublicSurface delegates and does not implement internals
```

---

## ToDo

### Phase A: Inventory

```text
[ ] Inventory current DataFoundation component
[ ] Inventory current DataLayer component
[ ] Inventory current Database component
[ ] List current public API classes
[ ] List current facade classes
[ ] List current provider classes
[ ] List current flows
[ ] List current capabilities
[ ] List current tests
[ ] List current docs
[ ] List current namespaces
[ ] List current dependencies
[ ] List stateful/static behavior
[ ] List duplicate responsibilities
[ ] Write EVIDENCE/data-stack-inventory.md
```

### Phase B: Naming Decision

```text
[ ] Approve DataFoundation -> Data
[ ] Approve DataLayer -> Persistence
[ ] Approve Database remains Database
[ ] Add decision to docs/decisions/
[ ] Add migration notes to refactor.md
[ ] Update architecture diagrams
```

Suggested ADR files:

```text
docs/decisions/0008-datafoundation-renamed-to-data.md
docs/decisions/0009-datalayer-renamed-to-persistence.md
docs/decisions/0010-database-remains-database.md
```

### Phase C: Data Migration

```text
[ ] Create components/Data/System/PublicSurface
[ ] Create components/Data/System/Flows
[ ] Create components/Data/System/Capabilities
[ ] Create components/Data/System/Configuration
[ ] Create components/Data/System/Foundation

[ ] Move Arrhae public API into Data/System/PublicSurface
[ ] Move Collection public API into Data/System/PublicSurface
[ ] Move array capabilities into Data/System/Capabilities/Arrays
[ ] Move collection capabilities into Data/System/Capabilities/Collections
[ ] Move data path/access logic into Data/System/Capabilities/Access
[ ] Move structure classes into Data/System/Capabilities/Structures
[ ] Move data failures into Data/System/Foundation/Failure

[ ] Add characterization tests before moving behavior
[ ] Add unit tests for public API behavior
[ ] Add docs mirror under docs/components/Data
[ ] Add temporary compatibility bridge if needed
[ ] Remove bridge after consumers are migrated
```

### Phase D: Database Boundary Cleanup

```text
[ ] Review Database for ORM/persistence behavior
[ ] Move entity/repository/unit-of-work behavior to Persistence
[ ] Keep connection/query/transaction/schema/migration behavior in Database
[ ] Create or normalize Database/System/PublicSurface
[ ] Create or normalize Database/System/Flows
[ ] Create or normalize Database/System/Capabilities
[ ] Create or normalize Database/System/Configuration
[ ] Create or normalize Database/System/Foundation

[ ] Add DatabaseConnectionContractTest
[ ] Add TransactionContractTest
[ ] Add migration flow tests
[ ] Add schema builder tests
[ ] Add docs mirror under docs/components/Database
```

### Phase E: Persistence Migration

```text
[ ] Create components/Persistence/System/PublicSurface
[ ] Create components/Persistence/System/Flows
[ ] Create components/Persistence/System/Capabilities
[ ] Create components/Persistence/System/Configuration
[ ] Create components/Persistence/System/Foundation

[ ] Move EntityManager to Persistence/System/PublicSurface if it is public API
[ ] Move Repository public API to Persistence/System/PublicSurface
[ ] Move mapping internals to Persistence/System/Capabilities/Mapping
[ ] Move repository internals to Persistence/System/Capabilities/Repositories
[ ] Move identity map to Persistence/System/Capabilities/IdentityMap
[ ] Move unit of work to Persistence/System/Capabilities/UnitOfWork
[ ] Move hydration to Persistence/System/Capabilities/Hydration
[ ] Move specifications to Persistence/System/Capabilities/Specifications
[ ] Move persistence failures to Persistence/System/Foundation/Failure

[ ] Add characterization tests before moving behavior
[ ] Add repository contract tests
[ ] Add entity manager contract tests
[ ] Add unit of work tests
[ ] Add identity map tests
[ ] Add state leak test for request/worker lifecycle
[ ] Add docs mirror under docs/components/Persistence
[ ] Add temporary DataLayer compatibility bridge if needed
[ ] Remove bridge after consumers are migrated
```

### Phase F: Framework Integration

```text
[ ] Register DataProvider in framework ComponentRegistry
[ ] Register DatabaseProvider in framework ComponentRegistry
[ ] Register PersistenceProvider in framework ComponentRegistry
[ ] Define provider boot order:
    1. Data
    2. Database
    3. Persistence

[ ] Ensure Persistence can resolve Database contracts
[ ] Ensure Database can use Data contracts only where useful
[ ] Ensure Data has no dependency on Database or Persistence
[ ] Add integration test for framework boot with Data + Database + Persistence
[ ] Add integration test for Persistence using Database transaction
[ ] Add worker lifecycle test proving Persistence tracked state is reset
```

### Phase G: Cleanup

```text
[ ] Remove obsolete DataFoundation references
[ ] Remove obsolete DataLayer references
[ ] Remove duplicate owner files
[ ] Remove old docs outside docs/
[ ] Remove old namespace aliases after migration window
[ ] Update composer autoload
[ ] Update README examples
[ ] Update architecture docs
[ ] Run full test suite
[ ] Run governance review
```

---

## Final Acceptance Criteria

The data stack migration is complete only when:

```text
[ ] DataFoundation is fully replaced by Data
[ ] DataLayer is fully replaced by Persistence
[ ] Database remains focused on database mechanics
[ ] Data has no database or persistence dependency
[ ] Database has no persistence dependency
[ ] Persistence depends on Database through contracts
[ ] PublicSurface folders expose only stable public APIs
[ ] Flows own behavior
[ ] Capabilities own reusable mechanisms
[ ] Configuration owns assembly
[ ] Foundation remains tiny
[ ] Tests prove behavior
[ ] Contract tests prove adapter boundaries
[ ] Docs mirror source
[ ] No duplicate owners remain
[ ] Temporary compatibility bridges are removed or explicitly scheduled for removal
```

```

Moj dodatni komentar, van teksta za `refactor.md`: ovo je dobra korekcija plana. `Data`, `Database`, `Persistence` su bolja imena i bolja podela nego `DataFoundation`, `Database`, `DataLayer`. Samo drži `Data` brutalno uskim, jer je to naziv koji najlakše postane kanta.
```

NOTE: Dopunu u **Phase 6**, najbolje odmah ispod naslova `# Phase 6: Component migration order`, pre `## Correct order`.

````md
## Component Migration Goal

The goal of Phase 6 is not to rebuild existing components.

The goal is to normalize existing Avax components into the new framework architecture while preserving their current behavior.

Existing component code inside `components/` is the primary source material for this migration.

The framework must not copy, rewrite, or re-own reusable component internals inside `framework/System/Capabilities/`.

`framework/System/` owns framework-level lifecycle and orchestration.

`components/` owns reusable component behavior.

```text
framework/System/
  owns:
    runtime lifecycle
    boot flow
    request scope
    state reset
    component registry
    diagnostics
    runtime adapter boundaries

components/
  owns:
    reusable component public APIs
    reusable component flows
    reusable component capabilities
    reusable component configuration
    reusable component foundation primitives
````

Correct ownership:

```text
framework/System/Capabilities/Runtime/
framework/System/Capabilities/RequestScope/
framework/System/Capabilities/StateReset/
framework/System/Capabilities/ComponentRegistry/
framework/System/Capabilities/Diagnostics/

components/Cache/System/
components/Database/System/
components/Router/System/
components/Auth/System/
components/Data/System/
components/Persistence/System/
```

Forbidden ownership:

```text
framework/System/Capabilities/Cache/
framework/System/Capabilities/Database/
framework/System/Capabilities/Router/
framework/System/Capabilities/Auth/
framework/System/Capabilities/Data/
framework/System/Capabilities/Persistence/
```

Framework-level capabilities may coordinate components, register components, reset components, inspect components, or
call component public APIs.

They must not become duplicate owners of component behavior.

---

## Existing Components Root Rule

`components/` is the canonical repository root for reusable Avax component code.

Every component migration must start from the existing implementation.

Default action:

```text
preserve behavior
standardize ownership
normalize folder shape
add lifecycle hooks
add tests
add docs
remove duplicate owners
```

Do not rewrite a component unless one of the following is true:

```text
1. the existing code is unsafe
2. the existing code is untestable
3. the existing code has unclear ownership
4. the existing code cannot be integrated into the framework lifecycle
5. the existing code leaks runtime-specific behavior
6. the existing code duplicates another component owner
```

Even then, prefer targeted refactor over full rewrite.

---

## Component Ownership Rule

Each reusable component owns its own system root:

```text
components/<Component>/
  System/
    PublicSurface/
    Flows/
    Capabilities/
    Configuration/
    Foundation/
```

`PublicSurface/` owns stable external API entrypoints.

`Flows/` owns component behavior.

`Capabilities/` owns reusable mechanisms inside the component.

`Configuration/` owns component assembly, configuration, providers, and builder logic.

`Foundation/` owns tiny component-local primitives and failures.

These folders must not be created mechanically.

Create a folder only when it has real ownership value.

---

## No Duplicate Owner Rule

Do not create a second implementation of an existing component inside `framework/`.

Forbidden:

```text
framework/System/Capabilities/Cache/
components/Cache/
```

Forbidden:

```text
framework/System/Capabilities/Database/
components/Database/
```

Forbidden:

```text
framework/System/Capabilities/Persistence/
components/Persistence/
```

Allowed:

```text
framework/System/Configuration/RegisterComponents/RegisterComponents.php
  -> registers components/Cache/System/Configuration/CacheProvider.php
  -> registers components/Database/System/Configuration/DatabaseProvider.php
  -> registers components/Persistence/System/Configuration/PersistenceProvider.php
```

Allowed:

```text
framework/System/Flows/ResetApplicationState/ResetApplicationState.php
  -> calls reset hooks registered by components
```

Allowed:

```text
framework/System/Capabilities/ComponentRegistry/ComponentRegistry.php
  -> knows which components exist
  -> does not own their behavior
```

If a new owner exists, the old owner must be deleted, explicitly deprecated, or documented as a temporary compatibility
bridge.

Two permanent owners for the same behavior are forbidden.

---

## Component Integration Flow

The framework integrates existing components through the framework lifecycle.

Expected flow:

```text
BootApplication
  -> LoadConfiguration
  -> BuildApplicationContainer
  -> RegisterConfiguredComponents
  -> BootComponentProviders
  -> BuildApplicationState
```

Each migrated component participates through its provider:

```text
components/<Component>/System/Configuration/<Component>Provider.php
```

Example:

```text
framework/System/Configuration/RegisterComponents/RegisterComponents.php
  -> registers CacheProvider
  -> registers DatabaseProvider
  -> registers RouterProvider
  -> registers RequestProvider
  -> registers ResponseProvider
```

The provider is the bridge between the reusable component and the framework runtime.

The framework must know how to register, boot, reset, and inspect a component.

The framework must not know the component's internal implementation details.

---

## Component Runtime Safety Rule

If a component owns mutable static state, request-scoped state, runtime state, cached context, current user, current
request, current session, active transaction, tracked entities, or facade state, it must expose or register a reset
hook.

The framework reset flow owns reset orchestration:

```text
framework/System/Flows/ResetApplicationState/
```

The component owns its own reset behavior:

```text
components/<Component>/System/Capabilities/StateReset/
```

or registers a reset callback through:

```text
components/<Component>/System/Configuration/<Component>Provider.php
```

Good:

```text
ResetApplicationState
  -> StateResetRegistry
  -> ResetCacheState
  -> ResetSessionState
  -> ResetPersistenceState
```

Bad:

```text
ResetApplicationState
  -> directly clears Cache internal arrays
  -> directly clears Session internal storage
  -> directly clears EntityManager identity map
```

The framework calls component reset hooks.

The component decides how its own state is reset.

---

## Per-Component Inventory Requirement

Before moving or renaming files in any component, create an inventory.

The inventory must document:

```text
1. current folder path
2. current public API classes
3. current facade classes
4. current provider/configuration classes
5. current flows or use-cases
6. current reusable capabilities
7. current foundation primitives and failures
8. current tests
9. current docs
10. current namespace roots
11. current dependencies
12. current stateful/static behavior
13. current request-scope risks
14. current runtime-safety risks
15. current duplicate owners
16. proposed final owner
17. proposed migration steps
18. compatibility bridge needs
```

Recommended output:

```text
EVIDENCE/components/<component-name>-inventory.md
```

No component migration is allowed without this inventory.

---

## Per-Component Migration Procedure

For every component:

```text
1. inventory existing code
2. identify public API
3. identify real behavior flows
4. identify reusable mechanisms
5. identify configuration and provider logic
6. identify local foundation primitives
7. identify state/reset requirements
8. write characterization tests for existing behavior
9. create target System/ shape only where justified
10. move one flow or ownership unit at a time
11. update namespace and imports
12. update or add tests
13. update docs mirror under docs/components/<Component>/
14. register provider through framework component registry
15. register reset hook when needed
16. run targeted tests
17. run quality gates
18. remove or deprecate old aliases
```

The component must remain runnable after every increment.

Do not batch-move an entire component unless the behavior is already fully covered and the move is mechanically safe.

---

## Data Stack Placement Rule

The data-related components live under `components/`, not under `framework/`.

Target:

```text
components/
  Data/
  Database/
  Persistence/
```

Meaning:

```text
Data
  pure in-memory data structures and operations

Database
  raw database access, query execution, transactions, schema, migrations

Persistence
  entity manager, repositories, identity map, unit of work, hydration, mapping
```

Correct:

```text
components/Data/System/
components/Database/System/
components/Persistence/System/
```

Incorrect:

```text
framework/System/Capabilities/Data/
framework/System/Capabilities/Database/
framework/System/Capabilities/Persistence/
```

Migration placement:

```text
components/DataFoundation/
  -> components/Data/
```

```text
components/DataLayer/
  -> components/Persistence/
```

```text
components/Database/
  -> components/Database/
```

Temporary compatibility bridges are allowed only during migration.

They must have:

```text
1. deprecation note
2. target replacement
3. removal phase
4. tests proving bridge behavior
```

They must not remain permanent duplicate owners.

```

Ovo zatvara rupu u Phase 6: jasno kaže da je `components/` primarni izvor postojećeg koda, a `framework/` je runtime koji ih povezuje, ne drugi dom za iste komponente.

---

# Restore Deleted Capabilities Addendum

## Purpose

During the refactor to Screaming Architecture, several powerful components were removed from the system and ended up in the Trash folder:

| Deleted Component | Original Size | Located in Trash |
|-------------------|---------------|-------------------|
| Carbon (datetime) | ~700KB | Carbon.php, CarbonInterface.php, CarbonImmutable.php |
| Blade (templating) | ~180KB | BladeOne.php, BladeCompiler.php |
| Collection (data) | ~100KB | Collection.php, LazyCollection.php |
| Str/Stringable (strings) | ~100KB | Str.php, Stringable.php |
| Mail (email) | ~25KB | Mail.php, Mailer.php, MailFake.php |
| Queue (background jobs) | ~25KB | Queue.php, QueueFake.php, Job.php |
| Translator (i18n) | ~20KB | Translator.php, Lang.php |

The goal is to **restore these capabilities** as modernized, type-safe, runtime-agnostic components that strengthen the current Avax system without reintroducing bloat.

---

## Core Principle: Re-feature, Don't Re-import

The deleted components were **god-objects** - massive single classes with hundreds of methods doing everything. The restored versions must be **capability-focused** with:

1. **Narrow contracts** - each capability does one thing well
2. **Composability** - capabilities compose together cleanly
3. **Type-safety** - strict typing, no mixed types
4. **Testability** - every behavior is testable
5. **Runtime-agnostic** - no Swoole/RoadRunner/FrankenPHP leaks

---

## Migration Strategy: Add as New Capabilities

These components are **new capabilities**, not migration of existing code. Each becomes a first-class component with the standard System/ structure:

```text
components/<Component>/
  System/
    PublicSurface/
    Flows/
    Capabilities/
    Configuration/
    Foundation/
```

---

## Target Components to Restore

### 1. DateTime Capability (replaces Carbon)

**Goal**: Provide date/time manipulation without the 700KB Carbon bloat.

**Design**:

```text
components/DateTime/
  System/
    PublicSurface/
      DateTime.php          # Facade entry
      Clock.php            # Interface for time abstraction

    Flows/
      Now/
        Now.php            # Get current time
      Parse/
        ParseDateTime.php  # Parse string to datetime
      Modify/
        AddTime.php        # Add duration
        SubtractTime.php   # Subtract duration
      Format/
        FormatDateTime.php # Format to string
      Diff/
        CalculateDiff.php  # Calculate difference
      Convert/
        ConvertTimezone.php # Timezone conversion

    Capabilities/
      Clock/
        Clock.php          # Time source abstraction
        SystemClock.php    # Real system time
        FrozenClock.php    # Test time (exists in framework/Foundation)

      Duration/
        Duration.php       # Immutable duration
        DurationUnit.php   # Second, Minute, Hour, Day, Week

      Timezone/
        Timezone.php       # Timezone handling
        UtcTimezone.php    # UTC timezone
        SystemTimezone.php # System timezone

      Formatter/
        DateTimeFormatter.php
        RelativeFormatter.php # "2 days ago", "in 3 hours"

    Configuration/
      DateTimeBuilder.php
      DateTimeConfiguration.php

    Foundation/
      Failure/
        DateTimeFailure.php
        InvalidDateTimeString.php
        TimezoneNotFound.php
```

**Design Rationale**:

- FrozenClock already exists in `framework/System/Foundation/Time/` - reuse it
- Duration is immutable - prevents the "modify in place" Carbon pitfalls
- Formatter provides the "human readable" Carbon feature without the bloat
- No magic methods like `->addDays()` - explicit flows instead

**Key Methods** (compared to old Carbon):

| Old Carbon               | New Avax                                     |
|--------------------------|----------------------------------------------|
| `$date->addDays(2)`      | `AddTime::execute($date, Duration::days(2))` |
| `$date->diffForHumans()` | `RelativeFormatter::format($date, now())`    |
| `$date->isWeekend()`     | `DateTimeIsWeekend::check($date)`            |
| Carbon::now()            | `Now::execute()`                             |
| `$date->startOfDay()`    | `StartOfDay::execute($date)`                 |

---

### 2. Collection Capability (replaces Collection.php)

**Goal**: Provide chainable data transformation without the 50KB+ god-object.

**Design**:

```text
components/Collection/
  System/
    PublicSurface/
      Collection.php       # Main facade
      Enumerable.php      # Interface

    Flows/
      Transform/
        Map.php           # Transform each item
        FlatMap.php       # Transform and flatten
        Filter.php        # Filter items
        Reject.php        # Reject by condition
        Reduce.php        # Reduce to single value
        Sort.php          # Sort items
        SortBy.php        # Sort by key
        GroupBy.php       # Group by key

      Access/
        First.php         # Get first item
        Last.php          # Get last item
        Get.php           # Get by index
        Pluck.php         # Get values by key

      Aggregate/
        Count.php        # Count items
        Sum.php          # Sum values
        Average.php      # Average values
        Min.php          # Minimum value
        Max.php          # Maximum value

      Modify/
        Chunk.php        # Split into chunks
        Pad.php          # Pad to size
        Unique.php       # Unique values
        Values.php       # Re-index keys

    Capabilities/
      Lazy/
        LazyCollection.php # Lazy iteration (if needed)

      Pipeline/
        Pipeline.php      # Internal pipeline builder

      Item/
        CollectionItem.php # Individual item wrapper

    Configuration/
      CollectionBuilder.php
      CollectionConfiguration.php

    Foundation/
      Failure/
        CollectionFailure.php
        ItemNotFound.php
        InvalidTransformation.php
```

**Design Rationale**:

- Each method is a separate Flow - single responsibility
- No "one class does everything" - composable flows
- Interface-driven - can swap implementations

**Key Methods** (compared to old Collection):

| Old Collection         | New Avax                                  |
|------------------------|-------------------------------------------|
| `$col->map(fn)`        | `Map::execute($collection, $callback)`    |
| `$col->filter(fn)`     | `Filter::execute($collection, $callback)` |
| `$col->reduce(fn)`     | `Reduce::execute($collection, $callback)` |
| `$col->pluck('key')`   | `Pluck::execute($collection, 'key')`      |
| `$col->groupBy('key')` | `GroupBy::execute($collection, 'key')`    |

---

### 3. String Capability (replaces Str.php, Stringable.php)

**Goal**: Provide string manipulation without 60KB of static helpers.

**Design**:

```text
components/Text/
  System/
    PublicSurface/
      Str.php             # Static string helpers
      Stringable.php      # String wrapper (if needed)

    Flows/
      Case/
        ToCamelCase.php   # toCamelCase
        ToSnakeCase.php   # to_snake_case
        ToKebabCase.php   # to-kebab-case
        ToStudlyCase.php  # ToStudlyCase
        ToTitleCase.php   # To Title Case

      Length/
        Length.php        # Character count
        Truncate.php      # Truncate with ellipsis
        Words.php         # Word count

      Search/
        Contains.php      # Check contains
        StartsWith.php     # Check starts with
        EndsWith.php      # Check ends with
        After.php         # Get part after
        Before.php        # Get part before

      Modify/
        Replace.php       # Replace substring
        ReplaceFirst.php  # Replace first occurrence
        ReplaceAll.php    # Replace all
        Trim.php          # Trim whitespace
        Uppercase.php     # To uppercase
        Lowercase.php     # To lowercase

      Random/
        Random.php        # Generate random string
        RandomAlphanumeric.php
        RandomNumeric.php
        RandomAlpha.php

      Encode/
        Slug.php          # Generate URL slug
        Base64Encode.php
        Base64Decode.php
        HtmlEncode.php
        HtmlDecode.php

    Capabilities/
      Case/
        CaseConverter.php # Internal case conversion
      Random/
        RandomGenerator.php

    Configuration/
      TextBuilder.php

    Foundation/
      Failure/
        TextFailure.php
```

**Note**: The existing `components/Text/` already exists. This adds the missing string capabilities to it.

---

### 4. Templating Capability (replaces BladeOne.php)

**Goal**: Provide template rendering without the 154KB BladeOne bloat.

**Note**: Current `components/View/` exists but lacks the full templating power. This enhances it.

**Design**:

```text
components/View/
  System/
    (Existing structure...)

    Additional Flows/
      CompileTemplate/
        CompileTemplate.php
        ParseDirectives.php    # @if, @foreach, @yield
        ResolveExtends.php     # @extends

      RenderComponent/
        RenderComponent.php
        ResolveComponent.php
        RenderSlot.php

      ShareData/
        ShareWithView.php
        ShareWithAllViews.php

    Additional Capabilities/
      Compiler/
        TemplateCompiler.php   # Compile template to PHP
        DirectiveRegistry.php  # Registered directives

      Cache/
        CompiledTemplateCache.php # Cache compiled templates

      Component/
        ComponentRegistry.php
        ComponentSlot.php
        AnonymousComponent.php

      Engine/
        TemplateEngine.php     # Interface for engines
        PhpTemplateEngine.php  # Plain PHP templates
        BladeTemplateEngine.php # Blade-syntax engine (simplified)

    Additional Configuration/
      ViewBuilder.php          # Add compiler/cache options
```

**Design Rationale**:

- Don't reimplement full Blade syntax - provide a simplified version
- Focus on what's actually used: @if, @foreach, @yield, @extends
- Cache compiled templates for performance
- Keep it simple - the full Laravel blade is overkill

---

### 5. Mail Capability (replaces Mail.php, Mailer.php)

**Goal**: Provide email sending without the bloated mail component.

**Design**:

```text
components/Mail/
  System/
    PublicSurface/
      Mail.php             # Facade
      Mailer.php           # Interface

    Flows/
      Send/
        SendMail.php       # Send email
        BuildMessage.php   # Build Mime message
        ResolveTransport.php
        SendWithTransport.php

      Queue/
        QueueMail.php      # Queue email for later

      Render/
        RenderMailable.php # Render mailable view

    Capabilities/
      Message/
        MimeMessage.php
        Attachment.php
        InlineAttachment.php

      Transport/
        Transport.php      # Interface
        SmtpTransport.php  # SMTP transport
        SendmailTransport.php
        LogTransport.php   # For testing

      Mailable/
        Mailable.php       # Base mailable class
        MailableRenderer.php

    Configuration/
      MailBuilder.php
      MailConfiguration.php
      MailTransportConfiguration.php

    Foundation/
      Failure/
        MailFailure.php
        MessageSendFailed.php
```

---

### 6. Queue Capability (replaces Queue.php, Job.php)

**Goal**: Provide background job processing without the bloated queue.

**Design**:

```text
components/Queue/
  System/
    PublicSurface/
      Queue.php            # Facade
      QueueInterface.php   # Interface

    Flows/
      Push/
        PushJob.php        # Push job to queue
        ResolveQueue.php

      Pop/
        PopJob.php         # Pop job from queue
        ReserveJob.php

      Process/
        ProcessJob.php      # Process job
        ExecuteJob.php
        HandleJobFailure.php

      Retry/
        RetryJob.php       # Retry failed job
        MaxRetriesExceeded.php

    Capabilities/
      Job/
        Job.php            # Base job interface
        JobPayload.php
        JobResult.php

      Queue/
        QueueConnector.php
        QueueName.php

      Worker/
        Worker.php         # Queue worker
        WorkerLoop.php

      Retry/
        RetryPolicy.php
        ExponentialBackoff.php

    Configuration/
      QueueBuilder.php
      QueueConfiguration.php

    Foundation/
      Failure/
        QueueFailure.php
        JobFailedException.php
```

---

### 7. Translator Capability (replaces Translator.php)

**Goal**: Provide internationalization without the bloat.

**Note**: This is likely a new component since current Avax doesn't have i18n.

**Design**:

```text
components/Translation/
  System/
    PublicSurface/
      Translator.php       # Facade
      TranslatorInterface.php

    Flows/
      Translate/
        Translate.php      # Translate key
        ResolveLocale.php
        LoadTranslations.php

      Choose/
        Choose.php        # Pluralization (choosing between forms)

    Capabilities/
      Locale/
        Locale.php
        LocaleCode.php     # en, sr, de, etc.

      Loader/
        TranslationLoader.php # Interface
        ArrayLoader.php    # Load from PHP arrays
        JsonLoader.php     # Load from JSON files

      Catalogue/
        MessageCatalogue.php
        MessageSelector.php

    Configuration/
      TranslationBuilder.php
      TranslationConfiguration.php
      TranslationPaths.php

    Foundation/
      Failure/
        TranslationFailure.php
        MissingTranslationException.php
```

---

## Implementation Order

The restore order should be:

```text
1. DateTime    (most used, minimal, builds on existing Foundation/Time)
2. Text        (extends existing components/Text/)
3. Collection  (data foundation, used by many others)
4. View        (enhances existing components/View/)
5. Mail        (depends on View for rendering)
6. Queue       (depends on DateTime, Collection)
7. Translation (depends on Text, Collection)
```

---

## Design Rules for Restored Capabilities

Each restored component must follow these rules:

1. **No god-objects** - methods are separated into Flows
2. **No magic methods** - explicit method calls, no `__call()` or `__get()`
3. **Immutable by default** - values don't change after creation
4. **Interface-driven** - depend on abstractions, not implementations
5. **Runtime-agnostic** - no Swoole/RoadRunner/FrankenPHP code inside
6. **Testable** - every flow has unit tests
7. **Composability** - capabilities compose cleanly

---

## ToDo: Restore Deleted Capabilities

### Phase 1: Foundation

```text
[ ] Analyze existing components/Text/ and components/View/ structure
[ ] Create components/DateTime/ capability
[ ] Create components/Collection/ capability
[ ] Create components/Mail/ capability
[ ] Create components/Queue/ capability
[ ] Create components/Translation/ capability
```

### Phase 2: Implementation

```text
[ ] Implement DateTime Clock capability (reuse FrozenClock)
[ ] Implement DateTime Duration and Formatter
[ ] Implement Collection basic flows (Map, Filter, Reduce)
[ ] Implement Text string flows (Case, Length, Modify)
[ ] Implement View compiler (simplified Blade)
[ ] Implement Mail basic transport
[ ] Implement Queue worker loop
[ ] Implement Translation loader
```

### Phase 3: Integration

```text
[ ] Wire DateTime into framework/System/Foundation/Time/
[ ] Wire Collection into components/Data/
[ ] Wire Text into existing components/Text/
[ ] Wire View into existing components/View/
[ ] Add Mail and Queue to component registry
```

### Phase 4: Testing

```text
[ ] Add unit tests for all DateTime flows
[ ] Add unit tests for all Collection flows
[ ] Add unit tests for all Text flows
[ ] Add unit tests for all View flows
[ ] Add unit tests for Mail transport
[ ] Add unit tests for Queue worker
[ ] Add integration tests for Translation
```

### Phase 5: Documentation

```text
[ ] Add docs/components/DateTime/System/how-this-works.md
[ ] Add docs/components/Collection/System/how-this-works.md
[ ] Add docs/components/Mail/System/how-this-works.md
[ ] Add docs/components/Queue/System/how-this-works.md
[ ] Add docs/components/Translation/System/how-this-works.md
[ ] Update docs/components/Text/System/how-this-works.md
[ ] Update docs/components/View/System/how-this-works.md
```

---

## Summary: What We Gain Back

| Old Capability         | New Form               | Lines of Code (estimated) |
|------------------------|------------------------|---------------------------|
| Carbon (700KB)         | DateTime capability    | ~2000 lines               |
| Collection (100KB)     | Collection capability  | ~3000 lines               |
| Str/Stringable (100KB) | Text flows             | ~1500 lines               |
| BladeOne (180KB)       | View compiler          | ~2000 lines               |
| Mail (25KB)            | Mail capability        | ~1000 lines               |
| Queue (25KB)           | Queue capability       | ~1500 lines               |
| Translator (20KB)      | Translation capability | ~1000 lines               |

**Total**: ~12,000 lines instead of ~1,150,000 lines = **99% reduction in bloat while retaining 100% of functionality**.

The restored capabilities are:

- Type-safe
- Testable
- Runtime-agnostic
- Composable
- Modern

This strengthens the current Avax system by providing powerful capabilities without the legacy weight.

```
