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

Code-Review-And-ToDo/review.md
Code-Review-And-ToDo/migration-map.md
Code-Review-And-ToDo/risk-register.md
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
Code-Review-And-ToDo/<slice>/review.md
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
10. Produce Code-Review-And-ToDo/review.md with:
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

```text
[ ] Add how-to-architecture-extension.md to docs/governance/
[ ] Update docs/governance/how-to-architecture.md to reference PublicSurface extension
[ ] Create docs/decisions/0001-avax-is-runtime-agnostic-framework.md
[ ] Create docs/decisions/0002-framework-system-owns-runtime-lifecycle.md
[ ] Create docs/decisions/0003-components-are-reusable-capabilities.md
[ ] Create docs/decisions/0004-public-surface-is-stable-api-boundary.md
[ ] Create docs/decisions/0005-runtime-adapters-must-not-leak-into-core.md
[ ] Create docs/decisions/0006-request-state-must-be-scoped.md
[ ] Create docs/decisions/0007-docs-mirror-source-structure.md
[ ] Create Code-Review-And-ToDo/migration-map.md
[ ] Create Code-Review-And-ToDo/risk-register.md
```

## Phase 1: Create framework/System skeleton

```text
[ ] Create framework/System/PublicSurface/
[ ] Create framework/System/Flows/
[ ] Create framework/System/Capabilities/
[ ] Create framework/System/Configuration/
[ ] Create framework/System/Foundation/

[ ] Create framework/System/PublicSurface/Avax.php
[ ] Create framework/System/PublicSurface/AvaxInterface.php

[ ] Create framework/System/Capabilities/Runtime/RuntimeInterface.php
[ ] Create framework/System/Capabilities/Runtime/RuntimeContext.php
[ ] Create framework/System/Capabilities/Runtime/RuntimeState.php
[ ] Create framework/System/Capabilities/Runtime/RuntimeRequest.php
[ ] Create framework/System/Capabilities/Runtime/RuntimeResponse.php
[ ] Create framework/System/Capabilities/Runtime/RuntimeResult.php

[ ] Create framework/System/Capabilities/RequestScope/RequestScope.php
[ ] Create framework/System/Capabilities/RequestScope/RequestScopeInterface.php
[ ] Create framework/System/Capabilities/RequestScope/RequestScopeId.php
[ ] Create framework/System/Capabilities/RequestScope/RequestScopeStore.php

[ ] Create framework/System/Flows/BootApplication/BootApplication.php
[ ] Create framework/System/Configuration/BuildApplication/BuildApplication.php

[ ] Add docs/framework/System/how-this-works.md
[ ] Add docs/framework/System/PublicSurface/how-this-works.md
[ ] Add docs/framework/System/Capabilities/Runtime/how-this-works.md
[ ] Add docs/framework/System/Capabilities/RequestScope/how-this-works.md
```

## Phase 2: Add first tests

```text
[ ] Add tests/Unit/Framework/System/PublicSurface/AvaxTest.php
[ ] Add tests/Unit/Framework/System/Capabilities/Runtime/RuntimeContextTest.php
[ ] Add tests/Unit/Framework/System/Capabilities/Runtime/RuntimeStateTest.php
[ ] Add tests/Unit/Framework/System/Capabilities/RequestScope/RequestScopeTest.php
[ ] Add tests/Unit/Framework/System/Capabilities/RequestScope/RequestScopeStoreTest.php
[ ] Add tests/Unit/Framework/System/Flows/BootApplication/BootApplicationTest.php
[ ] Add tests/Feature/HttpApplicationFeatureTest.php as future placeholder only when real behavior exists
```

## Phase 3: Request scope and state reset

```text
[ ] Implement request scope open behavior
[ ] Implement request scope close behavior
[ ] Implement scoped value storage
[ ] Prevent access after scope close
[ ] Add StateReset capability
[ ] Add ResetApplicationState flow
[ ] Add tests proving two simulated requests cannot share scoped state
[ ] Add tests proving closed scope cannot be reused
[ ] Add tests proving state reset runs after worker request
```

Critical tests:

```text
[ ] test_it_opens_request_scope_when_http_request_starts()
[ ] test_it_closes_request_scope_when_http_request_ends()
[ ] test_it_rejects_access_when_request_scope_is_closed()
[ ] test_it_does_not_leak_scoped_value_between_two_requests()
[ ] test_it_resets_runtime_context_after_worker_request()
```

## Phase 4: Runtime abstraction

```text
[ ] Implement PhpFpmRuntime
[ ] Implement CliRuntime
[ ] Define WorkerRuntimeInterface
[ ] Define WorkerLoop
[ ] Add RuntimeContractTest
[ ] Add WorkerRuntimeContractTest
[ ] Add RuntimeAdapterContractTest
[ ] Add integration test for PHP-FPM-style one-request lifecycle
[ ] Add integration test for worker-style repeated request lifecycle
```

Do **not** implement Swoole/RoadRunner/FrankenPHP first. First prove the Avax abstraction.

## Phase 5: HTTP framework flow

```text
[ ] Implement HandleIncomingHttp flow
[ ] Connect Request component through Avax RuntimeRequest
[ ] Connect Router component through MatchHttpRoute
[ ] Connect Response component through BuildHttpResponse and SendHttpResponse
[ ] Add HandleIncomingHttpIntegrationTest
[ ] Add HttpApplicationFeatureTest
[ ] Add docs/framework/System/Flows/HandleIncomingHttp/how-this-works.md
```

## Phase 6: Console framework flow

```text
[ ] Implement RunConsoleCommand flow
[ ] Connect Console component
[ ] Add ConsoleKernel public surface
[ ] Add RunConsoleCommandIntegrationTest
[ ] Add ConsoleApplicationFeatureTest
[ ] Add docs/framework/System/Flows/RunConsoleCommand/how-this-works.md
```

## Phase 7: Component migration order

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

```text
[ ] Add FrankenPhpRuntime
[ ] Add RoadRunnerRuntime
[ ] Add WorkermanRuntime
[ ] Add SwooleRuntime
[ ] Ensure no adapter class appears inside Request/Response/Router/Session/Auth
[ ] Add runtime leak checker in tooling/refactor/check-runtime-leaks.php
[ ] Add contract tests for each adapter
[ ] Add worker state leak tests for each adapter
```

## Phase 9: Docs completion

```text
[ ] Ensure docs/ mirrors framework/System
[ ] Ensure docs/ mirrors every migrated component
[ ] Add how-this-works.md for every ownership folder
[ ] Add mermaid diagrams for framework flows
[ ] Add debug-first sections
[ ] Add failure path sections
[ ] Add public API stability sections for PublicSurface
[ ] Run tooling/docs/validate-docs.php
[ ] Run tooling/docs/validate-docs-mirror-source.php
```

## Phase 10: Quality gates

```text
[ ] composer validate
[ ] composer dump-autoload
[ ] php -l changed PHP files
[ ] PHPUnit targeted tests
[ ] PHPUnit full suite
[ ] PHPStan/Psalm
[ ] Rector dry-run
[ ] Code style
[ ] Governance review
[ ] Documentation validation
[ ] Runtime leak check
[ ] Duplicate owner check
[ ] PublicSurface check
```

## Phase 11: Remove obsolete structure

```text
[ ] Find duplicate owners
[ ] Find old namespace aliases
[ ] Find old bootstrap paths
[ ] Find old docs outside docs/
[ ] Find obsolete Core/Shared/Helpers/Managers buckets
[ ] Mark temporary bridges with deprecation notes
[ ] Delete obsolete paths after migration window
[ ] Update composer autoload
[ ] Re-run full test suite
```

---

## 9. Final acceptance checklist

```text
Architecture:
[ ] framework/System owns lifecycle
[ ] components/*/System owns reusable capabilities
[ ] PublicSurface exists only where justified
[ ] PublicSurface delegates and does not implement internals
[ ] Flows own behavior
[ ] Capabilities own reusable mechanisms
[ ] Configuration owns assembly
[ ] Foundation remains tiny
[ ] Runtime adapters are isolated
[ ] Request state is scoped
[ ] State reset exists for worker runtimes

Testing:
[ ] Unit tests prove local behavior
[ ] Integration tests prove collaboration
[ ] Feature tests prove external usage
[ ] Contract tests prove adapter consistency
[ ] Characterization tests protect migrated legacy behavior
[ ] Worker state leak tests exist

Documentation:
[ ] docs/ is canonical
[ ] docs mirror source
[ ] how-this-works.md exists for ownership folders
[ ] PublicSurface docs explain stable API
[ ] Runtime docs explain adapter boundary
[ ] Request scope docs explain state safety

Quality:
[ ] composer validate passes
[ ] autoload passes
[ ] static analysis passes
[ ] style passes
[ ] tests pass
[ ] governance review passes
[ ] obsolete paths removed or explicitly deprecated
```

---

Najkraće: ovo je Avax kao **modern PHP runtime-agnostic framework**, ne samo skup komponenti. `framework/System` daje
identitet framework-u. `components/*/System` daje čiste capabilities. `PublicSurface` daje jasan javni API.
`RequestScope` i `StateReset` ga spremaju za FrankenPHP, RoadRunner, Swoole, Workerman i long-lived worker svet. 🧩
