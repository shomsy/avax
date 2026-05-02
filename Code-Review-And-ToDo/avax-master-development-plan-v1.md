# AvaX Master Development Plan v1

**Status:** canonical roadmap draft  
**Purpose:** one place that explains where AvaX is going, what must be fixed first, what comes later, and what
“production-ready” means.

---

## 0. North Star

AvaX is a capability-first, runtime-agnostic PHP framework for building, testing, and evolving system-design-grade
application architectures.

AvaX is not trying to replace Kafka, Redis Cluster, CDN, Kubernetes, object storage, search engines, or service meshes.
AvaX organizes the application architecture that integrates with infrastructure.

AvaX should become:

1. A clean runtime framework.
2. A strict capability/component architecture.
3. An agent-friendly codebase.
4. A production-ready PHP runtime layer.
5. A system-design application framework.
6. A reference platform for building examples like URL Shortener, Parking Lot, Distributed Rate Limiter, News Feed,
   Video Platform Metadata, and similar large-system architecture exercises.

---

## 1. Non-negotiable execution law

```text
Architecture without proof is not production readiness.
No feature expansion while taxonomy, autoload, namespace integrity, tests, and static analysis are RED.
No component is complete only because folders exist.
No empty placeholder classes.
No describeResponsibility-only classes.
No generic Manager/Service/Helper/Util/Support naming.
PublicSurface receives. Flows execute. Capabilities power. Configuration assembles. Foundation supports.
```

---

## 2. Stage map

```text
Stage 00: Current Truth Lock
Stage 01: Final Project Tree Freeze
Stage 02: Taxonomy Integrity Green
Stage 03: API Classification and Evolution Rules
Stage 04: Component Completion
Stage 05: Canonical Class Map
Stage 06: Autoload and Namespace Repair
Stage 07: Test Layer Repair
Stage 08: Static Analysis Green
Stage 09: AvaX Kernel Green
Stage 10: Production Readiness Baseline
Stage 11: Golden Path App
Stage 12: Public API and Compatibility Governance
Stage 13: Extension and Plugin Architecture
Stage 14: Benchmark and Performance Budget Suite
Stage 15: Observability Contract
Stage 16: Security Threat Model
Stage 17: Failure Simulation and Runtime Resilience
Stage 18: Package Split Readiness
Stage 19: Release, Upgrade and Migration Policy
Stage 20: System Design Kit
Stage 21: Reference Architectures
Stage 22: System Design Example Applications
Stage 23: Final Documentation and Positioning
```

---

# PART I: CANONICAL PROJECT TREE

## 3. Final repository tree

```text
avax/
  AGENTS.md
    Project-specific agent execution contract.
    Must say: read CURRENT_TRUTH.md first.
    Must point to this master plan.
    Must define forbidden work, validation gates, and done definition.

  CURRENT_TRUTH.md
    Single source of truth for current state.
    Contains date, architecture status, taxonomy status, autoload status, test status, static analysis status, production readiness status, blockers, forbidden work, and next 5 actions.

  README.md
    Public positioning document.
    Explains what AvaX is, what it is not, quickstart, architecture summary, golden path, and system-design direction.

  composer.json
    Composer package metadata and PSR-4 autoload.
    Final production autoload should point to framework/ and components/.
    Test autoload should point to tests/.

  composer.lock
    Locked dependencies.
    Must not change during taxonomy-only or documentation-only work.

  phpunit.xml
  phpstan.neon
  psalm.xml
  rector.php

  bin/
    avax
      CLI executable entrypoint.
      Delegates to framework/System/PublicSurface/ConsoleKernel.php or framework/System/Flows/RunConsoleCommand.

  framework/
    System/
      Runtime lifecycle owner.
      Owns boot, request handling, console handling, workers, runtime adapters, request scope, reset safety, and shutdown.

  components/
    Application/
    HTTP/
    CLI/
    DataStack/
    Identity/
    Security/
    Operations/
    Presentation/
    DeveloperTools/
      Reusable capability suites.
      Must not own framework runtime lifecycle.

  tests/
    Architecture/
    Unit/
    Integration/
    Feature/
    PublicApi/
    Compatibility/
    Support/
      Canonical test root.
      Namespace: Avax\Tests\...

  docs/
    architecture/
    decisions/
    framework/
    components/
    examples/
    governance/
    security/
    system-design/
      Canonical documentation.
      Docs must describe actual source, not desired future.

  examples/
    golden-path-app/
    system-design/
      Runnable examples.

  reference-architectures/
    url-shortener/
    parking-lot/
    distributed-rate-limiter/
    news-feed/
    video-platform-metadata/

  labs/
    SystemDesignKit/
      Experimental system-design tooling before promotion.

  benchmarks/
    container/
    router/
    middleware/
    cache/
    event-dispatch/
    queue-dispatch/
    response-build/
    url-shortener-redirect-path/

  tooling/
    refactor/
    quality/
    architecture/
    pre-commit/
    release/

  build/
    canonical-class-map.json
    reports/

  Code-Review-And-ToDo/
    master-plan/
    component-taxonomy/
    component-completion/
    test-repair/
    production-readiness/
    archive/
```

---

## 4. Final framework/System tree

```text
framework/System/
  PublicSurface/
    Avax.php
      Main public framework entrypoint. Delegates to BootApplication and runtime capabilities.
    AvaxInterface.php
      Stable public contract for main framework object.
    RuntimeKernel.php
      Public runtime kernel abstraction.
    RuntimeKernelInterface.php
      Stable public contract for runtime kernel.
    HttpKernel.php
      Public HTTP kernel abstraction.
    HttpKernelInterface.php
      Stable public contract for HTTP kernel.
    ConsoleKernel.php
      Public console kernel abstraction.
    ConsoleKernelInterface.php
      Stable public contract for console kernel.
    Runtime/
      Runtime.php
      RuntimeInterface.php

  Flows/
    BootApplication/
      BootApplication.php
      LoadConfiguration.php
      BuildContainer.php
      RegisterComponents.php
      BootProviders.php

    HandleIncomingHttp/
      HandleIncomingHttp.php
      OpenRequestScope.php
      ReadRuntimeRequest.php
      RunHttpPipeline.php
      CloseRequestScope.php
      ResetAfterRequest.php
      MapThrowableToResponse.php

    RunConsoleCommand/
      RunConsoleCommand.php
      ReadConsoleInput.php
      ResolveConsoleCommand.php
      ExecuteConsoleCommand.php
      RenderConsoleResult.php

    HandleWorkerRequest/
      HandleWorkerRequest.php
      AcceptWorkerRequest.php
      RunWorkerIteration.php
      RecoverWorkerFailure.php

    ResetApplicationState/
      ResetApplicationState.php
      ResetFacadeState.php
      ResetRequestScopedDependencies.php
      ResetLoggingContext.php
      ResetPersistenceState.php

    ShutdownRuntime/
      ShutdownRuntime.php
      DrainWorkers.php
      CloseConnections.php
      FlushTelemetry.php

  Capabilities/
    Runtime/
      RuntimeContext.php
      RuntimeMode.php
      RuntimeEnvironment.php

    RuntimeAdapters/
      PhpFpm/PhpFpmRuntimeAdapter.php
      PhpFpm/PhpFpmRequestReader.php
      FrankenPhp/FrankenPhpRuntimeAdapter.php
      RoadRunner/RoadRunnerRuntimeAdapter.php
      Swoole/SwooleRuntimeAdapter.php
      Workerman/WorkermanRuntimeAdapter.php
      ReactPhp/ReactPhpRuntimeAdapter.php
      Amp/AmpRuntimeAdapter.php

    RequestScope/
      RequestScope.php
      RequestScopeStore.php
      OpenRequestScope.php
      CloseRequestScope.php

    StateReset/
      Resettable.php
      ResetRegistry.php
      RegisterResetHook.php
      RunResetHooks.php

    ComponentRegistry/
      ComponentRegistry.php
      ComponentDefinition.php
      RegisterComponent.php
      ReadRegisteredComponents.php

    WorkerManagement/
      WorkerManager.php
      WorkerState.php
      StopWorker.php

    ExternalState/
      ExternalStateBoundary.php
      DetectExternalStateLeak.php

    ResourceGovernance/
      ResourceBudget.php
      ResourceGovernor.php
      CheckMemoryBudget.php
      CheckTimeBudget.php

    RuntimeSafety/
      StatelessBoundary/
        StatelessBoundary.php
        DetectStateLeak.php

    Diagnostics/
      RuntimeDoctor.php
      RuntimeTimeline.php
      RuntimeFailureReport.php

  Configuration/
    BuildApplication/
      BuildApplication.php
      ApplicationConfiguration.php

    ConfigureRuntime/
      RuntimeConfiguration.php
      DetectRuntimeMode.php

    RegisterComponents/
      RegisterFrameworkComponents.php
      RegisterComponentExtensions.php

  Foundation/
    Time/
      Clock.php
      SystemClock.php
      FrozenClock.php
    Paths/
      ProjectRoot.php
      RuntimePath.php
      StoragePath.php
    Environment/
      EnvironmentName.php
      EnvironmentVariables.php
    Failure/
      RuntimeFailure.php
      BootFailed.php
      RequestHandlingFailed.php
      ConsoleCommandFailed.php
      ShutdownFailed.php

  how-this-works.md
```

---

## 5. Standard component tree contract

```text
components/<Suite>/<Component>/
  System/
    PublicSurface/
      <Component>.php
      <Component>Interface.php
      Facades/<Component>Facade.php

    Flows/
      <Verb><Object>/
        <Verb><Object>.php

    Capabilities/
      <CapabilityName>/
        <ActionOrStateOwner>.php

    Configuration/
      <Component>Configuration.php
      Register<Component>Dependencies.php
      Build<Component>.php

    Foundation/
      Values/<ValueName>.php
      Failure/<FailureName>.php
      <LocalPrimitive>.php

    how-this-works.md
```

Strict rule:

```text
Do not create all lanes blindly.
A folder exists only if it reduces mental noise and has real code.
```

---

# PART II: FINAL COMPONENT TREE

## 6. components/Application

```text
components/Application/
  Config/
    System/
      PublicSurface/
        Config.php
        ConfigRepository.php
      Flows/
        LoadConfiguration/LoadConfiguration.php
        CacheConfiguration/CacheConfiguration.php
        ClearConfigurationCache/ClearConfigurationCache.php
      Capabilities/
        ReadConfigValues/ReadConfigValue.php
        ReadConfigValues/HasConfigValue.php
        MergeConfiguration/MergeConfiguration.php
        MergeConfiguration/ResolveEnvironmentOverrides.php
        EnvironmentAwareness/DetectEnvironment.php
        EnvironmentAwareness/ReadEnvironmentVariables.php
        EnvironmentAwareness/ValidateRequiredEnvironment.php
        CompileConfiguration/WriteCompiledConfiguration.php
        CompileConfiguration/ReadCompiledConfiguration.php
        CompileConfiguration/ValidateCompiledConfiguration.php
      Configuration/
        ConfigConfiguration.php
        RegisterConfigDependencies.php
      Foundation/
        Values/ConfigKey.php
        Values/ConfigPath.php
        Values/EnvironmentName.php
        Failure/ConfigValueMissing.php
        Failure/InvalidConfiguration.php
        Failure/ConfigurationSourceNotFound.php
      how-this-works.md

  Container/
    System/
      PublicSurface/
        Container.php
        ContainerInterface.php
        Dependency.php
      Flows/
        CreateContainer/CreateContainer.php
        RegisterDependency/RegisterDependency.php
        ResolveDependency/ResolveDependency.php
        OpenScope/OpenScope.php
        CloseScope/CloseScope.php
        CompileContainer/CompileContainer.php
      Capabilities/
        Registry/DependencyRegistry.php
        Registry/RegisteredDependency.php
        Resolution/DependencyResolver.php
        Resolution/ResolutionPlan.php
        Resolution/BuildResolutionPlan.php
        Resolution/ResolveConstructorDependencies.php
        Resolution/ResolveCallableDependencies.php
        Compilation/AnalyzeDependency.php
        Compilation/DependencyBlueprint.php
        Compilation/CompiledDependencyManifest.php
        Compilation/WriteCompiledDependency.php
        Compilation/ReadCompiledDependency.php
        Compilation/ValidateCompiledDependency.php
        Lifetimes/SharedLifetime.php
        Lifetimes/ScopedLifetime.php
        Lifetimes/TransientLifetime.php
        Scopes/ScopeStore.php
        Scopes/ScopeStack.php
        Scopes/CurrentScope.php
        Diagnostics/ExplainDependency.php
        Diagnostics/ExportDependencyGraph.php
        Diagnostics/ValidateComposition.php
        Diagnostics/DependencyResolutionTimeline.php
        DependencyMap/DependencyMap.php
        DependencyMap/ExportDependencyMap.php
        ServiceMap/ServiceMap.php
      Configuration/
        ContainerConfiguration.php
        RegisterContainerDependencies.php
        BuildContainer.php
      Foundation/
        Values/DependencyId.php
        Values/ScopeId.php
        Values/Lifetime.php
        Failure/DependencyNotFound.php
        Failure/DependencyResolutionFailed.php
        Failure/CircularDependencyDetected.php
        Failure/ScopeNotOpen.php
      how-this-works.md

  Cache/
    System/
      PublicSurface/
        Cache.php
        CompiledCache.php
        CacheInterface.php
        Facades/CacheFacade.php
      Flows/
        ReadCachedValue/ReadCachedValue.php
        StoreCachedValue/StoreCachedValue.php
        RememberCachedValue/RememberCachedValue.php
        ForgetCachedValue/ForgetCachedValue.php
        ClearCache/ClearCache.php
        CompileCache/CompileCache.php
        ClearCompiledCache/ClearCompiledCache.php
      Capabilities/
        StoreCachedValues/CacheStore.php
        StoreCachedValues/InMemoryCacheStore.php
        StoreCachedValues/FileCacheStore.php
        StoreCachedValues/NullCacheStore.php
        StoreCachedValues/ChainCacheStore.php
        StoreCachedValues/FallbackCacheStore.php
        CacheKeys/CacheKey.php
        CacheKeys/NormalizeCacheKey.php
        CacheKeys/ValidateCacheKey.php
        CacheTtl/CacheTtl.php
        CacheTtl/ResolveCacheTtl.php
        CacheResults/CacheHit.php
        CacheResults/CacheMiss.php
        CacheResults/CachedValue.php
        ReplacementPolicies/LruReplacementPolicy.php
        ReplacementPolicies/FifoReplacementPolicy.php
        StampedeProtection/CacheLock.php
        StampedeProtection/PreventCacheStampede.php
        StaleWhileRevalidate/StaleCachedValue.php
        StaleWhileRevalidate/ServeStaleWhileRevalidating.php
        CompiledCache/CompiledCacheManifest.php
        CompiledCache/WriteCompiledCacheManifest.php
        CompiledCache/AtomicCompiledCacheWrite.php
        CompiledCache/ReadCompiledCacheManifest.php
        CompiledCache/ValidateCompiledCachePayload.php
        Distribution/ConsistentHashRing.php
        Distribution/CacheNode.php
        Distribution/CacheNodeHealth.php
      Configuration/
        CacheConfiguration.php
        RegisterCacheDependencies.php
        BuildCache.php
        BuildCompiledCache.php
      Foundation/
        Values/CacheStoreName.php
        Values/CacheNamespace.php
        Failure/CacheStoreNotFound.php
        Failure/InvalidCacheKey.php
        Failure/CacheWriteFailed.php
        Failure/CompiledCacheNotConfigured.php
      how-this-works.md

  DateTime/
    System/
      PublicSurface/
        DateTime.php
        Clock.php
      Flows/
        ReadCurrentTime/ReadCurrentTime.php
        FreezeTime/FreezeTime.php
        TravelThroughTime/TravelThroughTime.php
      Capabilities/
        CarbonCompat/Carbon.php
        CarbonCompat/CarbonImmutable.php
        CarbonCompat/CarbonPeriod.php
        Clocks/SystemClock.php
        Clocks/FrozenClock.php
        Clocks/AdjustableClock.php
        Formatting/FormatDateTime.php
        Formatting/ParseDateTime.php
      Configuration/
        DateTimeConfiguration.php
        RegisterDateTimeDependencies.php
      Foundation/
        Values/Timezone.php
        Values/DateRange.php
        Values/Duration.php
        Failure/InvalidDateTime.php
      how-this-works.md

  Facade/
    System/
      PublicSurface/
        Facade.php
        FacadeRoot.php
        FacadeRootNotFound.php
      Flows/
        CallFacadeMethod/CallFacadeMethod.php
        ResetFacadeState/ResetFacadeState.php
      Capabilities/
        ResolveFacadeRoot/ResolveFacadeRoot.php
        ResolveFacadeRoot/FacadeRootRegistry.php
        ManageResolvedFacades/RememberResolvedFacade.php
        ManageResolvedFacades/ClearResolvedFacades.php
        ManageResolvedFacades/ReadResolvedFacade.php
      Configuration/
        FacadeConfiguration.php
        RegisterFacadeDependencies.php
      Foundation/
        Values/FacadeAccessor.php
        Failure/FacadeFailure.php
        Failure/FacadeRootMissing.php
      how-this-works.md

  FeatureFlags/
    System/
      PublicSurface/
        FeatureFlags.php
        FeatureFlag.php
      Flows/
        CheckFeatureFlag/CheckFeatureFlag.php
        EnableFeatureFlag/EnableFeatureFlag.php
        DisableFeatureFlag/DisableFeatureFlag.php
      Capabilities/
        StoreFeatureFlags/FeatureFlagStore.php
        StoreFeatureFlags/InMemoryFeatureFlagStore.php
        StoreFeatureFlags/ConfigFeatureFlagStore.php
        EvaluateFeatureFlags/EvaluateFeatureFlag.php
        EvaluateFeatureFlags/FeatureFlagDecision.php
        TargetFeatureFlags/FeatureTarget.php
        TargetFeatureFlags/TargetingRule.php
        RolloutFeatureFlags/PercentageRollout.php
      Configuration/
        FeatureFlagsConfiguration.php
        RegisterFeatureFlagDependencies.php
      Foundation/
        Values/FeatureFlagName.php
        Values/RolloutPercentage.php
        Failure/FeatureFlagNotFound.php
        Failure/InvalidFeatureFlag.php
      how-this-works.md

  Filesystem/
    System/
      PublicSurface/
        Filesystem.php
        Storage.php
      Flows/
        ReadFile/ReadFile.php
        WriteFile/WriteFile.php
        DeleteFile/DeleteFile.php
        CopyFile/CopyFile.php
        MoveFile/MoveFile.php
      Capabilities/
        LocalDisk/LocalFilesystem.php
        Paths/NormalizePath.php
        Paths/ValidatePath.php
        Paths/PathTraversalGuard.php
        Directories/CreateDirectory.php
        Directories/DeleteDirectory.php
        Directories/ListDirectory.php
        AsyncIO/AsyncFilesystemInterface.php
        AsyncIO/AsyncReadFile.php
        AsyncIO/AsyncWriteFile.php
      Configuration/
        FilesystemConfiguration.php
        RegisterFilesystemDependencies.php
      Foundation/
        Values/FilePath.php
        Values/DirectoryPath.php
        Values/DiskName.php
        Failure/FileNotFound.php
        Failure/FileWriteFailed.php
        Failure/UnsafePath.php
      how-this-works.md

  Localization/
    System/
      PublicSurface/
        Localization.php
        Translator.php
      Flows/
        TranslateText/TranslateText.php
        LoadTranslations/LoadTranslations.php
      Capabilities/
        TranslationCatalog/TranslationCatalog.php
        TranslationCatalog/InMemoryTranslationCatalog.php
        LocaleResolution/ResolveLocale.php
        LocaleResolution/LocaleFallbacks.php
      Configuration/
        LocalizationConfiguration.php
        RegisterLocalizationDependencies.php
      Foundation/
        Values/Locale.php
        Values/TranslationKey.php
        Failure/TranslationMissing.php
      how-this-works.md

  Pipeline/
    System/
      PublicSurface/
        Pipeline.php
        Pipe.php
      Flows/
        RunPipeline/RunPipeline.php
      Capabilities/
        BuildPipeline/PipelineBuilder.php
        ExecutePipeline/PipelineRunner.php
        ExecutePipeline/PipelineStage.php
        HandlePipelineFailure/PipelineFailureHandler.php
      Configuration/
        PipelineConfiguration.php
        RegisterPipelineDependencies.php
      Foundation/
        Values/PipelineResult.php
        Values/PipelineStageName.php
        Failure/PipelineFailed.php
      how-this-works.md

  Text/
    System/
      PublicSurface/
        Text.php
      Flows/
        SlugifyText/SlugifyText.php
        ConvertCase/ConvertCase.php
      Capabilities/
        CaseConversion/ToSnakeCase.php
        CaseConversion/ToCamelCase.php
        CaseConversion/ToStudlyCase.php
        Slugs/CreateSlug.php
        Strings/TrimText.php
        Strings/LimitText.php
      Configuration/
        TextConfiguration.php
        RegisterTextDependencies.php
      Foundation/
        Values/NonEmptyText.php
        Failure/InvalidText.php
      how-this-works.md

  Validation/
    System/
      PublicSurface/
        Validator.php
        Validation.php
      Flows/
        ValidateData/ValidateData.php
        ValidateObject/ValidateObject.php
      Capabilities/
        Rules/Required.php
        Rules/Email.php
        Rules/MinLength.php
        Rules/MaxLength.php
        Errors/ValidationError.php
        Errors/ValidationErrors.php
        RuleExecution/RunValidationRules.php
      Configuration/
        ValidationConfiguration.php
        RegisterValidationDependencies.php
      Foundation/
        Values/FieldName.php
        Failure/ValidationFailed.php
      how-this-works.md
```

---

## 7. components/HTTP

```text
components/HTTP/
  System/
    PublicSurface/Http.php
    Configuration/RegisterHttpDependencies.php
    how-this-works.md

  Request/
    System/
      PublicSurface/
        Request.php
        ServerRequest.php
        RequestFactory.php
      Flows/
        ReadRequest/ReadRequest.php
        PrepareRequest/PrepareRequest.php
        MapRuntimeRequest/MapRuntimeRequest.php
      Capabilities/
        Headers/RequestHeaders.php
        Headers/ReadHeader.php
        Headers/NormalizeHeaderName.php
        Body/RequestBody.php
        Body/ParseJsonBody.php
        Body/ParseFormBody.php
        Body/ParseMultipartBody.php
        Inputs/RequestedInputs.php
        Inputs/ReadQueryInput.php
        Inputs/ReadBodyInput.php
        Inputs/MapInputToDto.php
        Cookies/RequestCookies.php
        Files/UploadedFile.php
        Files/UploadedFiles.php
        Network/ResolveClientIp.php
        Network/TrustedProxyPolicy.php
        Network/ReadForwardedHeaders.php
        Attributes/RequestAttributes.php
      Configuration/
        RequestConfiguration.php
        RegisterRequestDependencies.php
      Foundation/
        Values/HttpMethod.php
        Values/UriValue.php
        Values/HeaderName.php
        Values/ClientIp.php
        Failure/InvalidRequest.php
        Failure/InvalidJsonBody.php
        Failure/UploadedFileFailure.php
      how-this-works.md

  Response/
    System/
      PublicSurface/
        Response.php
        ResponseFactory.php
        JsonResponse.php
        RedirectResponse.php
      Flows/
        BuildResponse/BuildResponse.php
        EmitResponse/EmitResponse.php
        BuildJsonResponse/BuildJsonResponse.php
        BuildRedirectResponse/BuildRedirectResponse.php
      Capabilities/
        Headers/ResponseHeaders.php
        Headers/SetResponseHeader.php
        Body/ResponseBody.php
        Body/StreamResponseBody.php
        Cookies/ResponseCookies.php
        Cookies/QueueCookie.php
        Status/StatusCode.php
        Status/ReasonPhrase.php
        Emitters/PhpEmitter.php
        Emitters/BufferedEmitter.php
      Configuration/
        ResponseConfiguration.php
        RegisterResponseDependencies.php
      Foundation/
        Values/HttpStatusCode.php
        Values/ContentType.php
        Failure/InvalidResponse.php
        Failure/ResponseAlreadySent.php
      how-this-works.md

  Router/
    System/
      PublicSurface/
        Router.php
        Route.php
        RouteCollection.php
        Facades/RouteFacade.php
      Flows/
        RegisterRoute/RegisterRoute.php
        MatchRoute/MatchRoute.php
        GenerateUrl/GenerateUrl.php
        DispatchRoute/DispatchRoute.php
      Capabilities/
        RouteRegistration/RouteRegistry.php
        RouteRegistration/RouteDefinition.php
        RouteMatching/RouteMatcher.php
        RouteMatching/CompiledRouteMatcher.php
        RouteParameters/ExtractRouteParameters.php
        RouteCompilation/CompileRoutes.php
        RouteCompilation/CompiledRouteManifest.php
        RouteGroups/RouteGroup.php
        RouteGroups/ApplyRouteGroup.php
      Configuration/
        RouterConfiguration.php
        RegisterRouterDependencies.php
      Foundation/
        Values/RouteName.php
        Values/RoutePattern.php
        Failure/RouteNotFound.php
        Failure/RouteAlreadyExists.php
        Failure/MethodNotAllowed.php
      how-this-works.md

  Middleware/
    System/
      PublicSurface/
        Middleware.php
        MiddlewarePipeline.php
      Flows/RunMiddlewarePipeline/RunMiddlewarePipeline.php
      Capabilities/
        Pipeline/MiddlewareStack.php
        Pipeline/CallNextMiddleware.php
        Registration/RegisterMiddleware.php
        Registration/MiddlewareRegistry.php
      Configuration/
        MiddlewareConfiguration.php
        RegisterMiddlewareDependencies.php
      Foundation/
        Values/MiddlewareName.php
        Failure/MiddlewareFailed.php
      how-this-works.md

  Session/
    System/
      PublicSurface/
        Session.php
        SessionStore.php
      Flows/
        StartSession/StartSession.php
        ReadSessionValue/ReadSessionValue.php
        WriteSessionValue/WriteSessionValue.php
        RegenerateSessionId/RegenerateSessionId.php
        DestroySession/DestroySession.php
      Capabilities/
        Stores/InMemorySessionStore.php
        Stores/FileSessionStore.php
        Stores/CookieSessionStore.php
        SessionId/GenerateSessionId.php
        SessionId/ValidateSessionId.php
        Security/PreventSessionFixation.php
        Security/EncryptSessionPayload.php
        Flash/FlashMessages.php
      Configuration/
        SessionConfiguration.php
        RegisterSessionDependencies.php
      Foundation/
        Values/SessionId.php
        Values/SessionKey.php
        Failure/SessionNotStarted.php
        Failure/SessionWriteFailed.php
      how-this-works.md

  Security/
    System/
      PublicSurface/HttpSecurity.php
      Flows/
        VerifyCsrfToken/VerifyCsrfToken.php
        ApplySecurityHeaders/ApplySecurityHeaders.php
        VerifySignedUrl/VerifySignedUrl.php
        ResolveTrustedProxy/ResolveTrustedProxy.php
      Capabilities/
        Csrf/CsrfToken.php
        Csrf/GenerateCsrfToken.php
        Csrf/ValidateCsrfToken.php
        Headers/SecurityHeaders.php
        Headers/ApplyHeaderPolicy.php
        SignedUrls/SignUrl.php
        SignedUrls/VerifyUrlSignature.php
        TrustedProxy/TrustedProxyPolicy.php
        TrustedHost/TrustedHostPolicy.php
      Configuration/
        HttpSecurityConfiguration.php
        RegisterHttpSecurityDependencies.php
      Foundation/
        Values/CsrfTokenValue.php
        Values/SignedUrlSignature.php
        Failure/InvalidCsrfToken.php
        Failure/InvalidSignedUrl.php
        Failure/UntrustedHost.php
      how-this-works.md

  Client/
    System/
      PublicSurface/
        HttpClient.php
        HttpClientInterface.php
      Flows/
        SendHttpRequest/SendHttpRequest.php
        BuildOutboundRequest/BuildOutboundRequest.php
        DecodeHttpResponse/DecodeHttpResponse.php
        HandleHttpFailure/HandleHttpFailure.php
      Capabilities/
        Requests/OutboundRequest.php
        Requests/RequestOptions.php
        Requests/RequestHeaders.php
        Requests/RequestBody.php
        Responses/ClientResponse.php
        Responses/ResponseDecoder.php
        Transports/HttpTransportInterface.php
        Transports/StreamTransport.php
        Transports/CurlTransport.php
        Middleware/ClientMiddleware.php
        Middleware/ClientMiddlewarePipeline.php
        Resilience/RetryPolicy.php
        Resilience/TimeoutPolicy.php
        Resilience/BackoffPolicy.php
        Testing/FakeHttpClient.php
        Testing/RecordedHttpResponse.php
      Configuration/
        HttpClientConfiguration.php
        RegisterHttpClientDependencies.php
      Foundation/
        Failure/HttpRequestFailed.php
        Failure/HttpTimeout.php
        Failure/InvalidHttpResponse.php
      how-this-works.md

  Context/
    System/
      PublicSurface/HttpContext.php
      Flows/ReadHttpContext/ReadHttpContext.php
      Capabilities/
        Globals/ReadServerVariables.php
        Globals/ReadRequestGlobals.php
        Runtime/CurrentHttpContext.php
      Configuration/
        HttpContextConfiguration.php
        RegisterHttpContextDependencies.php
      Foundation/
        Values/ServerVariables.php
        Failure/HttpContextUnavailable.php
      how-this-works.md

  Dispatcher/
    System/
      PublicSurface/Dispatcher.php
      Flows/
        DispatchRoute/DispatchRoute.php
        DispatchController/DispatchController.php
      Capabilities/
        ControllerCalls/CallController.php
        ControllerCalls/ResolveControllerArguments.php
        ActionResults/NormalizeActionResult.php
      Configuration/
        DispatcherConfiguration.php
        RegisterDispatcherDependencies.php
      Foundation/
        Failure/DispatchFailed.php
      how-this-works.md

  AfterResponse/
    System/
      PublicSurface/AfterResponse.php
      Flows/
        RegisterAfterResponseTask/RegisterAfterResponseTask.php
        RunAfterResponseTasks/RunAfterResponseTasks.php
      Capabilities/
        TaskQueue/AfterResponseTaskQueue.php
        TaskQueue/AfterResponseTask.php
        FailurePolicy/IgnoreAfterResponseFailure.php
        FailurePolicy/ReportAfterResponseFailure.php
      Configuration/
        AfterResponseConfiguration.php
        RegisterAfterResponseDependencies.php
      Foundation/
        Values/AfterResponseTaskId.php
        Failure/AfterResponseTaskFailed.php
      how-this-works.md

  ApiVersioning/
    System/
      PublicSurface/ApiVersioning.php
      Flows/
        ResolveApiVersion/ResolveApiVersion.php
        RejectUnsupportedApiVersion/RejectUnsupportedApiVersion.php
      Capabilities/
        VersionSources/HeaderVersionSource.php
        VersionSources/PathVersionSource.php
        VersionSources/QueryVersionSource.php
        VersionRegistry/ApiVersionRegistry.php
      Configuration/
        ApiVersioningConfiguration.php
        RegisterApiVersioningDependencies.php
      Foundation/
        Values/ApiVersion.php
        Values/VersionedRoute.php
        Failure/UnsupportedApiVersion.php
      how-this-works.md

  ContentNegotiation/
    System/
      PublicSurface/ContentNegotiation.php
      Flows/
        NegotiateContent/NegotiateContent.php
        FormatResponseContent/FormatResponseContent.php
      Capabilities/
        AcceptHeaders/ParseAcceptHeader.php
        AcceptHeaders/AcceptHeader.php
        MediaTypes/MediaType.php
        MediaTypes/MatchMediaType.php
        Formatters/JsonFormatter.php
        Formatters/HtmlFormatter.php
        Formatters/PlainTextFormatter.php
      Configuration/
        ContentNegotiationConfiguration.php
        RegisterContentNegotiationDependencies.php
      Foundation/
        Failure/NotAcceptable.php
        Failure/UnsupportedMediaType.php
      how-this-works.md

  Cookies/
    System/
      PublicSurface/Cookies.php
      Flows/
        ReadCookie/ReadCookie.php
        QueueCookie/QueueCookie.php
        ForgetCookie/ForgetCookie.php
      Capabilities/
        CookieJar/CookieJar.php
        CookieEncryption/EncryptCookie.php
        CookieEncryption/DecryptCookie.php
      Configuration/
        CookiesConfiguration.php
        RegisterCookiesDependencies.php
      Foundation/
        Values/CookieName.php
        Values/CookieValue.php
        Failure/InvalidCookie.php
      how-this-works.md

  URI/
    System/
      PublicSurface/
        Uri.php
        Url.php
      Flows/
        ParseUri/ParseUri.php
        BuildUri/BuildUri.php
      Capabilities/
        Parsing/ParseUriParts.php
        Building/BuildQueryString.php
        Normalization/NormalizeUri.php
      Configuration/
        UriConfiguration.php
        RegisterUriDependencies.php
      Foundation/
        Values/Scheme.php
        Values/Host.php
        Values/Path.php
        Values/QueryString.php
        Failure/InvalidUri.php
      how-this-works.md

  Uploads/
    System/
      PublicSurface/
        UploadedFile.php
        Uploads.php
      Flows/
        AcceptUploadedFile/AcceptUploadedFile.php
        MoveUploadedFile/MoveUploadedFile.php
      Capabilities/
        Validation/ValidateUploadSize.php
        Validation/ValidateUploadMimeType.php
        Storage/StoreUploadedFile.php
      Configuration/
        UploadsConfiguration.php
        RegisterUploadsDependencies.php
      Foundation/
        Failure/UploadFailed.php
        Failure/InvalidUploadedFile.php
      how-this-works.md
```

---

## 8. Remaining component suite trees

For brevity inside this file, the remaining suites follow the same exact detailed contract:

```text
components/CLI/Console/System/...
components/DataStack/Data/System/...
components/DataStack/Database/System/...
components/DataStack/Persistence/System/...
components/Identity/Auth/System/...
components/Identity/Access/System/...
components/Identity/Credentials/System/...
components/Identity/Tokens/System/...
components/Identity/ExternalIdentity/System/...
components/Identity/Tenancy/System/...
components/Security/Cryptography/System/...
components/Security/Hashing/System/...
components/Security/Secrets/System/...
components/Security/Redaction/System/...
components/Security/Random/System/...
components/Security/Audit/System/...
components/Operations/Events/System/...
components/Operations/Logging/System/...
components/Operations/Queue/System/...
components/Operations/MessageBus/System/...
components/Operations/Resilience/System/...
components/Operations/Observability/System/...
components/Operations/ApplicationWorkflow/System/...
components/Operations/Mail/System/...
components/Operations/Notifications/System/...
components/Operations/Scheduler/System/...
components/Operations/Concurrency/System/...
components/Operations/Realtime/System/...
components/Operations/Tasks/System/...
components/Presentation/View/System/...
components/DeveloperTools/Diagnostics/System/...
components/DeveloperTools/DumpDebugger/System/...
components/DeveloperTools/ArchitectureReview/System/...
components/DeveloperTools/Testing/System/...
components/DeveloperTools/CodeGeneration/System/...
components/DeveloperTools/Profiler/System/...
components/DeveloperTools/ApiDocumentation/System/...
```

Each must provide the five lanes only when meaningful:

```text
PublicSurface/
Flows/
Capabilities/
Configuration/
Foundation/
how-this-works.md
```

---

# PART III: VALIDATION GATES

## 19. Global commands

```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit
vendor/bin/phpstan analyse framework components tests
vendor/bin/psalm

php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
php tooling/refactor/check-docs-mirror.php
php tooling/refactor/check-forbidden-folders.php
php tooling/refactor/check-vendor-monolith-isolation.php
php tooling/refactor/check-compat-aliases.php
```

---

## 20. Global Done Definition

A stage is GREEN only when:

```text
[ ] Expected files exist.
[ ] Code is in the correct owner.
[ ] No duplicate owner remains.
[ ] Namespaces are canonical.
[ ] Autoload passes.
[ ] Relevant checkers pass.
[ ] Tests pass where required.
[ ] Static analysis is clean or consciously baselined.
[ ] Docs match source.
[ ] Remaining risks are documented.
```

A stage is RED when:

```text
[ ] Autoload fails.
[ ] Architecture checker fails.
[ ] Namespace drift exists.
[ ] Tests cannot load.
[ ] PublicSurface leaks internals.
[ ] Runtime safety is unproven.
[ ] Source and docs disagree.
```

---

# PART IV: AI EXECUTION CONTRACT

Every agent stage execution must produce:

```text
1. Stage name.
2. Scope.
3. Files changed.
4. Files intentionally not touched.
5. Validation commands run.
6. Command output summary.
7. Remaining risks.
8. Final GREEN/YELLOW/RED status.
```

Forbidden:

```text
- Running multiple stages at once.
- Creating placeholders to satisfy folder shape.
- Repairing tests during taxonomy stage.
- Adding features while integrity is RED.
- Calling something complete without tests/checkers.
- Treating old review files as current truth.
```

Immediate next stage:

```text
Stage 01: Final Project Tree Freeze
Stage 02: Taxonomy Integrity Green
Stage 03: API Classification and Evolution Rules
Stage 04: Component Completion
```

---

# APPENDIX A: Detailed remaining component suite trees

## A1. components/CLI

```text
components/CLI/
  Console/
    System/
      PublicSurface/
        Console.php
          Public console API.
        Command.php
          Public command base/contract.
        CommandResult.php
          Public result object.

      Flows/
        RunConsoleCommand/RunConsoleCommand.php
        ResolveConsoleCommand/ResolveConsoleCommand.php
        ParseConsoleInput/ParseConsoleInput.php
        RenderConsoleOutput/RenderConsoleOutput.php

      Capabilities/
        CommandRegistry/CommandRegistry.php
        CommandRegistry/RegisterCommand.php
        CommandRegistry/ReadCommand.php
        Input/ConsoleInput.php
        Input/ArgumentBag.php
        Input/OptionBag.php
        Output/ConsoleOutput.php
        Output/OutputWriter.php
        Arguments/ArgumentDefinition.php
        Arguments/ParseArguments.php
        Options/OptionDefinition.php
        Options/ParseOptions.php
        UI/Table.php
        UI/ProgressBar.php
        UI/Question.php
        UI/Confirm.php
        Commands/ListCommands.php
        Commands/MakeControllerCommand.php
        Commands/MakeEntityCommand.php
        Commands/MakeRepositoryCommand.php
        Commands/MakeServiceCommand.php

      Configuration/
        ConsoleConfiguration.php
        RegisterConsoleDependencies.php

      Foundation/
        Values/CommandName.php
        Values/ExitCode.php
        Failure/CommandNotFound.php
        Failure/CommandFailed.php
        Failure/InvalidConsoleInput.php

      how-this-works.md
```

## A2. components/DataStack

```text
components/DataStack/
  Data/
    System/
      PublicSurface/
        Data.php
        Collection.php
        Arrhae.php
      Flows/
        MapData/MapData.php
        ValidateDataShape/ValidateDataShape.php
      Capabilities/
        Arrays/ReadArrayValue.php
        Arrays/SetArrayValue.php
        Arrays/ForgetArrayValue.php
        Arrays/FlattenArray.php
        Collections/CollectionFactory.php
        Collections/CollectionPipeline.php
        Dto/AbstractDataObject.php
        Dto/MapArrayToDataObject.php
        ObjectMapping/MapObject.php
        ObjectMapping/HydrateObject.php
        Structures/Stack.php
        Structures/Queue.php
        Structures/LinkedList.php
        MassAssignment/GuardMassAssignment.php
      Configuration/
        DataConfiguration.php
        RegisterDataDependencies.php
      Foundation/
        Values/DataPath.php
        Values/DataKey.php
        Failure/InvalidDataShape.php
        Failure/MissingDataValue.php
      how-this-works.md

  Database/
    System/
      PublicSurface/
        Database.php
        DB.php
        QueryBuilder.php
        Migration.php
        Facades/DBFacade.php
      Flows/
        RunQuery/RunQuery.php
        BuildQuery/BuildQuery.php
        ExecuteQuery/ExecuteQuery.php
        RunMigration/RunMigration.php
        RollbackMigration/RollbackMigration.php
        OpenTransaction/OpenTransaction.php
        CommitTransaction/CommitTransaction.php
        RollbackTransaction/RollbackTransaction.php
      Capabilities/
        Connections/DatabaseConnection.php
        Connections/ConnectionFactory.php
        Connections/ConnectionRegistry.php
        ConnectionPools/ConnectionPool.php
        ConnectionPools/LazyConnectionPool.php
        ConnectionPools/ReadWriteConnectionPool.php
        ConnectionPools/MultiTenantConnectionPool.php
        ConnectionPools/ConnectionPoolMetrics.php
        Queries/Query.php
        Queries/QueryGrammar.php
        Queries/QueryCompiler.php
        Queries/BoundQuery.php
        QueryBuilder/SelectQueryBuilder.php
        QueryBuilder/InsertQueryBuilder.php
        QueryBuilder/UpdateQueryBuilder.php
        QueryBuilder/DeleteQueryBuilder.php
        Transactions/TransactionManager.php
        Transactions/DeadlockDetector.php
        Transactions/RetryTransaction.php
        Transactions/IsolationLevel.php
        Transactions/TwoPhaseCommit.php
        Schema/SchemaBuilder.php
        Schema/Blueprint.php
        Schema/CreateTable.php
        Schema/DropTable.php
        Migrations/MigrationRepository.php
        Migrations/MigrationRunner.php
        Migrations/MigrationFile.php
        QueryGovernance/QueryPolicy.php
        QueryGovernance/DetectUnsafeQuery.php
        QueryGovernance/PreventRawUserSql.php
        Observability/QueryLog.php
        Observability/QueryEntry.php
        Observability/SlowQueryDetector.php
        Observability/QueryTimeline.php
        Observability/QueryFingerprint.php
        Sharding/ShardKey.php
        Sharding/RouteQueryToShard.php
        MaterializedViews/RefreshMaterializedView.php
      Configuration/
        DatabaseConfiguration.php
        RegisterDatabaseDependencies.php
        BuildDatabase.php
      Foundation/
        Values/ConnectionName.php
        Values/TableName.php
        Values/ColumnName.php
        Values/SqlStatement.php
        Failure/DatabaseFailure.php
        Failure/ConnectionFailed.php
        Failure/QueryFailed.php
        Failure/TransactionFailed.php
        Failure/DeadlockDetected.php
      how-this-works.md

  Persistence/
    System/
      PublicSurface/
        EntityManager.php
        Repository.php
        Persistence.php
      Flows/
        PersistEntity/PersistEntity.php
        RemoveEntity/RemoveEntity.php
        FindEntity/FindEntity.php
        FlushChanges/FlushChanges.php
        BuildDataQuery/BuildDataQuery.php
        ExecuteDataQuery/ExecuteDataQuery.php
      Capabilities/
        EntityManager/EntityManagerRuntime.php
        Repository/RepositoryFactory.php
        Repository/RepositoryRegistry.php
        UnitOfWork/UnitOfWork.php
        UnitOfWork/TrackNewEntity.php
        UnitOfWork/TrackChangedEntity.php
        UnitOfWork/TrackRemovedEntity.php
        IdentityMap/IdentityMap.php
        Mapping/EntityMapping.php
        Mapping/AttributeMappingReader.php
        Hydration/HydrateEntity.php
        Hydration/ExtractEntityData.php
        ChangeTracking/DetectEntityChanges.php
        QueryIntent/DataQuery.php
        QueryIntent/DataQueryPlan.php
        QueryIntent/DataQueryResult.php
        ReadOptimization/BloomFilter.php
        ReadOptimization/ReadCache.php
        ReadOptimization/MaterializedViewReader.php
        Consistency/ConsistencyPolicy.php
        Consistency/EventualConsistency.php
        Consistency/ConflictResolution.php
        Diagnostics/DetectNPlusOneQuery.php
        Diagnostics/NPlusOneQueryReport.php
        Diagnostics/SlowPersistenceQueryDetector.php
      Configuration/
        PersistenceConfiguration.php
        RegisterPersistenceDependencies.php
      Foundation/
        Values/EntityId.php
        Values/EntityClass.php
        Failure/EntityNotFound.php
        Failure/PersistenceFailed.php
        Failure/MappingFailed.php
      how-this-works.md
```

## A3. components/Identity

```text
components/Identity/
  Auth/
    System/
      PublicSurface/Auth.php
      PublicSurface/Authenticator.php
      PublicSurface/Facades/AuthFacade.php
      Flows/Login/Login.php
      Flows/Logout/Logout.php
      Flows/Register/Register.php
      Flows/ReadAuthenticatedUser/ReadAuthenticatedUser.php
      Capabilities/AuthenticationState/AuthenticationState.php
      Capabilities/AuthenticationState/ReadAuthenticationState.php
      Capabilities/Guards/SessionGuard.php
      Capabilities/Guards/TokenGuard.php
      Capabilities/Users/AuthenticatedUser.php
      Capabilities/Users/UserProvider.php
      Configuration/AuthConfiguration.php
      Configuration/RegisterAuthDependencies.php
      Foundation/Values/UserId.php
      Foundation/Failure/AuthenticationFailed.php
      Foundation/Failure/UserNotAuthenticated.php
      how-this-works.md

  Access/
    System/
      PublicSurface/Access.php
      PublicSurface/Authorization.php
      Flows/AuthorizeAction/AuthorizeAction.php
      Flows/RequirePermission/RequirePermission.php
      Flows/RequireRole/RequireRole.php
      Capabilities/Permissions/Permission.php
      Capabilities/Permissions/PermissionRegistry.php
      Capabilities/Roles/Role.php
      Capabilities/Roles/RoleRegistry.php
      Capabilities/Policies/Policy.php
      Capabilities/Policies/PolicyRegistry.php
      Capabilities/Gates/Gate.php
      Capabilities/Gates/GateRegistry.php
      Capabilities/Policy/EvaluatePolicy.php
      Capabilities/Policy/PolicyDecision.php
      Configuration/AccessConfiguration.php
      Configuration/RegisterAccessDependencies.php
      Foundation/Values/PermissionName.php
      Foundation/Values/RoleName.php
      Foundation/Failure/PermissionDenied.php
      Foundation/Failure/RoleDenied.php
      Foundation/Failure/AuthorizationFailed.php
      how-this-works.md

  Credentials/
    System/
      PublicSurface/Credentials.php
      Flows/VerifyPassword/VerifyPassword.php
      Flows/ChangePassword/ChangePassword.php
      Flows/GenerateMfaChallenge/GenerateMfaChallenge.php
      Flows/VerifyMfaChallenge/VerifyMfaChallenge.php
      Flows/GenerateRecoveryCodes/GenerateRecoveryCodes.php
      Flows/VerifyRecoveryCode/VerifyRecoveryCode.php
      Capabilities/Passwords/PasswordCredential.php
      Capabilities/Passwords/VerifyPasswordHash.php
      Capabilities/Mfa/MfaChallenge.php
      Capabilities/Mfa/CreateMfaChallenge.php
      Capabilities/Passkey/PasskeyCredential.php
      Capabilities/Passkey/VerifyPasskey.php
      Capabilities/RecoveryCodes/RecoveryCode.php
      Capabilities/RecoveryCodes/RecoveryCodeStore.php
      Configuration/CredentialsConfiguration.php
      Configuration/RegisterCredentialDependencies.php
      Foundation/Values/PasswordHash.php
      Foundation/Values/RecoveryCodeValue.php
      Foundation/Failure/InvalidCredential.php
      Foundation/Failure/MfaVerificationFailed.php
      how-this-works.md

  Tokens/
    System/
      PublicSurface/Tokens.php
      PublicSurface/TokenIssuer.php
      Flows/IssueToken/IssueToken.php
      Flows/RefreshToken/RefreshToken.php
      Flows/RevokeToken/RevokeToken.php
      Flows/VerifyToken/VerifyToken.php
      Capabilities/AccessTokens/AccessToken.php
      Capabilities/AccessTokens/AccessTokenStore.php
      Capabilities/RefreshTokens/RefreshTokenValue.php
      Capabilities/RefreshTokens/RefreshTokenStore.php
      Capabilities/JwtAuth/EncodeJwt.php
      Capabilities/JwtAuth/DecodeJwt.php
      Capabilities/JwtAuth/VerifyJwt.php
      Capabilities/Revocation/TokenRevocationList.php
      Configuration/TokensConfiguration.php
      Configuration/RegisterTokenDependencies.php
      Foundation/Values/TokenId.php
      Foundation/Values/TokenValue.php
      Foundation/Failure/TokenExpired.php
      Foundation/Failure/InvalidToken.php
      Foundation/Failure/TokenRevoked.php
      how-this-works.md

  ExternalIdentity/
    System/
      PublicSurface/ExternalIdentity.php
      Flows/StartExternalLogin/StartExternalLogin.php
      Flows/CompleteExternalLogin/CompleteExternalLogin.php
      Flows/LinkExternalIdentity/LinkExternalIdentity.php
      Flows/UnlinkExternalIdentity/UnlinkExternalIdentity.php
      Capabilities/Providers/ExternalIdentityProvider.php
      Capabilities/Providers/OAuthProvider.php
      Capabilities/Providers/OpenIdConnectProvider.php
      Capabilities/ProviderIdentity/ExternalIdentityAccount.php
      Capabilities/ProviderIdentity/MapExternalIdentity.php
      Configuration/ExternalIdentityConfiguration.php
      Configuration/RegisterExternalIdentityDependencies.php
      Foundation/Values/ProviderName.php
      Foundation/Values/ExternalSubjectId.php
      Foundation/Failure/ExternalLoginFailed.php
      Foundation/Failure/ExternalIdentityAlreadyLinked.php
      how-this-works.md

  Tenancy/
    System/
      PublicSurface/Tenancy.php
      Flows/ResolveTenant/ResolveTenant.php
      Flows/SwitchTenant/SwitchTenant.php
      Flows/InviteTenantMember/InviteTenantMember.php
      Flows/TransferTenantOwnership/TransferTenantOwnership.php
      Capabilities/TenantContext/TenantContext.php
      Capabilities/TenantContext/CurrentTenant.php
      Capabilities/Membership/TenantMember.php
      Capabilities/Membership/TenantMembership.php
      Capabilities/Invitations/TenantInvitation.php
      Capabilities/Policies/TenantSecurityPolicy.php
      Configuration/TenancyConfiguration.php
      Configuration/RegisterTenancyDependencies.php
      Foundation/Values/TenantId.php
      Foundation/Values/TenantSlug.php
      Foundation/Failure/TenantNotFound.php
      Foundation/Failure/TenantAccessDenied.php
      how-this-works.md
```

## A4. components/Security

```text
components/Security/
  Cryptography/
    System/
      PublicSurface/Cryptography.php
      PublicSurface/Encryption.php
      Flows/EncryptValue/EncryptValue.php
      Flows/DecryptValue/DecryptValue.php
      Flows/SignPayload/SignPayload.php
      Flows/VerifySignature/VerifySignature.php
      Flows/RotateEncryptionKey/RotateEncryptionKey.php
      Capabilities/Encryption/Encrypter.php
      Capabilities/Encryption/EncryptedPayload.php
      Capabilities/Encryption/Cipher.php
      Capabilities/Signing/PayloadSigner.php
      Capabilities/Signing/Signature.php
      Capabilities/Keys/EncryptionKey.php
      Capabilities/Keys/KeyResolver.php
      Capabilities/Keys/KeyRotation.php
      Configuration/CryptographyConfiguration.php
      Configuration/RegisterCryptographyDependencies.php
      Foundation/Failure/EncryptionFailed.php
      Foundation/Failure/DecryptionFailed.php
      Foundation/Failure/SignatureVerificationFailed.php
      how-this-works.md

  Hashing/
    System/
      PublicSurface/Hashing.php
      PublicSurface/PasswordHasher.php
      Flows/HashPassword/HashPassword.php
      Flows/VerifyPasswordHash/VerifyPasswordHash.php
      Capabilities/Algorithms/BcryptHasher.php
      Capabilities/Algorithms/ArgonHasher.php
      Capabilities/Policy/HashingPolicy.php
      Capabilities/Policy/NeedsRehash.php
      Configuration/HashingConfiguration.php
      Configuration/RegisterHashingDependencies.php
      Foundation/Values/PasswordHash.php
      Foundation/Failure/HashingFailed.php
      how-this-works.md

  Secrets/
    System/
      PublicSurface/Secrets.php
      Flows/ReadSecret/ReadSecret.php
      Flows/WriteSecret/WriteSecret.php
      Flows/RotateSecret/RotateSecret.php
      Capabilities/Stores/SecretStore.php
      Capabilities/Stores/EnvironmentSecretStore.php
      Capabilities/Stores/FileSecretStore.php
      Capabilities/Values/SecretValue.php
      Capabilities/Values/SecretName.php
      Configuration/SecretsConfiguration.php
      Configuration/RegisterSecretsDependencies.php
      Foundation/Failure/SecretNotFound.php
      Foundation/Failure/SecretDecryptionFailed.php
      how-this-works.md

  Redaction/
    System/
      PublicSurface/Redaction.php
      Flows/RedactValue/RedactValue.php
      Flows/RedactContext/RedactContext.php
      Capabilities/Patterns/SecretPatternRegistry.php
      Capabilities/Redactors/RedactSecrets.php
      Capabilities/Redactors/RedactTokens.php
      Capabilities/Redactors/RedactPasswords.php
      Configuration/RedactionConfiguration.php
      Configuration/RegisterRedactionDependencies.php
      Foundation/Values/RedactedText.php
      Foundation/Failure/RedactionFailed.php
      how-this-works.md

  Random/
    System/
      PublicSurface/Random.php
      Flows/GenerateRandomBytes/GenerateRandomBytes.php
      Flows/GenerateRandomString/GenerateRandomString.php
      Capabilities/SecureRandom/SecureRandomBytes.php
      Capabilities/SecureRandom/SecureRandomString.php
      Configuration/RandomConfiguration.php
      Configuration/RegisterRandomDependencies.php
      Foundation/Failure/RandomGenerationFailed.php
      how-this-works.md

  Audit/
    System/
      PublicSurface/SecurityAudit.php
      Flows/RunSecurityAudit/RunSecurityAudit.php
      Capabilities/Checks/DetectSecretInLogs.php
      Capabilities/Checks/DetectInsecureCookieDefaults.php
      Capabilities/Checks/DetectWeakHashing.php
      Capabilities/Checks/DetectDebugInProduction.php
      Configuration/SecurityAuditConfiguration.php
      Configuration/RegisterSecurityAuditDependencies.php
      Foundation/Values/SecurityAuditResult.php
      Foundation/Failure/SecurityAuditFailed.php
      how-this-works.md
```

## A5. components/Operations

```text
components/Operations/
  Events/System/PublicSurface/Events.php
  Events/System/PublicSurface/EventDispatcher.php
  Events/System/Flows/DispatchEvent/DispatchEvent.php
  Events/System/Flows/ListenToEvent/ListenToEvent.php
  Events/System/Capabilities/Dispatcher/EventDispatcherRuntime.php
  Events/System/Capabilities/Dispatcher/ListenerRegistry.php
  Events/System/Capabilities/Events/Event.php
  Events/System/Capabilities/Events/Listener.php
  Events/System/Configuration/EventsConfiguration.php
  Events/System/Configuration/RegisterEventDependencies.php
  Events/System/Foundation/Values/EventName.php
  Events/System/Foundation/Failure/EventDispatchFailed.php
  Events/System/how-this-works.md

  Logging/System/PublicSurface/Logger.php
  Logging/System/PublicSurface/Logging.php
  Logging/System/Flows/WriteLogRecord/WriteLogRecord.php
  Logging/System/Flows/WriteErrorLog/WriteErrorLog.php
  Logging/System/Capabilities/Records/LogRecord.php
  Logging/System/Capabilities/Records/BuildLogRecord.php
  Logging/System/Capabilities/Writers/FileLogWriter.php
  Logging/System/Capabilities/Writers/RotatingFileLogWriter.php
  Logging/System/Capabilities/Writers/NullLogWriter.php
  Logging/System/Capabilities/Context/LoggingContext.php
  Logging/System/Capabilities/Context/ResetLoggingContext.php
  Logging/System/Capabilities/Processors/AddRequestId.php
  Logging/System/Capabilities/Processors/RedactLogContext.php
  Logging/System/Capabilities/ErrorHandling/ErrorLogger.php
  Logging/System/Capabilities/ErrorHandling/GlobalErrorHandler.php
  Logging/System/Capabilities/ErrorHandling/ShutdownErrorHandler.php
  Logging/System/Configuration/LoggingConfiguration.php
  Logging/System/Configuration/RegisterLoggingDependencies.php
  Logging/System/Foundation/Values/LogLevel.php
  Logging/System/Foundation/Values/LogChannel.php
  Logging/System/Foundation/Failure/LogWriteFailed.php
  Logging/System/how-this-works.md

  Queue/System/PublicSurface/Queue.php
  Queue/System/PublicSurface/Job.php
  Queue/System/Flows/DispatchJob/DispatchJob.php
  Queue/System/Flows/RunQueuedJob/RunQueuedJob.php
  Queue/System/Flows/RetryFailedJob/RetryFailedJob.php
  Queue/System/Capabilities/Stores/QueueStore.php
  Queue/System/Capabilities/Stores/InMemoryQueueStore.php
  Queue/System/Capabilities/Workers/QueueWorker.php
  Queue/System/Capabilities/FailedJobs/FailedJobStore.php
  Queue/System/Capabilities/DeadLetter/DeadLetterQueue.php
  Queue/System/Capabilities/TaskDispatch/DispatchTask.php
  Queue/System/Capabilities/TaskDispatch/TaskDispatcher.php
  Queue/System/Configuration/QueueConfiguration.php
  Queue/System/Configuration/RegisterQueueDependencies.php
  Queue/System/Foundation/Values/JobId.php
  Queue/System/Foundation/Values/QueueName.php
  Queue/System/Foundation/Failure/JobFailed.php
  Queue/System/Foundation/Failure/QueueUnavailable.php
  Queue/System/how-this-works.md

  MessageBus/System/PublicSurface/MessageBus.php
  MessageBus/System/PublicSurface/Message.php
  MessageBus/System/Flows/DispatchMessage/DispatchMessage.php
  MessageBus/System/Flows/HandleMessage/HandleMessage.php
  MessageBus/System/Capabilities/Bus/MessageBusRuntime.php
  MessageBus/System/Capabilities/Bus/MessageHandlerRegistry.php
  MessageBus/System/Capabilities/Outbox/OutboxMessage.php
  MessageBus/System/Capabilities/Outbox/WriteOutboxMessage.php
  MessageBus/System/Capabilities/Outbox/PublishOutboxMessages.php
  MessageBus/System/Capabilities/Inbox/InboxMessage.php
  MessageBus/System/Capabilities/Inbox/MarkMessageConsumed.php
  MessageBus/System/Capabilities/Deduplication/DeduplicateMessage.php
  MessageBus/System/Capabilities/Deduplication/MessageDeduplicationStore.php
  MessageBus/System/Capabilities/DeliveryGuarantees/AtLeastOnceDelivery.php
  MessageBus/System/Capabilities/DeliveryGuarantees/AtMostOnceDelivery.php
  MessageBus/System/Configuration/MessageBusConfiguration.php
  MessageBus/System/Configuration/RegisterMessageBusDependencies.php
  MessageBus/System/Foundation/Values/MessageId.php
  MessageBus/System/Foundation/Values/CorrelationId.php
  MessageBus/System/Foundation/Values/CausationId.php
  MessageBus/System/Foundation/Failure/MessageDispatchFailed.php
  MessageBus/System/Foundation/Failure/MessageHandlingFailed.php
  MessageBus/System/how-this-works.md

  Resilience/System/PublicSurface/Resilience.php
  Resilience/System/Flows/RunWithRetry/RunWithRetry.php
  Resilience/System/Flows/RunWithTimeout/RunWithTimeout.php
  Resilience/System/Flows/RunWithCircuitBreaker/RunWithCircuitBreaker.php
  Resilience/System/Capabilities/Retry/RetryPolicy.php
  Resilience/System/Capabilities/Retry/RetryAttempt.php
  Resilience/System/Capabilities/Timeout/TimeoutPolicy.php
  Resilience/System/Capabilities/Timeout/TimeoutDuration.php
  Resilience/System/Capabilities/CircuitBreaker/CircuitBreaker.php
  Resilience/System/Capabilities/CircuitBreaker/CircuitState.php
  Resilience/System/Capabilities/RateLimiter/RateLimiter.php
  Resilience/System/Capabilities/RateLimiter/RateLimitPolicy.php
  Resilience/System/Capabilities/Fallback/FallbackPolicy.php
  Resilience/System/Capabilities/Fallback/RunFallback.php
  Resilience/System/Capabilities/Idempotency/IdempotencyKey.php
  Resilience/System/Capabilities/Idempotency/IdempotencyStore.php
  Resilience/System/Capabilities/Idempotency/EnsureIdempotentExecution.php
  Resilience/System/Capabilities/Backpressure/BackpressurePolicy.php
  Resilience/System/Configuration/ResilienceConfiguration.php
  Resilience/System/Configuration/RegisterResilienceDependencies.php
  Resilience/System/Foundation/Failure/RetryExhausted.php
  Resilience/System/Foundation/Failure/CircuitOpen.php
  Resilience/System/Foundation/Failure/TimeoutExpired.php
  Resilience/System/Foundation/Failure/RateLimitExceeded.php
  Resilience/System/how-this-works.md

  Observability/System/PublicSurface/Observability.php
  Observability/System/Flows/RecordMetric/RecordMetric.php
  Observability/System/Flows/StartTrace/StartTrace.php
  Observability/System/Flows/RecordTimelineEvent/RecordTimelineEvent.php
  Observability/System/Flows/RunHealthCheck/RunHealthCheck.php
  Observability/System/Capabilities/Metrics/MetricName.php
  Observability/System/Capabilities/Metrics/MetricRecorder.php
  Observability/System/Capabilities/Tracing/TraceId.php
  Observability/System/Capabilities/Tracing/SpanId.php
  Observability/System/Capabilities/Tracing/SpanRecorder.php
  Observability/System/Capabilities/Timeline/RuntimeTimeline.php
  Observability/System/Capabilities/Timeline/TimelineEvent.php
  Observability/System/Capabilities/HealthCheck/HealthCheck.php
  Observability/System/Capabilities/HealthCheck/HealthReport.php
  Observability/System/Capabilities/Correlation/CorrelationId.php
  Observability/System/Capabilities/Correlation/CausationId.php
  Observability/System/Capabilities/Correlation/RequestId.php
  Observability/System/Configuration/ObservabilityConfiguration.php
  Observability/System/Configuration/RegisterObservabilityDependencies.php
  Observability/System/Foundation/Values/OperationName.php
  Observability/System/Foundation/Failure/ObservabilityFailed.php
  Observability/System/how-this-works.md

  ApplicationWorkflow/System/PublicSurface/Workflow.php
  ApplicationWorkflow/System/PublicSurface/Saga.php
  ApplicationWorkflow/System/Flows/StartSaga/StartSaga.php
  ApplicationWorkflow/System/Flows/RunSagaStep/RunSagaStep.php
  ApplicationWorkflow/System/Flows/CompleteSaga/CompleteSaga.php
  ApplicationWorkflow/System/Flows/FailSaga/FailSaga.php
  ApplicationWorkflow/System/Flows/CompensateSaga/CompensateSaga.php
  ApplicationWorkflow/System/Flows/ResumeSaga/ResumeSaga.php
  ApplicationWorkflow/System/Capabilities/SagaState/SagaState.php
  ApplicationWorkflow/System/Capabilities/SagaState/SagaStore.php
  ApplicationWorkflow/System/Capabilities/Steps/SagaStep.php
  ApplicationWorkflow/System/Capabilities/Steps/StepRunner.php
  ApplicationWorkflow/System/Capabilities/Compensation/CompensationStep.php
  ApplicationWorkflow/System/Capabilities/Compensation/RunCompensation.php
  ApplicationWorkflow/System/Capabilities/Orchestration/OrchestrationPlan.php
  ApplicationWorkflow/System/Capabilities/Orchestration/RunOrchestration.php
  ApplicationWorkflow/System/Capabilities/Consistency/ConsistencyPolicy.php
  ApplicationWorkflow/System/Capabilities/Retries/SagaRetryPolicy.php
  ApplicationWorkflow/System/Capabilities/Timeouts/SagaTimeoutPolicy.php
  ApplicationWorkflow/System/Configuration/WorkflowConfiguration.php
  ApplicationWorkflow/System/Configuration/RegisterWorkflowDependencies.php
  ApplicationWorkflow/System/Foundation/Values/WorkflowId.php
  ApplicationWorkflow/System/Foundation/Values/SagaId.php
  ApplicationWorkflow/System/Foundation/Failure/WorkflowFailed.php
  ApplicationWorkflow/System/Foundation/Failure/SagaCompensationFailed.php
  ApplicationWorkflow/System/how-this-works.md

  Mail/System/PublicSurface/Mail.php
  Mail/System/PublicSurface/Mailer.php
  Mail/System/Flows/SendMail/SendMail.php
  Mail/System/Capabilities/Messages/MailMessage.php
  Mail/System/Capabilities/Transports/MailTransport.php
  Mail/System/Capabilities/Transports/NullMailTransport.php
  Mail/System/Capabilities/Transports/SmtpMailTransport.php
  Mail/System/Configuration/MailConfiguration.php
  Mail/System/Configuration/RegisterMailDependencies.php
  Mail/System/Foundation/Values/EmailAddress.php
  Mail/System/Foundation/Values/MailSubject.php
  Mail/System/Foundation/Failure/MailSendFailed.php
  Mail/System/Foundation/Failure/MailTransportUnavailable.php
  Mail/System/how-this-works.md

  Notifications/System/PublicSurface/Notifications.php
  Notifications/System/PublicSurface/Notification.php
  Notifications/System/Flows/SendNotification/SendNotification.php
  Notifications/System/Capabilities/Channels/MailNotificationChannel.php
  Notifications/System/Capabilities/Channels/DatabaseNotificationChannel.php
  Notifications/System/Capabilities/Routing/NotificationRoute.php
  Notifications/System/Configuration/NotificationsConfiguration.php
  Notifications/System/Configuration/RegisterNotificationDependencies.php
  Notifications/System/Foundation/Values/NotificationId.php
  Notifications/System/Foundation/Failure/NotificationFailed.php
  Notifications/System/how-this-works.md

  Scheduler/System/PublicSurface/Scheduler.php
  Scheduler/System/PublicSurface/ScheduledTask.php
  Scheduler/System/Flows/RegisterScheduledTask/RegisterScheduledTask.php
  Scheduler/System/Flows/RunDueTasks/RunDueTasks.php
  Scheduler/System/Capabilities/Schedules/CronExpression.php
  Scheduler/System/Capabilities/Schedules/ScheduleRegistry.php
  Scheduler/System/Capabilities/History/ScheduledTaskHistory.php
  Scheduler/System/Configuration/SchedulerConfiguration.php
  Scheduler/System/Configuration/RegisterSchedulerDependencies.php
  Scheduler/System/Foundation/Values/ScheduleId.php
  Scheduler/System/Foundation/Failure/ScheduledTaskFailed.php
  Scheduler/System/how-this-works.md

  Concurrency/System/PublicSurface/Concurrency.php
  Concurrency/System/Flows/RunConcurrently/RunConcurrently.php
  Concurrency/System/Flows/CancelTask/CancelTask.php
  Concurrency/System/Capabilities/Tasks/ConcurrentTask.php
  Concurrency/System/Capabilities/Tasks/TaskGroup.php
  Concurrency/System/Capabilities/Cancellation/CancellationToken.php
  Concurrency/System/Capabilities/EventLoop/EventLoop.php
  Concurrency/System/Capabilities/EventLoop/SynchronousEventLoop.php
  Concurrency/System/Configuration/ConcurrencyConfiguration.php
  Concurrency/System/Configuration/RegisterConcurrencyDependencies.php
  Concurrency/System/Foundation/Failure/TaskCancelled.php
  Concurrency/System/Foundation/Failure/ConcurrentTaskFailed.php
  Concurrency/System/how-this-works.md

  Realtime/System/PublicSurface/Realtime.php
  Realtime/System/Flows/OpenRealtimeConnection/OpenRealtimeConnection.php
  Realtime/System/Flows/BroadcastRealtimeMessage/BroadcastRealtimeMessage.php
  Realtime/System/Flows/JoinRealtimeChannel/JoinRealtimeChannel.php
  Realtime/System/Flows/LeaveRealtimeChannel/LeaveRealtimeChannel.php
  Realtime/System/Capabilities/Channels/RealtimeChannel.php
  Realtime/System/Capabilities/Channels/ChannelRegistry.php
  Realtime/System/Capabilities/Connections/RealtimeConnection.php
  Realtime/System/Capabilities/Connections/ConnectionRegistry.php
  Realtime/System/Capabilities/Adapters/RealtimeAdapter.php
  Realtime/System/Configuration/RealtimeConfiguration.php
  Realtime/System/Configuration/RegisterRealtimeDependencies.php
  Realtime/System/Foundation/Values/ChannelName.php
  Realtime/System/Foundation/Values/ConnectionId.php
  Realtime/System/Foundation/Failure/RealtimeConnectionFailed.php
  Realtime/System/how-this-works.md

  Tasks/System/PublicSurface/Tasks.php
  Tasks/System/PublicSurface/Task.php
  Tasks/System/Flows/DispatchTask/DispatchTask.php
  Tasks/System/Flows/RunTask/RunTask.php
  Tasks/System/Capabilities/Registry/TaskRegistry.php
  Tasks/System/Capabilities/Execution/TaskRunner.php
  Tasks/System/Configuration/TasksConfiguration.php
  Tasks/System/Configuration/RegisterTaskDependencies.php
  Tasks/System/Foundation/Values/TaskId.php
  Tasks/System/Foundation/Failure/TaskFailed.php
  Tasks/System/how-this-works.md
```

## A6. components/Presentation and DeveloperTools

```text
components/Presentation/
  View/System/PublicSurface/View.php
  View/System/PublicSurface/ViewFactory.php
  View/System/Flows/RenderView/RenderView.php
  View/System/Flows/CompileView/CompileView.php
  View/System/Capabilities/Engines/BladeOne/BladeOneEngine.php
  View/System/Capabilities/Engines/BladeOne/BladeOneCompiler.php
  View/System/Capabilities/Engines/PhpViewEngine.php
  View/System/Capabilities/Templates/TemplateFinder.php
  View/System/Capabilities/Templates/TemplatePath.php
  View/System/Capabilities/Escaping/EscapeHtml.php
  View/System/Capabilities/Escaping/EscapeAttribute.php
  View/System/Capabilities/ViewData/ViewData.php
  View/System/Configuration/ViewConfiguration.php
  View/System/Configuration/RegisterViewDependencies.php
  View/System/Foundation/Values/ViewName.php
  View/System/Foundation/Values/TemplateFile.php
  View/System/Foundation/Failure/ViewNotFound.php
  View/System/Foundation/Failure/ViewRenderFailed.php
  View/System/how-this-works.md

components/DeveloperTools/
  Diagnostics/System/PublicSurface/Diagnostics.php
  Diagnostics/System/PublicSurface/Doctor.php
  Diagnostics/System/Flows/RunDoctor/RunDoctor.php
  Diagnostics/System/Flows/RunScalingReadinessCheck/RunScalingReadinessCheck.php
  Diagnostics/System/Capabilities/Checks/DiagnosticCheck.php
  Diagnostics/System/Capabilities/Checks/DiagnosticReport.php
  Diagnostics/System/Capabilities/ScalingReadiness/ScalingReadinessReport.php
  Diagnostics/System/Capabilities/ScalingReadiness/CheckScalingReadiness.php
  Diagnostics/System/Configuration/DiagnosticsConfiguration.php
  Diagnostics/System/Configuration/RegisterDiagnosticsDependencies.php
  Diagnostics/System/Foundation/Failure/DiagnosticFailed.php
  Diagnostics/System/how-this-works.md

  DumpDebugger/System/PublicSurface/DumpDebugger.php
  DumpDebugger/System/Flows/RenderDebugError/RenderDebugError.php
  DumpDebugger/System/Flows/RenderCliDebugError/RenderCliDebugError.php
  DumpDebugger/System/Capabilities/ErrorScreens/WhoopsCompat/WhoopsErrorScreen.php
  DumpDebugger/System/Capabilities/ErrorScreens/IgnitionCompat/IgnitionErrorScreen.php
  DumpDebugger/System/Capabilities/SourcePreview/SourcePreview.php
  DumpDebugger/System/Capabilities/Redaction/RedactDebugContext.php
  DumpDebugger/System/Capabilities/Dumps/DumpValue.php
  DumpDebugger/System/Configuration/DumpDebuggerConfiguration.php
  DumpDebugger/System/Configuration/RegisterDumpDebuggerDependencies.php
  DumpDebugger/System/Foundation/Failure/DebugRenderFailed.php
  DumpDebugger/System/how-this-works.md

  ArchitectureReview/System/PublicSurface/ArchitectureReview.php
  ArchitectureReview/System/Flows/RunArchitectureReview/RunArchitectureReview.php
  ArchitectureReview/System/Capabilities/Checkers/CheckComponentSuiteStructure.php
  ArchitectureReview/System/Capabilities/Checkers/CheckDuplicateOwners.php
  ArchitectureReview/System/Capabilities/Checkers/CheckNamespaceDrift.php
  ArchitectureReview/System/Capabilities/Checkers/CheckPublicSurface.php
  ArchitectureReview/System/Capabilities/Checkers/CheckRuntimeLeaks.php
  ArchitectureReview/System/Capabilities/Checkers/CheckDocsMirror.php
  ArchitectureReview/System/Capabilities/Checkers/CheckForbiddenFolders.php
  ArchitectureReview/System/Capabilities/Checkers/CheckVendorMonolithIsolation.php
  ArchitectureReview/System/Capabilities/Reports/ArchitectureReviewReport.php
  ArchitectureReview/System/Configuration/ArchitectureReviewConfiguration.php
  ArchitectureReview/System/Configuration/RegisterArchitectureReviewDependencies.php
  ArchitectureReview/System/Foundation/Failure/ArchitectureViolation.php
  ArchitectureReview/System/how-this-works.md

  Testing/System/PublicSurface/Testing.php
  Testing/System/Flows/FakeComponent/FakeComponent.php
  Testing/System/Flows/ResetFakes/ResetFakes.php
  Testing/System/Capabilities/Fakes/EventFake.php
  Testing/System/Capabilities/Fakes/CacheFake.php
  Testing/System/Capabilities/Fakes/MailFake.php
  Testing/System/Capabilities/Fakes/QueueFake.php
  Testing/System/Capabilities/Fakes/HttpFake.php
  Testing/System/Capabilities/Fakes/TimeFake.php
  Testing/System/Capabilities/ContractTesting/ContractTestRunner.php
  Testing/System/Capabilities/ContractTesting/ContractTestReport.php
  Testing/System/Configuration/TestingConfiguration.php
  Testing/System/Configuration/RegisterTestingDependencies.php
  Testing/System/Foundation/Failure/FakeNotRegistered.php
  Testing/System/how-this-works.md

  CodeGeneration/System/PublicSurface/CodeGeneration.php
  CodeGeneration/System/Flows/GenerateClass/GenerateClass.php
  CodeGeneration/System/Flows/GenerateController/GenerateController.php
  CodeGeneration/System/Flows/GenerateEntity/GenerateEntity.php
  CodeGeneration/System/Flows/GenerateRepository/GenerateRepository.php
  CodeGeneration/System/Flows/GenerateService/GenerateService.php
  CodeGeneration/System/Capabilities/Generators/ClassGenerator.php
  CodeGeneration/System/Capabilities/Generators/ControllerGenerator.php
  CodeGeneration/System/Capabilities/Generators/EntityGenerator.php
  CodeGeneration/System/Capabilities/Generators/RepositoryGenerator.php
  CodeGeneration/System/Capabilities/Generators/ServiceGenerator.php
  CodeGeneration/System/Capabilities/Stubs/StubFinder.php
  CodeGeneration/System/Capabilities/Stubs/RenderStub.php
  CodeGeneration/System/Capabilities/Files/WriteGeneratedFile.php
  CodeGeneration/System/Configuration/CodeGenerationConfiguration.php
  CodeGeneration/System/Configuration/RegisterCodeGenerationDependencies.php
  CodeGeneration/System/Foundation/Values/GeneratedFilePath.php
  CodeGeneration/System/Foundation/Values/StubName.php
  CodeGeneration/System/Foundation/Failure/CodeGenerationFailed.php
  CodeGeneration/System/how-this-works.md

  Profiler/System/PublicSurface/Profiler.php
  Profiler/System/Flows/ProfileOperation/ProfileOperation.php
  Profiler/System/Capabilities/Timers/StartTimer.php
  Profiler/System/Capabilities/Timers/StopTimer.php
  Profiler/System/Capabilities/Reports/ProfileReport.php
  Profiler/System/Configuration/ProfilerConfiguration.php
  Profiler/System/Configuration/RegisterProfilerDependencies.php
  Profiler/System/Foundation/Failure/ProfilingFailed.php
  Profiler/System/how-this-works.md

  ApiDocumentation/System/PublicSurface/ApiDocumentation.php
  ApiDocumentation/System/Flows/GenerateApiDocumentation/GenerateApiDocumentation.php
  ApiDocumentation/System/Capabilities/Reflection/ReadPublicApi.php
  ApiDocumentation/System/Capabilities/Rendering/RenderApiMarkdown.php
  ApiDocumentation/System/Configuration/ApiDocumentationConfiguration.php
  ApiDocumentation/System/Configuration/RegisterApiDocumentationDependencies.php
  ApiDocumentation/System/Foundation/Failure/ApiDocumentationFailed.php
  ApiDocumentation/System/how-this-works.md
```
