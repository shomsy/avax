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
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolutionPolicy;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependencies;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency;
use Avax\Components\Application\Container\System\Capabilities\Runtime\CompiledRuntime;
use Avax\Components\Application\Container\System\Capabilities\Runtime\DependencyPool;
use Avax\Components\Application\Container\System\Capabilities\Runtime\HotPathInliner;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ManageScopes;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ScopeStore;

/**
 * Builds the runtime and compilation collaborators for one container instance.
 */
final class AssembleRuntime
{
    public function assemble(CreateContainerConfig $config, ObservabilityAssembly $observability) : RuntimeAssembly
    {
        $registrations = new DependencyRegistry();
        $scopeStore    = new ScopeStore();
        $servicePool   = new DependencyPool();
        $scopes        = new ManageScopes(
            store  : $scopeStore,
            pool   : $servicePool,
            metrics: $observability->metrics,
        );
        $dependencies  = new ResolveDependencies();
        $blueprints    = new CreateDependencyBlueprint(
            cache       : new BlueprintCache(
                              cacheDir    : $config->cacheDir,
                              cacheVersion: $config->cacheVersion,
                              debug       : $config->debug,
                              metrics     : $observability->metrics,
                          ),
            dependencies: $dependencies,
        );
        $callArguments = new ResolveCallArguments(dependencies: $dependencies);
        $caller        = new FunctionCaller(arguments: $callArguments);
        $policy        = new ResolutionPolicy(
            strict  : $config->strict,
            debug   : $config->debug,
            profile : $config->effectivePolicyProfile(),
            failMode: $config->policyFailMode,
            profiles: $config->policyProfiles,
        );
        $compiler      = new CompileContainer(
            registrations         : $registrations,
            blueprints            : $blueprints,
            cacheDir              : $config->cacheDir,
            cacheVersion          : $config->cacheVersion,
            configHash            : $config->configHash(),
            diagnosticsMode       : $config->diagnosticsMode,
            environment           : $config->environment(),
            compileMode           : $config->compileMode,
            strict                : $config->strict,
            settingsFingerprint   : $config->settingsFingerprint(),
            benchmarkBuildMarker  : $config->benchmarkBuildMarker(),
            executionMode         : $config->executionMode,
            pruneMode             : $config->pruneMode,
            validateOnLoad        : $config->validatesCompiledArtifactsOnLoad(),
            failClosedOnCorruption: $config->failsClosedOnCompiledCorruption(),
            validateBeforeCompile : $config->validatesBeforeCompile(),
            metrics               : $observability->metrics,
            services              : new DependencyCompiler(
                                        registrations: $registrations,
                                        blueprints   : $blueprints,
                                    ),
        );
        $resolver      = new ResolveDependency(
            registrations    : $registrations,
            scopes           : $scopes,
            builder          : new BuildService(
                                   blueprints  : $blueprints,
                                   dependencies: $dependencies,
                               ),
            blueprints       : $blueprints,
            injectProperties : new InjectProperties(),
            injectMethods    : new InjectMethods(arguments: $callArguments),
            caller           : $caller,
            metrics          : $observability->metrics,
            timeline         : $observability->timeline,
            policy           : $policy,
            compiledRuntime  : new CompiledRuntime(
                                   compiler     : $compiler,
                                   inliner      : new HotPathInliner(),
                                   metrics      : $observability->metrics,
                                   executionMode: $config->executionMode,
                               ),
            deferredProviders: new DeferredProviderRegistry(),
            diagnosticsMode  : $config->diagnosticsMode,
            environment      : $config->environment(),
            sliceBoundaryMode: $config->sliceBoundaryMode,
            asyncTarget      : $config->asyncTarget,
        );

        return new RuntimeAssembly(
            registrations: $registrations,
            scopeStore   : $scopeStore,
            servicePool  : $servicePool,
            scopes       : $scopes,
            caller       : $caller,
            policy       : $policy,
            compiler     : $compiler,
            resolver     : $resolver,
        );
    }
}
