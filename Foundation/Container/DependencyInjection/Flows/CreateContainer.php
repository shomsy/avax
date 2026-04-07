<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Flows;

use Avax\Container\Compilation\CompileContainer;
use Avax\Container\Compilation\ServiceCompiler;
use Avax\Container\Container;
use Avax\Container\ContainerInterface;
use Avax\Container\DependencyInjection\Injection\Invocation\FunctionCaller;
use Avax\Container\DependencyInjection\Injection\Invocation\ResolveCallArguments;
use Avax\Container\Configuration\ContainerSettings;
use Avax\Container\Configuration\CreateContainerConfig;
use Avax\Container\Foundation\Time\Clock;
use Avax\Container\DependencyInjection\Injection\Methods\InjectMethods;
use Avax\Container\DependencyInjection\Injection\Properties\InjectProperties;
use Avax\Container\Observability\ResolutionMetrics;
use Avax\Container\Observability\ResolutionTelemetry;
use Avax\Container\Observability\ResolutionTimeline;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ResolutionPolicy;
use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistry;
use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistryInterface;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\BlueprintCache;
use Avax\Container\DependencyInjection\Dependencies\Resolution\BuildService;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\CreateServiceBlueprint;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ResolveDependencies;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ServiceResolver;
use Avax\Container\DependencyInjection\Scopes\ManageScopes;
use Avax\Container\DependencyInjection\Scopes\ScopeInterface;
use Avax\Container\DependencyInjection\Scopes\ScopeStore;
use Avax\Container\Runtime\HotPathInliner;
use Avax\Container\Runtime\ServicePool;
use Psr\Container\ContainerInterface as PsrContainerInterface;

final class CreateContainer
{
    /**
     * @param array<string, mixed> $settings
     */
    public function create(
        string $cacheDir = '',
        bool $debug = false,
        array $settings = [],
        CreateContainerConfig|null $config = null
    ) : Container {
        $config ??= new CreateContainerConfig(
            cacheDir: $cacheDir,
            debug   : $debug,
            settings: $settings
        );

        $registrations = new ServiceRegistry;
        $scopeStore    = new ScopeStore;
        $servicePool   = new ServicePool;
        $scopes        = new ManageScopes(store: $scopeStore, pool: $servicePool);
        $clock         = new Clock;
        $metrics       = new ResolutionMetrics;
        $timeline      = new ResolutionTimeline(clock: $clock);
        $dependencies  = new ResolveDependencies;
        $blueprints    = new CreateServiceBlueprint(
            cache       : new BlueprintCache(
                cacheDir    : $config->cacheDir,
                cacheVersion: $config->cacheVersion,
                debug       : $config->debug,
                metrics     : $metrics
            ),
            dependencies: $dependencies
        );
        $callArguments = new ResolveCallArguments(dependencies: $dependencies);
        $caller        = new FunctionCaller(arguments: $callArguments);
        $policy        = new ResolutionPolicy(
            strict: $config->strict,
            debug : $config->debug
        );
        $compiler      = new CompileContainer(
            registrations: $registrations,
            blueprints   : $blueprints,
            cacheDir     : $config->cacheDir,
            cacheVersion : $config->cacheVersion,
            metrics      : $metrics,
            services     : new ServiceCompiler(
                registrations: $registrations,
                blueprints   : $blueprints
            )
        );
        $resolver      = new ServiceResolver(
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
            metrics         : $metrics,
            timeline        : $timeline,
            policy          : $policy,
            compiler        : $compiler,
            inliner         : new HotPathInliner
        );
        $telemetry = $resolver->telemetry();

        $container = new Container(resolver: $resolver);
        $resolver->setContainer(container: $container);

        $this->seedSystemServices(
            resolver      : $resolver,
            container     : $container,
            scopes        : $scopes,
            scopeStore    : $scopeStore,
            servicePool   : $servicePool,
            registrations : $registrations,
            settings      : new ContainerSettings(items: $config->settings),
            config        : $config,
            policy        : $policy,
            clock         : $clock,
            metrics       : $metrics,
            timeline      : $timeline,
            telemetry     : $telemetry,
            caller        : $caller
        );

        return $container;
    }

    private function seedSystemServices(
        ServiceResolver $resolver,
        Container $container,
        ManageScopes $scopes,
        ScopeStore $scopeStore,
        ServicePool $servicePool,
        ServiceRegistry $registrations,
        ContainerSettings $settings,
        CreateContainerConfig $config,
        ResolutionPolicy $policy,
        Clock $clock,
        ResolutionMetrics $metrics,
        ResolutionTimeline $timeline,
        ResolutionTelemetry $telemetry,
        FunctionCaller $caller
    ) : void {
        $registrations->bootstrapInstance(abstract: PsrContainerInterface::class, instance: $container);
        $registrations->bootstrapInstance(abstract: ContainerInterface::class, instance: $container);
        $registrations->bootstrapInstance(abstract: Container::class, instance: $container);
        $registrations->bootstrapInstance(abstract: ServiceResolver::class, instance: $resolver);
        $registrations->bootstrapInstance(abstract: ServiceRegistryInterface::class, instance: $registrations);
        $registrations->bootstrapInstance(abstract: ScopeInterface::class, instance: $scopes);
        $registrations->bootstrapInstance(abstract: ManageScopes::class, instance: $scopes);
        $registrations->bootstrapInstance(abstract: ScopeStore::class, instance: $scopeStore);
        $registrations->bootstrapInstance(abstract: ServicePool::class, instance: $servicePool);
        $registrations->bootstrapInstance(abstract: ServiceRegistry::class, instance: $registrations);
        $registrations->bootstrapInstance(abstract: CreateContainerConfig::class, instance: $config);
        $registrations->bootstrapInstance(abstract: ContainerSettings::class, instance: $settings);
        $registrations->bootstrapInstance(abstract: ResolutionPolicy::class, instance: $policy);
        $registrations->bootstrapInstance(abstract: Clock::class, instance: $clock);
        $registrations->bootstrapInstance(abstract: ResolutionMetrics::class, instance: $metrics);
        $registrations->bootstrapInstance(abstract: ResolutionTimeline::class, instance: $timeline);
        $registrations->bootstrapInstance(abstract: ResolutionTelemetry::class, instance: $telemetry);
        $registrations->bootstrapInstance(abstract: FunctionCaller::class, instance: $caller);
    }
}
