# TODO-006 Object Graph Map

## Selected TODO

- TODO: `TODO-006`
- title: Move framework public entrypoint object-graph assembly out of runtime/PublicSurface
- status before this task: OPEN / P0 BLOCKER
- analysis mode: analysis-first
- implementation decision: MAY PROCEED with smallest safe slice

## Public Entrypoints Involved

- `framework/System/PublicSurface/Avax.php`
  - `Avax::create()`
  - `Avax::boot()`
  - `Avax::dsl()`
  - `Avax::bootInternal()`
- `framework/System/PublicSurface/BootDsl.php`
  - `BootDsl::make()`
  - `BootDsl::create()`
- `framework/System/PublicSurface/App.php`
  - `App::handle()`
  - `App::asHttpKernel()`
  - `App::asConsoleKernel()`
  - `App::asRuntimeKernel()`
  - `App::handleRequest()`

## Where Object Graph Assembly Happened Before Slice A

- `Avax::create()` directly constructs `CreateHttpResponse`, `CreateRequestFromGlobals`, request readers/normalizers, `CreateApplication`, `SystemClock`, `ProjectPath`, `ComponentRegistry`, `RequestScopeStore`, `RuntimeContext`, `StateResetRegistry`, and `HandleIncomingHttp`.
- `Avax::bootInternal()` directly constructs `CreateHttpResponse`, `HandleIncomingHttp`, `BootApplication`, `BuildApplicationState`, `ComponentRegistry`, `RequestScopeStore`, `RuntimeContext`, `StateResetRegistry`, `HttpKernel`, `ConsoleKernel`, `RunConsoleCommand`, `PreCommitConfig`, `PreCommit`, `RuntimeKernel`, and `ResetApplicationState`.
- `BootDsl::create()` directly constructs `SystemClock` fallback, `ProjectPath`, `CreateHttpResponse`, `HandleIncomingHttp`, `ProviderRegistry`, and `BootDslEngine`.
- `App::handle()` directly constructs `OpenHttpRequestScope` and `CloseHttpRequestScope` per request.
- `App::ensureInitialized()` lazily constructed `RunApplication` through `RunApplication::withDefaultResolutionPipeline()`.
- `App::asHttpKernel()` creates an anonymous `HttpKernelInterface` adapter.
- `App::asConsoleKernel()` directly constructs `ConsoleKernel`, `RunConsoleCommand`, `PreCommitConfig`, and `PreCommit`.
- `App::asRuntimeKernel()` directly constructs `RuntimeKernel`.
- `RunApplication::withDefaultResolutionPipeline()` directly constructed `RouteFacadeContainer`, `ResolveCallable`, `ControllerResolver`, `ArgumentResolver`, `SecureRequestInputBuilder`, `ReadIncomingHttpRequest`, `MatchHttpRoute`, `MatchRoute`, and `RunApplication`.
- `CreateApplication::make()` and `CreateApplication::fromBuilder()` assemble runtime state, reset registry, `Runtime`, `ResetApplicationState`, `NormalizeControllerResult`, and `App`.
- `BootDslEngine::createRuntimeAndApp()` assembles runtime state, reset registry, `Runtime`, `CreateHttpResponse`, `NormalizeControllerResult`, `CreateRequestFromGlobals`, `ResetApplicationState`, and `App`.

## PublicSurface Files Constructing Runtime/Internal Objects Directly

- `framework/System/PublicSurface/Avax.php`
- `framework/System/PublicSurface/BootDsl.php`
- `framework/System/PublicSurface/App.php`

## Runtime/Flow Files Constructing Object Graphs Directly

- `framework/System/Flows/RunApplication/RunApplication.php`
- `framework/System/Flows/CreateApplication/CreateApplication.php`

## Configuration Files Already Owning Assembly

- `framework/System/Configuration/BootDsl/BootDslEngine.php`
- `framework/System/Configuration/BuildApplication/Builders/BuildApplication.php`
- `framework/System/Configuration/BuildApplication/Builders/ApplicationBuilder.php`
- `framework/System/Configuration/Builders/BuildDispatchConfiguredRoute.php`

## Correct Internal Owner For Assembly

Assembly belongs under `framework/System/Configuration/**`.

For the first safe slice, the correct owner is:

- `framework/System/Configuration/Builders/BuildRunApplication.php`

Reason:

- `RunApplication` is a runtime flow and should receive ready collaborators.
- The default route dispatch pipeline is configuration-time object graph assembly, similar to `BuildDispatchConfiguredRoute`.
- `App` should receive a ready `RunApplication` dispatcher instead of lazily assembling one from PublicSurface.

## Allowed Files

Implementation may touch only:

- `framework/System/Configuration/Builders/BuildRunApplication.php` (new)
- `framework/System/PublicSurface/App.php`
- `framework/System/Flows/RunApplication/RunApplication.php`
- `framework/System/Flows/CreateApplication/CreateApplication.php`
- `framework/System/Configuration/BootDsl/BootDslEngine.php`
- focused tests under:
  - `tests/Unit/Framework/V4RuntimeApp/`
  - `tests/Unit/Framework/Configuration/BootDsl/`
  - `tests/Unit/Framework/V4HealthEndpoints/` only if constructor helper needs update
- task evidence under `.agents/management/evidence/generated/maximum-remaining-backlog-sweep/todo-006-framework-entrypoint-object-graph/`

## Forbidden Files

- `fix-this.md`
- `TODO.md`
- unrelated components
- `components/Identity/Auth/**` (TODO-007)
- broad framework cleanup outside the listed files
- composer/autoload changes
- roadmap docs
- old V5.9/AuthBuilder evidence
- generated dumps or cache files

## Smallest Safe Remediation Slice

Slice A:

1. Add `BuildRunApplication` under `framework/System/Configuration/Builders`.
2. Move the `RunApplication::withDefaultResolutionPipeline()` object graph into `BuildRunApplication`.
3. Remove the static object-graph factory from `RunApplication`.
4. Change `App` to receive a ready `RunApplication` dispatcher through its constructor.
5. Remove lazy `App::ensureInitialized()` dispatch assembly.
6. Update existing App creation points in `CreateApplication` and `BootDslEngine` to pass the dispatcher.
7. Run focused tests and gates.

Slice A implemented:

- `BuildRunApplication::fromDefaultResolutionPipeline()` now owns the default dispatch pipeline assembly under Configuration.
- The builder is static and stateless.
- `App` receives a ready `RunApplication`.
- `RunApplication::withDefaultResolutionPipeline()` and `App::ensureInitialized()` were removed.
- `CreateApplication` and `BootDslEngine` pass the configured dispatcher into `App`.

Why this slice is safe:

- It does not change public API signatures on `Avax::create()`, `Avax::dsl()`, route registration, or `App::handle()`.
- It removes the highest-confidence lazy runtime assembly path without touching TODO-007.
- It keeps behavior equivalent by reusing the same object graph in a Configuration owner.
- Direct `new` remains in configuration/building code, which is the approved owner.

## Known Residual TODO-006 Work After Slice A

- `Avax::create()` still assembles a `CreateApplication` object graph.
- `Avax::bootInternal()` still assembles full kernels.
- `BootDsl::create()` still assembles BootDslEngine inputs.
- `App::asHttpKernel()`, `App::asConsoleKernel()`, and `App::asRuntimeKernel()` still construct compatibility adapters.
- `CreateApplication` still owns runtime/App assembly and may need a later configuration-owner extraction.

These are not part of Slice A.

## Implementation May Proceed

YES.

Ownership is clear for Slice A:

- PublicSurface should receive and delegate.
- `RunApplication` should execute.
- `BuildRunApplication` under Configuration should assemble.

Stop condition:

- If focused tests show constructor/API fallout broader than listed files, stop and mark TODO_PARTIAL/BLOCKED with evidence instead of expanding scope.

Post-validation decision:

- Slice A may proceed to commit and self-review.
- TODO-006 remains open for later slices.
