<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection;

use Avax\Container\Container;
use Avax\Container\ContainerInterface;
use Avax\Container\DependencyInjection\Calls\FunctionCaller;
use Avax\Container\DependencyInjection\Calls\ResolveCallArguments;
use Avax\Container\DependencyInjection\Configuration\ContainerSettings;
use Avax\Container\DependencyInjection\Configuration\CreateContainerConfig;
use Avax\Container\DependencyInjection\Observability\ResolutionMetrics;
use Avax\Container\DependencyInjection\Observability\ResolutionTelemetry;
use Avax\Container\DependencyInjection\Observability\ResolutionTimeline;
use Avax\Container\DependencyInjection\Policies\ResolutionPolicy;
use Avax\Container\DependencyInjection\Registrations\ServiceRegistry;
use Avax\Container\DependencyInjection\Registrations\ServiceRegistryInterface;
use Avax\Container\DependencyInjection\Resolution\BlueprintCache;
use Avax\Container\DependencyInjection\Resolution\BuildService;
use Avax\Container\DependencyInjection\Resolution\CreateServiceBlueprint;
use Avax\Container\DependencyInjection\Resolution\ResolveDependencies;
use Avax\Container\DependencyInjection\Resolution\ResolvePlan;
use Avax\Container\DependencyInjection\Resolution\ServiceResolver;
use Avax\Container\DependencyInjection\Scopes\ManageScopes;
use Avax\Container\DependencyInjection\Scopes\ScopeInterface;
use Avax\Container\DependencyInjection\Scopes\ScopeStore;
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
        $scopes        = new ManageScopes(store: $scopeStore);
        $metrics       = new ResolutionMetrics;
        $timeline      = new ResolutionTimeline;
        $blueprints    = new CreateServiceBlueprint(cache: new BlueprintCache);
        $dependencies  = new ResolveDependencies;
        $callArguments = new ResolveCallArguments(dependencies: $dependencies);
        $caller        = new FunctionCaller(arguments: $callArguments);
        $policy        = new ResolutionPolicy(
            strict: $config->strict,
            debug : $config->debug
        );
        $resolver      = new ServiceResolver(
            registrations   : $registrations,
            scopes          : $scopes,
            plan            : new ResolvePlan,
            builder         : new BuildService(
                blueprints  : $blueprints,
                dependencies: $dependencies
            ),
            blueprints      : $blueprints,
            injectProperties: new \Avax\Container\DependencyInjection\Injection\InjectProperties,
            injectMethods   : new \Avax\Container\DependencyInjection\Injection\InjectMethods(arguments: $callArguments),
            caller          : $caller,
            metrics         : $metrics,
            timeline        : $timeline,
            policy          : $policy
        );
        $telemetry = $resolver->telemetry();

        $container = new Container(resolver: $resolver);
        $resolver->setContainer(container: $container);

        $this->seedSystemServices(
            resolver      : $resolver,
            container     : $container,
            scopes        : $scopes,
            scopeStore    : $scopeStore,
            registrations : $registrations,
            settings      : new ContainerSettings(items: $config->settings),
            config        : $config,
            policy        : $policy,
            metrics       : $metrics,
            timeline      : $timeline,
            telemetry     : $telemetry,
            caller        : $caller
        );

        return $container;
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function build(
        string $cacheDir = '',
        bool $debug = false,
        array $settings = [],
        CreateContainerConfig|null $config = null
    ) : Container {
        return $this->create(
            cacheDir: $cacheDir,
            debug   : $debug,
            settings: $settings,
            config  : $config
        );
    }

    private function seedSystemServices(
        ServiceResolver $resolver,
        Container $container,
        ManageScopes $scopes,
        ScopeStore $scopeStore,
        ServiceRegistry $registrations,
        ContainerSettings $settings,
        CreateContainerConfig $config,
        ResolutionPolicy $policy,
        ResolutionMetrics $metrics,
        ResolutionTimeline $timeline,
        ResolutionTelemetry $telemetry,
        FunctionCaller $caller
    ) : void {
        $resolver->instance(abstract: PsrContainerInterface::class, instance: $container);
        $resolver->instance(abstract: ContainerInterface::class, instance: $container);
        $resolver->instance(abstract: Container::class, instance: $container);
        $resolver->instance(abstract: ServiceResolver::class, instance: $resolver);
        $resolver->instance(abstract: ServiceRegistryInterface::class, instance: $registrations);
        $resolver->instance(abstract: ScopeInterface::class, instance: $scopes);
        $resolver->instance(abstract: ManageScopes::class, instance: $scopes);
        $resolver->instance(abstract: ScopeStore::class, instance: $scopeStore);
        $resolver->instance(abstract: ServiceRegistry::class, instance: $registrations);
        $resolver->instance(abstract: CreateContainerConfig::class, instance: $config);
        $resolver->instance(abstract: ContainerSettings::class, instance: $settings);
        $resolver->instance(abstract: ResolutionPolicy::class, instance: $policy);
        $resolver->instance(abstract: ResolutionMetrics::class, instance: $metrics);
        $resolver->instance(abstract: ResolutionTimeline::class, instance: $timeline);
        $resolver->instance(abstract: ResolutionTelemetry::class, instance: $telemetry);
        $resolver->instance(abstract: FunctionCaller::class, instance: $caller);
        $resolver->instance(abstract: 'config', instance: $settings);
    }
}
