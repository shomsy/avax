<?php

declare(strict_types=1);

namespace Avax\Container\Configuration\Assembly;

use Avax\Container\Compilation\CompileContainer;
use Avax\Container\Compilation\ServiceCompiler;
use Avax\Container\Configuration\CreateContainerConfig;
use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistry;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\BlueprintCache;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\CreateServiceBlueprint;
use Avax\Container\DependencyInjection\Dependencies\Providers\DeferredProviderRegistry;
use Avax\Container\DependencyInjection\Dependencies\Resolution\BuildService;
use Avax\Container\DependencyInjection\Dependencies\Resolution\CompiledRuntime;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ResolutionPolicy;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ResolveDependencies;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ServiceResolver;
use Avax\Container\DependencyInjection\Injection\Invocation\FunctionCaller;
use Avax\Container\DependencyInjection\Injection\Invocation\ResolveCallArguments;
use Avax\Container\DependencyInjection\Injection\Methods\InjectMethods;
use Avax\Container\DependencyInjection\Injection\Properties\InjectProperties;
use Avax\Container\DependencyInjection\Scopes\ManageScopes;
use Avax\Container\DependencyInjection\Scopes\ScopeStore;
use Avax\Container\Runtime\HotPathInliner;
use Avax\Container\Runtime\ServicePool;

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
        $scopes = new ManageScopes(store: $scopeStore, pool: $servicePool);
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
            debug : $config->debug
        );
        $compiler = new CompileContainer(
            registrations           : $registrations,
            blueprints              : $blueprints,
            cacheDir                : $config->cacheDir,
            cacheVersion            : $config->cacheVersion,
            configHash              : $config->configHash(),
            environment             : $config->environment(),
            compileMode             : $config->compileMode,
            strict                  : $config->strict,
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
                compiler: $compiler,
                inliner : new HotPathInliner,
                metrics : $observability->metrics
            ),
            deferredProviders: new DeferredProviderRegistry,
            diagnosticsMode : $config->diagnosticsMode
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
