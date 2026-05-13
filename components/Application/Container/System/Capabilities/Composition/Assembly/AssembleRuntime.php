<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Composition\Assembly;

use Avax\Components\Application\Container\System\Capabilities\Composition\Compilation\CompileContainer;
use Avax\Components\Application\Container\System\Capabilities\Composition\Compilation\DependencyCompiler;
use Avax\Components\Application\Container\System\Capabilities\Composition\CreateContainerConfig;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistry;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Blueprints\BlueprintCache;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Blueprints\CreateDependencyBlueprint;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Providers\DeferredProviderRegistry;
use Avax\Components\Application\Container\System\Capabilities\Execution\BuildService;
use Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Invocation\FunctionCaller;
use Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Invocation\ResolveCallArguments;
use Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Methods\InjectMethods;
use Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Properties\InjectProperties;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependencies;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency;
use Avax\Components\Application\Container\System\Capabilities\ResolutionPolicy;
use Avax\Components\Application\Container\System\Capabilities\Runtime\CompiledRuntime;
use Avax\Components\Application\Container\System\Capabilities\Runtime\DependencyPool;
use Avax\Components\Application\Container\System\Capabilities\Runtime\HotPathInliner;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ManageScopes;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ScopeStore;
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;

/**
 * Builds the runtime and compilation collaborators for one container instance.
 */
final class AssembleRuntime
{
    public function assemble(CreateContainerConfig $createContainerConfig, ObservabilityAssembly $observabilityAssembly): RuntimeAssembly
    {
        $dependencyRegistry = new DependencyRegistry();
        $scopeStore = new ScopeStore();
        $dependencyPool = new DependencyPool();
        $manageScopes = new ManageScopes(
            store  : $scopeStore,
            pool   : $dependencyPool,
            metrics: $observabilityAssembly->metrics,
        );
        $resolveDependencies = new ResolveDependencies();
        $createDependencyBlueprint = new CreateDependencyBlueprint(
            blueprintCache     : new BlueprintCache(
                                     filesystem  : new Filesystem(),
                                     cacheDir    : $createContainerConfig->cacheDir,
                                     cacheVersion: $createContainerConfig->cacheVersion,
                                     debug       : $createContainerConfig->debug,
                resolutionMetrics: $observabilityAssembly->metrics,
            ),
            resolveDependencies: $resolveDependencies,
        );
        $resolveCallArguments = new ResolveCallArguments(dependencies: $resolveDependencies);
        $functionCaller = new FunctionCaller(arguments: $resolveCallArguments);
        $resolutionPolicy = new ResolutionPolicy(
            strict  : $createContainerConfig->strict,
            debug   : $createContainerConfig->debug,
            profile : $createContainerConfig->effectivePolicyProfile(),
            failMode: $createContainerConfig->policyFailMode,
            profiles: $createContainerConfig->policyProfiles,
        );
        $compileContainer = new CompileContainer(
            cacheDir              : $createContainerConfig->cacheDir,
            cacheVersion          : $createContainerConfig->cacheVersion,
            configHash            : $createContainerConfig->configHash(),
            diagnosticsMode       : $createContainerConfig->diagnosticsMode,
            environment           : $createContainerConfig->environment(),
            compileMode           : $createContainerConfig->compileMode,
            strict                : $createContainerConfig->strict,
            settingsFingerprint   : $createContainerConfig->settingsFingerprint(),
            benchmarkBuildMarker  : $createContainerConfig->benchmarkBuildMarker(),
            executionMode         : $createContainerConfig->executionMode,
            pruneMode             : $createContainerConfig->pruneMode,
            validateOnLoad        : $createContainerConfig->validatesCompiledArtifactsOnLoad(),
            failClosedOnCorruption: $createContainerConfig->failsClosedOnCompiledCorruption(),
            validateBeforeCompile : $createContainerConfig->validatesBeforeCompile(),
            registrations         : $dependencyRegistry,
            blueprints            : $createDependencyBlueprint,
            metrics               : $observabilityAssembly->metrics,
            services              : new DependencyCompiler(
                registrations: $dependencyRegistry,
                blueprints   : $createDependencyBlueprint,
            ),
            filesystem            : new Filesystem(),
        );
        $resolveDependency = new ResolveDependency(
            registrations    : $dependencyRegistry,
            scopes           : $manageScopes,
            builder          : new BuildService(
                blueprints  : $createDependencyBlueprint,
                dependencies: $resolveDependencies,
            ),
            blueprints       : $createDependencyBlueprint,
            injectProperties : new InjectProperties(),
            injectMethods    : new InjectMethods(arguments: $resolveCallArguments),
            caller           : $functionCaller,
            metrics          : $observabilityAssembly->metrics,
            timeline         : $observabilityAssembly->timeline,
            policy           : $resolutionPolicy,
            compiledRuntime  : new CompiledRuntime(
                executionMode: $createContainerConfig->executionMode,
                compiler     : $compileContainer,
                inliner      : new HotPathInliner(),
                metrics      : $observabilityAssembly->metrics,
            ),
            deferredProviders: new DeferredProviderRegistry(),
            diagnosticsMode  : $createContainerConfig->diagnosticsMode,
            environment      : $createContainerConfig->environment(),
            sliceBoundaryMode: $createContainerConfig->sliceBoundaryMode,
            asyncTarget      : $createContainerConfig->asyncTarget,
        );

        return new RuntimeAssembly(
            registrations: $dependencyRegistry,
            scopeStore   : $scopeStore,
            servicePool  : $dependencyPool,
            scopes       : $manageScopes,
            caller       : $functionCaller,
            policy       : $resolutionPolicy,
            compiler     : $compileContainer,
            resolver     : $resolveDependency,
        );
    }
}
