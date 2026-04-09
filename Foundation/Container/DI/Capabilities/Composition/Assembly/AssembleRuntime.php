<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Composition\Assembly;

use Avax\Container\DI\Capabilities\Composition\Compilation\CompileContainer;
use Avax\Container\DI\Capabilities\Composition\Compilation\ServiceCompiler;
use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Container\DI\Capabilities\Declaration\Bindings\ServiceRegistry;
use Avax\Container\DI\Capabilities\Declaration\Blueprints\BlueprintCache;
use Avax\Container\DI\Capabilities\Declaration\Blueprints\CreateServiceBlueprint;
use Avax\Container\DI\Capabilities\Declaration\Providers\DeferredProviderRegistry;
use Avax\Container\DI\Capabilities\Execution\BuildService;
use Avax\Container\DI\Capabilities\Runtime\CompiledRuntime;
use Avax\Container\DI\Capabilities\Resolution\ResolutionPolicy;
use Avax\Container\DI\Capabilities\Resolution\ResolveDependencies;
use Avax\Container\DI\Capabilities\Resolution\ServiceResolver;
use Avax\Container\DI\Capabilities\Execution\Injection\Invocation\FunctionCaller;
use Avax\Container\DI\Capabilities\Execution\Injection\Invocation\ResolveCallArguments;
use Avax\Container\DI\Capabilities\Execution\Injection\Methods\InjectMethods;
use Avax\Container\DI\Capabilities\Execution\Injection\Properties\InjectProperties;
use Avax\Container\DI\Capabilities\Runtime\Scopes\ManageScopes;
use Avax\Container\DI\Capabilities\Runtime\Scopes\ScopeStore;
use Avax\Container\DI\Capabilities\Runtime\HotPathInliner;
use Avax\Container\DI\Capabilities\Runtime\ServicePool;

/**
 * Builds the runtime and compilation collaborators for one container instance.
 */
final class AssembleRuntime
{
    public function assemble(CreateContainerConfig $config, ObservabilityAssembly $observability) : RuntimeAssembly
    {
        $registrations = new ServiceRegistry;
        $scopeStore = new ScopeStore;
        $servicePool = new ServicePool;
        $scopes = new ManageScopes(
            store  : $scopeStore,
            pool   : $servicePool,
            metrics: $observability->metrics
        );
        $dependencies = new ResolveDependencies;
        $blueprints = new CreateServiceBlueprint(
            cache       : new BlueprintCache(
                cacheDir    : $config->cacheDir,
                cacheVersion: $config->cacheVersion,
                debug       : $config->debug,
                metrics     : $observability->metrics
            ),
            dependencies: $dependencies
        );
        $callArguments = new ResolveCallArguments(dependencies: $dependencies);
        $caller = new FunctionCaller(arguments: $callArguments);
        $policy = new ResolutionPolicy(
            strict: $config->strict,
            debug : $config->debug,
            profile: $config->effectivePolicyProfile(),
            failMode: $config->policyFailMode,
            profiles: $config->policyProfiles
        );
        $compiler = new CompileContainer(
            registrations           : $registrations,
            blueprints              : $blueprints,
            cacheDir                : $config->cacheDir,
            cacheVersion            : $config->cacheVersion,
            configHash              : $config->configHash(),
            diagnosticsMode         : $config->diagnosticsMode,
            environment             : $config->environment(),
            compileMode             : $config->compileMode,
            strict                  : $config->strict,
            settingsFingerprint     : $config->settingsFingerprint(),
            benchmarkBuildMarker    : $config->benchmarkBuildMarker(),
            executionMode           : $config->executionMode,
            pruneMode               : $config->pruneMode,
            validateOnLoad          : $config->validatesCompiledArtifactsOnLoad(),
            failClosedOnCorruption  : $config->failsClosedOnCompiledCorruption(),
            validateBeforeCompile   : $config->validatesBeforeCompile(),
            metrics                 : $observability->metrics,
            services                : new ServiceCompiler(
                registrations: $registrations,
                blueprints   : $blueprints
            )
        );
        $resolver = new ServiceResolver(
            registrations   : $registrations,
            scopes          : $scopes,
            builder         : new BuildService(
                blueprints  : $blueprints,
                dependencies: $dependencies
            ),
            blueprints      : $blueprints,
            injectProperties: new InjectProperties,
            injectMethods   : new InjectMethods(arguments: $callArguments),
            caller          : $caller,
            metrics         : $observability->metrics,
            timeline        : $observability->timeline,
            policy          : $policy,
            compiledRuntime : new CompiledRuntime(
                compiler     : $compiler,
                inliner      : new HotPathInliner,
                metrics      : $observability->metrics,
                executionMode: $config->executionMode
            ),
            deferredProviders: new DeferredProviderRegistry,
            diagnosticsMode : $config->diagnosticsMode,
            environment     : $config->environment(),
            sliceBoundaryMode: $config->sliceBoundaryMode,
            asyncTarget     : $config->asyncTarget
        );

        return new RuntimeAssembly(
            registrations: $registrations,
            scopeStore   : $scopeStore,
            servicePool  : $servicePool,
            scopes       : $scopes,
            caller       : $caller,
            policy       : $policy,
            compiler     : $compiler,
            resolver     : $resolver
        );
    }
}
