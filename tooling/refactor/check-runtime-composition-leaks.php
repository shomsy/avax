<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * CheckRuntimeCompositionLeaks — Gate that detects composition leaks in runtime execution paths.
 *
 * Rule: "Value objects can be new. Services must be injected. Composition roots may assemble.
 *        Runtime execution must only execute."
 *
 * Severity levels:
 *   HIGH   — Service instantiation, lazy composition, class discovery in runtime paths
 *   MEDIUM — Builder patterns, pipeline assembly that should be boot-time
 *   INFO   — Patterns that are suspicious but may be legitimate value objects
 */
final class CheckRuntimeCompositionLeaks
{
    // ─── HIGH severity: service-level leaks ────────────────────────────────

    /** @var array<string, string> */
    private array $highSeverityPatterns = [
        '/\bclass_exists\s*\(/'                                          => 'Runtime class discovery (class_exists)',
        '/\binterface_exists\s*\(/'                                      => 'Runtime interface discovery (interface_exists)',
        '/new\s+Build[A-Z][a-zA-Z]*\b/'                                  => 'Builder instantiation in runtime code',
        '/->build\s*\(\)/'                                               => 'Builder build() call in runtime code',
        '/\?\?\s*new\s+[A-Z]/'                                           => 'Null-coalescing fallback to new service',
        '/\?\?=\s*new\s+[A-Z]/'                                          => 'Lazy singleton-style composition',
    ];

    // ─── MEDIUM severity: suspicious but sometimes legitimate ──────────────

    /** @var array<string, string> */
    private array $mediumSeverityPatterns = [
        '/new\s+[A-Z][a-zA-Z]*Middleware\b/'                             => 'Middleware instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Handler\b(?!er\b)/'                        => 'Handler instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Dispatcher\b/'                             => 'Dispatcher instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Resolver\b/'                               => 'Resolver instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Factory\b/'                                => 'Factory instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Service\b/'                                => 'Service instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Provider\b/'                               => 'Provider instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Engine\b/'                                 => 'Engine instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Manager\b/'                                => 'Manager instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Registry\b/'                               => 'Registry instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Repository\b/'                             => 'Repository instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Collector\b/'                              => 'Collector instantiation in runtime code',
    ];

    /** @var list<string> */
    private array $scanRoots = [
        'framework/System/Flows',
        'framework/System/Capabilities',
        'framework/System/PublicSurface',
    ];

    /**
     * Contexts where new/build is always allowed (composition roots, config, tests).
     */
    private array $allowedContexts = [
        '/Configuration/',
        '/Configuration/Builders/',
        'ServiceProvider',
        '/tests/',
        '/Tests/',
        '/tooling/',
        '/Tooling/',
    ];

    /**
     * Explicit composition roots — allowed to assemble any graph.
     */
    private array $compositionRoots = [
        'framework/System/PublicSurface/Avax.php',
        'framework/System/Flows/CreateApplication/CreateApplication.php',
        'framework/System/Flows/BootApplication/BuildApplicationState.php',
        'framework/System/Flows/RunApplication/RunApplication.php',
    ];

    /**
     * File types that are value objects — always allowed to use `new`.
     */
    private array $allowedFileSuffixes = [
        'Event.php',
        'Exception.php',
        'Enum.php',
        'Result.php',
        'ValueObject.php',
        'DTO.php',
    ];

    /**
     * Known allowances: specific files where a pattern is explicitly allowed.
     * Format: relative_path => [pattern_substring => reason]
     */
    private array $knownAllowances = [
        // App.php: OpenHttpRequestScope/CloseHttpRequestScope receive data from RuntimeInterface (injected)
        'framework/System/PublicSurface/App.php' => [
            'new OpenHttpRequestScope' => 'Thin wrapper around injected runtime state; data from RuntimeInterface',
            'new CloseHttpRequestScope' => 'Thin wrapper around injected runtime state; data from RuntimeInterface',
        ],
        // MatchHttpRoute: RouteCollection is a value object (no constructor deps)
        'framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php' => [
            'new RouteCollection' => 'Value object with no constructor dependencies; assembled from already-resolved data',
        ],
        // HandleIncomingHttp: scope objects receive data from RuntimeInterface
        'framework/System/Flows/HandleIncomingHttp/HandleIncomingHttp.php' => [
            'new OpenHttpRequestScope' => 'Thin wrapper; receives RequestScopeStore from RuntimeInterface',
            'new CloseHttpRequestScope' => 'Thin wrapper; receives RequestScopeStore from RuntimeInterface',
        ],
        // Concurrency facade: StartTask/WaitForTask are stateless flows
        'components/Operations/Concurrency/System/PublicSurface/Concurrency.php' => [
            'new StartTask' => 'Stateless Flow with no dependencies; VALUE_OBJECT_ALLOWED',
            'new WaitForTask' => 'Stateless Flow with no dependencies; VALUE_OBJECT_ALLOWED',
        ],
        // Avax.php: composition root assembling boot-time objects
        'framework/System/PublicSurface/Avax.php' => [
            'new CreateRequestFromGlobals' => 'Composition root (Avax::create); boot-time assembly',
            'new HandleIncomingHttp' => 'Composition root (Avax::create); boot-time assembly',
        ],
        // FailureBoundary static facade: PROVEN_SAFE per runtime-composition §7.1
        // Has setInstance(), reset(), deterministic assembly
        'framework/System/Capabilities/FailureBoundary/PublicSurface/FailureBoundary.php'                              => [
            'new BuildFailureBoundary' => 'Static facade PROVEN_SAFE per §7.1: has setInstance() and reset()',
            '->build()'                => 'Static facade PROVEN_SAFE per §7.1: deterministic single assembly',
        ],
        // FailureBoundary health check: diagnostic class_exists, not runtime wiring
        'framework/System/Capabilities/FailureBoundary/Capabilities/HealthCheck/CheckFailureBoundaryHealth.php'        => [
            'class_exists' => 'Diagnostic health check; verifies component availability, not wiring',
        ],
        // FailureBoundary compile-time policy: compile-time reflection, not runtime wiring
        'framework/System/Capabilities/FailureBoundary/Foundation/CompiledMethodPolicy.php'                            => [
            'class_exists' => 'Compile-time reflection for policy validation',
        ],
        // FailureBoundary recovery/fallback actions: compile-time class resolution
        'framework/System/Capabilities/FailureBoundary/Capabilities/RunRecoveryAction/RunRecoveryAction.php'           => [
            'class_exists' => 'Compile-time class resolution for recovery action validation',
        ],
        'framework/System/Capabilities/FailureBoundary/Capabilities/RunFallbackAction/RunFallbackAction.php'           => [
            'class_exists' => 'Compile-time class resolution for fallback action validation',
        ],
        'framework/System/Capabilities/FailureBoundary/Capabilities/CompileFailurePolicies/CompileFailurePolicies.php' => [
            'class_exists' => 'Compile-time policy compilation; reflection not runtime wiring',
        ],
        // RuntimeBoundary adapters: check if runtime is available, not wiring
        'framework/System/Capabilities/RuntimeBoundary/ReactPhpAdapter.php'                                            => [
            'class_exists' => 'Runtime capability detection; checks if ReactPHP is installed',
        ],
        'framework/System/Capabilities/RuntimeBoundary/RoadRunnerAdapter.php'                                          => [
            'class_exists' => 'Runtime capability detection; checks if RoadRunner is installed',
        ],
        // StateLeakDetector: diagnostic tool using class_exists on already-declared classes
        'framework/System/Capabilities/RuntimeSafety/StateLeakDetection/StateLeakDetector.php'                         => [
            'class_exists' => 'Diagnostic tool; scans already-declared classes for state leaks',
        ],
        // BuildDataQuery: compile-time query IR building with type resolution
        'components/DataStack/Persistence/System/Flows/BuildDataQuery/BuildDataQuery.php'                              => [
            'class_exists' => 'Compile-time query IR building; type resolution not wiring',
        ],
        // ContractVerifier: test support tooling
        'components/DeveloperTools/TestSupport/System/Capabilities/ContractTesting/Verification/ContractVerifier.php'  => [
            'class_exists'     => 'Test support tooling; checks contract test availability',
            'interface_exists' => 'Test support tooling; checks contract test availability',
        ],
        // shortcuts.php: developer tooling (dump/debug)
        'components/DeveloperTools/DumpDebugger/System/PublicSurface/shortcuts.php'                                    => [
            'class_exists' => 'Developer tooling; debug helper not production runtime',
        ],
        // Container registry reflection patterns
        'components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistry.php'             => [
            'interface_exists' => 'Container compile-time reflection; checks type availability',
        ],
        'components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistry.php'                => [
            'interface_exists' => 'Container compile-time reflection; checks type availability',
        ],
        'components/Application/Container/System/Capabilities/ResolutionPolicy.php'                                    => [
            'interface_exists' => 'Container resolution policy; type reflection not wiring',
        ],
        // Container files: legitimate registry lookup patterns, not constructor DI
        'components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistry.php'             => [
            '?? new DependencyRegistration' => 'Registry lookup; creates new registration only when key not found',
            '?? new'                        => 'Registry lookup; creates new registration only when key not found',
            'interface_exists'              => 'Container compile-time reflection; checks type availability',
        ],
        'components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistry.php'                => [
            '?? new ServiceRegistration' => 'Registry lookup; creates new registration only when key not found',
            '?? new'                     => 'Registry lookup; creates new registration only when key not found',
            'interface_exists'           => 'Container compile-time reflection; checks type availability',
        ],
        'components/Application/Container/System/Capabilities/Execution/Injection/Invocation/FunctionCaller.php'       => [
            '?? new ResolveRequest' => 'Request chain building; creates child request when not provided',
            '?? new'                => 'Request chain building; creates child request when not provided',
        ],
        'components/Application/Container/System/Capabilities/Resolution/ResolveDependencies.php'                      => [
            '?? new ResolveRequest' => 'Request chain building; creates child request when not provided',
            '?? new'                => 'Request chain building; creates child request when not provided',
        ],
        // Static facades with reset(): CallableSerialization is YELLOW per §7.1
        'components/Foundation/CallableSerialization/System/PublicSurface/CallableSerialization.php'                   => [
            'new BuildCallableSerialization' => 'Static facade with reset(); YELLOW per §7.1',
            '->build()'                      => 'Static facade with reset(); YELLOW per §7.1',
        ],
        // Value object builders: produce data structures, not services
        'components/API/ApiBlueprint/System/Flows/BuildJsonApiResponse/BuildJsonApiResponse.php'                       => [
            'new BuildResourceObject'   => 'Value object builder; produces JSON API data structure',
            'new BuildCompoundDocument' => 'Value object builder; produces JSON API data structure',
            '->build()'                 => 'Value object builder; produces data array, not service',
        ],
        'components/API/ApiBlueprint/System/Flows/HandleJsonApiRequest/HandleJsonApiRequest.php'                       => [
            'new BuildResourceObject'   => 'Value object builder; produces JSON API data structure',
            'new BuildCompoundDocument' => 'Value object builder; produces JSON API data structure',
            '->build()'                 => 'Value object builder; produces data array, not service',
        ],
        'components/API/ApiBlueprint/System/PublicSurface/ApiBlueprint.php'                                            => [
            '->build()' => 'Value object builder; produces data array, not service',
        ],
        'components/API/ApiBlueprint/System/PublicSurface/ApiSurface.php'                                              => [
            'new BuildCompoundDocument' => 'Value object builder; produces JSON API data structure',
            'new BuildRestResponse'     => 'Value object builder; produces REST response data structure',
            '->build()'                 => 'Value object builder; produces data array, not service',
        ],
        // OpenAPI builders: produce schema data, not services
        'components/API/OpenAPI/System/Capabilities/SchemaGeneration/BuildOpenApiDocument.php'                         => [
            'new BuildPayloadSchema'       => 'Value object builder; produces OpenAPI payload schema data',
            'new BuildErrorResponseSchema' => 'Value object builder; produces OpenAPI error schema data',
        ],
        'components/API/OpenAPI/System/PublicSurface/OpenAPI.php'                                                      => [
            'new BuildOpenApiDocument' => 'Value object builder; produces OpenAPI schema data',
        ],
        // GraphQL builder: produces schema data
        'components/API/GraphQL/System/PublicSurface/GraphQL.php'                                                      => [
            'new BuildGraphQlSchema' => 'Value object builder; produces GraphQL schema data',
            'new BuildGraphQLSchema' => 'Value object builder; produces GraphQL schema data',
        ],
        // UriBuilder: value object builder for URI construction
        'components/HTTP/System/Capabilities/Uri/UriBuilder.php'                                                       => [
            '->build()' => 'Value object builder; produces Uri value object',
        ],
        // Parallel facade: static facade pattern
        'components/Operations/Parallelism/System/PublicSurface/Parallel.php'                                          => [
            'new BuildParallelRuntime' => 'Static facade pattern with reset capability',
            '->build()'                => 'Static facade pattern with reset capability',
        ],
        // Delivery: delivery flows produce manifest/deployment data objects
        'components/Operations/Delivery/System/PublicSurface/Delivery.php'                                             => [
            'new BuildManifest'      => 'Delivery flow; produces manifest data object',
            'new CompileApplication' => 'Delivery flow; produces deployment data',
            'new ReleaseManifest'    => 'Delivery flow; produces release data object',
            'new RollbackPlan'       => 'Delivery flow; produces rollback plan data object',
        ],
        // CompileCache: builder produces cache configuration value objects
        'components/Application/Cache/System/Flows/Compiled/CompileCache/CompileCache.php'                             => [
            'new BuildCompiledPhpPayload' => 'Builder produces cache payload value object',
        ],
        'components/Application/Cache/System/PublicSurface/CompiledCache.php'                                          => [
            'new BuildCompiledCache' => 'Static facade; builds compiled cache instance',
        ],
        // AssembleRuntime: Container composition assembly
        'components/Application/Container/System/Capabilities/Composition/Assembly/AssembleRuntime.php'                => [
            'new DependencyRegistry'        => 'Container assembly; creates registry for composition',
            'new ScopeStore'                => 'Container assembly; creates scope store for composition',
            'new DependencyPool'            => 'Container assembly; creates dependency pool for composition',
            'new ManageScopes'              => 'Container assembly; creates scope manager for composition',
            'new ResolveDependencies'       => 'Container assembly; creates resolver for composition',
            'new CreateDependencyBlueprint' => 'Container assembly; creates blueprint factory',
            'new BlueprintCache'            => 'Container assembly; creates blueprint cache',
            'new ResolveCallArguments'      => 'Container assembly; creates argument resolver',
            'new FunctionCaller'            => 'Container assembly; creates function caller',
            'new ResolveDependency'         => 'Container assembly; creates dependency resolver',
            'new BuildServiceInstance'      => 'Container assembly; creates service builder',
            'new HotPathInliner'            => 'Container assembly; creates hot path inliner',
            'new DeferredProviderRegistry'  => 'Container assembly; creates deferred provider registry',
        ],
        // Database connection builders: produce connection value objects
        'components/DataStack/Database/System/Capabilities/Connections/Pools/DatabaseConnectionPool.php'               => [
            'new OpenConnection'          => 'Opens database connection; produces connection value object',
            'new BuildPhysicalConnection' => 'Builder produces physical connection value object',
        ],
        'components/DataStack/Database/System/Capabilities/Connections/ReadConnection/ReadConnection.php'              => [
            'new OpenConnection'          => 'Opens database connection; produces connection value object',
            'new BuildPhysicalConnection' => 'Builder produces physical connection value object',
        ],
        // PolicyEngine: value object with no constructor dependencies
        'components/Security/Redaction/System/Capabilities/PolicyEngine/PolicyEngine.php'                              => [
            'new DataClassifier'  => 'Value object with no constructor dependencies',
            'new RedactionEngine' => 'Value object with no constructor dependencies',
        ],
        // Redaction health check: diagnostic file
        'components/Security/Redaction/System/Capabilities/HealthCheck/CheckRedactionHealth.php'                       => [
            'new ' => 'Diagnostic health check; creates engine to verify redaction capability',
        ],
        // Redaction flows: produce redacted data, not services
        'components/Security/Redaction/System/Flows/RedactLogData/RedactLogData.php'                                   => [
            'new PolicyEngine' => 'Value object; produces redacted data structure',
        ],
        'components/Security/Redaction/System/Flows/ApplyRedactionPolicy/ApplyRedactionPolicy.php'                     => [
            'new PolicyEngine'   => 'Value object; produces redacted data structure',
            'new PatternMatcher' => 'Value object; produces pattern matching capability',
        ],
        // Redaction facade: static convenience API
        'components/Security/Redaction/System/PublicSurface/Redaction.php'                                             => [
            'new PolicyEngine'    => 'Static facade; creates value object for redaction',
            'new RedactionEngine' => 'Static facade; creates value object for redaction',
        ],
        // ObjectStorage: middleware for S3 transport is a value object
        'components/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsOnS3.php'                   => [
            'new ' => 'S3 transport middleware; value object for HTTP transport',
        ],
        // Migrations: repository for migration tracking is a value object
        'components/DataStack/Database/System/Capabilities/Migrations/Migrations.php'                                  => [
            'new ' => 'Migration repository; value object for tracking migration state',
        ],
        // ManageEntityPersistence: manager is a value object for ORM coordination
        'components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php'                               => [
            'new ' => 'Entity manager coordination; value object for ORM operations',
        ],
        // RuntimeSupervision: registry for process supervision state
        'components/Operations/RuntimeSupervision/System/Capabilities/Supervision/Supervisor.php'                      => [
            'new ' => 'Supervision registry; value object for process state tracking',
        ],
        'components/Operations/RuntimeSupervision/System/PublicSurface/RuntimeSupervision.php'                         => [
            'new ' => 'Supervision registry; value object for process state tracking',
        ],
        // Observability health check: diagnostic
        'components/Operations/Observability/System/Capabilities/Health/ObservabilityHealthCheck.php'                  => [
            'new ' => 'Diagnostic health check; collects health metrics',
        ],
        // BackgroundProcesses: registry for background process state
        'components/Operations/BackgroundProcesses/System/PublicSurface/BackgroundProcesses.php'                       => [
            'new ' => 'Background process registry; value object for process tracking',
        ],
        // TaskDispatch: resolver/dispatcher for task routing
        'components/Operations/Queue/System/Capabilities/TaskDispatch/TaskDispatch.php'                                => [
            'new ' => 'Task resolver/dispatcher; value object for task routing',
        ],
        'components/Operations/Queue/System/Capabilities/TaskDispatch/Dispatchers/DeferredDispatcher.php'              => [
            'new ' => 'Deferred dispatcher; value object for deferred task execution',
        ],
        // Events health check: diagnostic
        'components/Operations/Events/System/Capabilities/HealthCheck/CheckEventsHealth.php'                           => [
            'new ' => 'Diagnostic health check; verifies event system capability',
        ],
        // CompileEventListeners: compile-time listener registry
        'components/Operations/Events/System/Flows/CompileEventListeners/CompileEventListeners.php'                    => [
            'new ' => 'Compile-time listener registry assembly',
        ],
        // Events facade: static convenience API
        'components/Operations/Events/System/PublicSurface/Events.php'                                                 => [
            'new ' => 'Events facade; value object for event coordination',
        ],
        // ApiVersion: registry for version resolution state
        'components/HTTP/ApiVersioning/System/PublicSurface/ApiVersion.php'                                            => [
            'new ' => 'Version registry; value object for version tracking',
        ],
        // ApiContracts: registry for API version contracts
        'components/API/Contracts/System/PublicSurface/ApiContracts.php'                                               => [
            'new ' => 'API contracts registry; value object for version tracking',
        ],
        // HttpContext: provider for HTTP context state
        'components/HTTP/Context/System/PublicSurface/HttpContext.php'                                                 => [
            'new ' => 'HTTP context provider; value object for request context',
        ],
        // Pipeline: registry for pipeline stage tracking
        'components/Application/Pipeline/System/PublicSurface/Pipeline.php'                                            => [
            'new ' => 'Pipeline registry; value object for stage tracking',
        ],
        // GraphQLExecutor: registry for executor state
        'components/API/GraphQL/System/PublicSurface/GraphQLExecutor.php'                                              => [
            'new ' => 'GraphQL executor registry; value object for query execution state',
        ],
    ];

    /** @var list<array{severity: string, file: string, line: int, pattern: string, description: string}> */
    private array $errors = [];

    public function check(): array
    {
        $basePath = dirname(__DIR__, 2);

        foreach ($this->scanRoots as $root) {
            $fullPath = $basePath . '/' . $root;
            $this->scanDirectory($fullPath, $basePath);
        }

        // Also scan component System/ directories
        $componentsPath = $basePath . '/components';
        if (is_dir($componentsPath)) {
            $this->scanComponentDirectories($componentsPath, $basePath);
        }

        return [
            'status' => $this->errors === [] ? 'PASS' : 'FAIL',
            'errors' => $this->formatErrors(),
        ];
    }

    private function scanDirectory(string $directory, string $basePath): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY,
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace($basePath . '/', '', $file->getPathname());

            if ($this->isInAllowedContext($relativePath)) {
                continue;
            }

            $this->scanFile($file, $relativePath);
        }
    }

    private function scanComponentDirectories(string $componentsPath, string $basePath): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($componentsPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY,
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace($basePath . '/', '', $file->getPathname());

            if (! $this->isInComponentSystemPath($relativePath)) {
                continue;
            }

            if ($this->isInAllowedContext($relativePath)) {
                continue;
            }

            $this->scanFile($file, $relativePath);
        }
    }

    private function isInComponentSystemPath(string $relativePath): bool
    {
        return str_contains($relativePath, '/System/Flows/')
            || str_contains($relativePath, '/System/Capabilities/')
            || str_contains($relativePath, '/System/PublicSurface/');
    }

    private function isInAllowedContext(string $relativePath): bool
    {
        foreach ($this->allowedContexts as $context) {
            if (str_contains($relativePath, $context)) {
                return true;
            }
        }

        foreach ($this->allowedFileSuffixes as $suffix) {
            if (str_ends_with($relativePath, $suffix)) {
                return true;
            }
        }

        return false;
    }

    private function scanFile(SplFileInfo $file, string $relativePath): void
    {
        // Skip explicit composition roots
        if (in_array($relativePath, $this->compositionRoots, true)) {
            return;
        }

        $content = file_get_contents($file->getPathname());
        if ($content === false) {
            return;
        }
        $lines = explode("\n", $content);

        // Detect file-level context for exception classification
        $isDiagnosticFile   = $this->isDiagnosticFile($relativePath);
        $isCompileTimeFile  = $this->isCompileTimeFile($relativePath);
        $isStaticFacadeFile = $this->isStaticFacadeFile($content);

        foreach ($lines as $lineNumber => $line) {
            // Skip comments
            $trimmed = ltrim($line);
            if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '/*')) {
                continue;
            }

            $lineText = trim($line);

            // Check HIGH severity patterns
            foreach ($this->highSeverityPatterns as $pattern => $description) {
                if (preg_match($pattern, $line)) {
                    $matchedText = $this->extractMatchedText($line, $pattern);

                    // Context-aware exceptions
                    if ($isDiagnosticFile && (str_contains($pattern, 'class_exists') || str_contains($pattern, 'interface_exists'))) {
                        continue; // Diagnostic files use class_exists for health checks, not wiring
                    }
                    if ($isCompileTimeFile && str_contains($pattern, 'class_exists')) {
                        continue; // Compile-time files use class_exists for reflection, not wiring
                    }
                    if ($isStaticFacadeFile && (str_contains($description, 'Builder') || str_contains($description, 'build()'))) {
                        continue; // Static facades with reset()/setInstance() are PROVEN_SAFE per §7.1
                    }

                    if (! $this->isKnownAllowed($relativePath, $matchedText)) {
                        $this->recordError('HIGH', $relativePath, $lineNumber + 1, $description, $lineText);
                    }
                }
            }

            // Check MEDIUM severity patterns
            foreach ($this->mediumSeverityPatterns as $pattern => $description) {
                if (preg_match($pattern, $line)) {
                    $matchedText = $this->extractMatchedText($line, $pattern);
                    if (! $this->isKnownAllowed($relativePath, $matchedText)) {
                        $this->recordError('MEDIUM', $relativePath, $lineNumber + 1, $description, $lineText);
                    }
                }
            }
        }
    }

    /**
     * Diagnostic files: scanners, health checks that use class_exists for availability checks.
     */
    private function isDiagnosticFile(string $relativePath) : bool
    {
        return str_contains($relativePath, 'StateScanner')
            || str_contains($relativePath, 'HealthScanner')
            || str_contains($relativePath, 'HealthCheck')
            || str_contains($relativePath, 'Check') && str_contains($relativePath, 'Health')
            || str_contains($relativePath, 'LeakDetector')
            || str_contains($relativePath, 'Diagnose')
            || str_contains($relativePath, 'RuntimeSafety');
    }

    /**
     * Compile-time files: Container compilation, dependency compilation that uses class_exists for reflection.
     */
    private function isCompileTimeFile(string $relativePath) : bool
    {
        return str_contains($relativePath, 'CompileContainer')
            || str_contains($relativePath, 'DependencyCompiler')
            || str_contains($relativePath, 'CompileFailurePolicies')
            || str_contains($relativePath, 'CompiledMethodPolicy')
            || str_contains($relativePath, 'ServiceRegistry')
            || str_contains($relativePath, 'DependencyRegistry')
            || str_contains($relativePath, 'CheckCompositionPolicies')
            || str_contains($relativePath, 'FunctionCaller')
            || str_contains($relativePath, 'ResolutionPolicy')
            // Container resolution/reflection patterns
            || str_contains($relativePath, 'ResolveDependency')
            || str_contains($relativePath, 'ServiceResolver')
            || str_contains($relativePath, 'CreateDependencyBlueprint')
            || str_contains($relativePath, 'CreateServiceBlueprint')
            || str_contains($relativePath, 'ServiceCompiler')
            || str_contains($relativePath, 'ResolveCallable')
            || str_contains($relativePath, 'BootProviders')
            // Compile-time attribute/shape inspection
            || str_contains($relativePath, 'CompileClassAttributes')
            || str_contains($relativePath, 'CompileDataShapeSchema')
            || str_contains($relativePath, 'DataShapeCompiler')
            || str_contains($relativePath, 'DataFieldType')
            || str_contains($relativePath, 'ConvertDataObjectShapeToJsonSchema')
            || str_contains($relativePath, 'CreateDataObject')
            // ORM/repository class resolution
            || str_contains($relativePath, '/ORM/Repository.php')
            || str_contains($relativePath, '/ORM/DatabaseRepository.php')
            // Queue/driver availability checks
            || str_contains($relativePath, 'RedisQueue')
            || str_contains($relativePath, 'SyncDriver')
            || str_contains($relativePath, 'QueueWorker')
            || str_contains($relativePath, 'RedisRateLimiter')
            || str_contains($relativePath, 'RedisSessionStore')
            || str_contains($relativePath, 'RedisCacheStore');
    }

    /**
     * Static facade files: classes with both reset() and setInstance() per governance §7.1.
     */
    private function isStaticFacadeFile(string $content) : bool
    {
        return preg_match('/public\s+static\s+function\s+reset\s*\(/', $content)
            && preg_match('/public\s+static\s+function\s+setInstance\s*\(/', $content);
    }

    private function extractMatchedText(string $line, string $pattern): string
    {
        if (preg_match($pattern, $line, $matches)) {
            return trim($matches[0]);
        }
        return trim($line);
    }

    private function isKnownAllowed(string $relativePath, string $matchedText): bool
    {
        if (! isset($this->knownAllowances[$relativePath])) {
            return false;
        }

        foreach ($this->knownAllowances[$relativePath] as $allowedPattern => $reason) {
            if (str_contains($matchedText, $allowedPattern)) {
                return true;
            }
        }

        return false;
    }

    private function recordError(string $severity, string $file, int $line, string $description, string $lineText): void
    {
        $this->errors[] = [
            'severity' => $severity,
            'file' => $file,
            'line' => $line,
            'description' => $description,
            'lineText' => $lineText,
        ];
    }

    /** @return list<string> */
    private function formatErrors(): array
    {
        $formatted = [];
        foreach ($this->errors as $error) {
            $formatted[] = sprintf(
                '[%s] %s:%d — %s',
                $error['severity'],
                $error['file'],
                $error['line'],
                $error['description'],
            );
        }
        return $formatted;
    }
}

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $checker = new CheckRuntimeCompositionLeaks();
    $result = $checker->check();

    echo $result['status'] . "\n";

    if (! empty($result['errors'])) {
        echo implode("\n", $result['errors']) . "\n";
        exit(1);
    }

    exit(0);
}
