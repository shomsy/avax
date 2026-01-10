<?php

declare(strict_types=1);

namespace Avax\Container\Features\Operate\Boot;

use Avax\Cache\CacheManager;
use Avax\Container\Features\Core\ContainerBuilder;
use Avax\Container\Features\Core\Enum\ServiceLifetime;
use Avax\Container\Features\Define\Store\ServiceDefinitionEntity;
use Avax\Container\Features\Define\Store\ServiceDefinitionRepository;
use Avax\Container\Features\Define\Store\ServiceDependencyRepository;
use Avax\Container\Features\Define\Store\ServiceDiscovery;
use Avax\Container\Features\Operate\Config\BootstrapProfile;
use Avax\Container\Features\Operate\Config\ContainerConfig;
use Avax\Container\Features\Operate\Config\TelemetryConfig;
use Avax\Container\Features\Think\Cache\CacheManagerIntegration;
use Avax\Container\Features\Think\Cache\PrototypeCache;
use Avax\Container\Guard\Rules\ServiceValidator;
use Avax\Container\Observe\Metrics\EnhancedMetricsCollector;
use Avax\Container\Observe\Metrics\LoggerFactoryIntegration;
use Avax\Container\Observe\Metrics\MetricsCollector;
use Avax\Container\Container;
use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;
use Avax\Logging\LoggerFactory;
use RuntimeException;
use Throwable;

/**
 * Enterprise-grade container bootstrap orchestrator for comprehensive dependency injection initialization.
 *
 * This sophisticated bootstrapper provides end-to-end orchestration for container initialization
 * in enterprise environments, seamlessly integrating caching, logging, service discovery,
 * validation, telemetry, and database-backed service definitions into a production-ready
 * dependency injection container with comprehensive monitoring and error handling.
 *
 * It delegates the low-level wiring to {@see ContainerBuilder} while managing the high-level
 * application lifecycle steps including profile selection and post-build verification.
 *
 * @package Avax\Container\Operate\Boot
 * @see docs/Features/Operate/Boot/ContainerBootstrap.md#quick-summary
 */
class ContainerBootstrap
{
    /**
     * Initialize the bootstrapper with a profile and optional database backend.
     *
     * @param BootstrapProfile  $profile      Fully resolved bootstrap profile.
     * @param QueryBuilder|null $queryBuilder Optional query builder for repository-backed services.
     *
     * @see docs/Features/Operate/Boot/ContainerBootstrap.md#method-__construct
     */
    public function __construct(
        private BootstrapProfile           $profile,
        private readonly QueryBuilder|null $queryBuilder = null
    ) {}

    /**
     * Create a bootstrapper from a configuration file.
     *
     * @param string            $configPath   Path to the bootstrap configuration file.
     * @param QueryBuilder|null $queryBuilder Optional query builder for repository-backed services.
     *
     * @return self
     * @throws RuntimeException If the configuration file cannot be found.
     * @see docs/Features/Operate/Boot/ContainerBootstrap.md#method-fromconfigfile
     */
    public static function fromConfigFile(string $configPath, QueryBuilder|null $queryBuilder = null): self
    {
        if (! file_exists($configPath)) {
            throw new RuntimeException(message: "Configuration file not found: {$configPath}");
        }

        $config = require $configPath;

        $profile = BootstrapProfile::fromArrays(
            container: $config['container'] ?? [],
            telemetry: $config['telemetry'] ?? []
        );

        return new self(profile: $profile, queryBuilder: $queryBuilder);
    }

    /**
     * Create a bootstrapper using the development profile.
     *
     * @param QueryBuilder|null $queryBuilder Optional query builder for repository-backed services.
     *
     * @return self
     * @see docs/Features/Operate/Boot/ContainerBootstrap.md#method-development
     */
    public static function development(QueryBuilder|null $queryBuilder = null): self
    {
        return new self(profile: BootstrapProfile::development(), queryBuilder: $queryBuilder);
    }

    /**
     * Create a bootstrapper using the production profile.
     *
     * @param QueryBuilder|null $queryBuilder Optional query builder for repository-backed services.
     *
     * @return self
     * @see docs/Features/Operate/Boot/ContainerBootstrap.md#method-production
     */
    public static function production(QueryBuilder|null $queryBuilder = null): self
    {
        return new self(profile: BootstrapProfile::production(), queryBuilder: $queryBuilder);
    }

    /**
     * Bootstrap a complete container with all configured features enabled.
     *
     * This method orchestrates the creation and configuration of the ContainerBuilder,
     * loads definitions, builds the final container, and performs post-build
     * initialization steps like validation and monitoring.
     *
     * @return Container Fully configured immutable container instance.
     * @see docs/Features/Operate/Boot/ContainerBootstrap.md#method-bootstrap
     */
    public function bootstrap(): Container
    {
        // 1. Create and configure Builder
        $builder = ContainerBuilder::create()
            ->withProfile($this->profile);

        // 2. Configure bindings (Aliases, Infra bindings)
        $this->configureBuilder(builder: $builder);

        // 3. Load service definitions (from DB to Builder)
        $this->loadServiceDefinitions(builder: $builder);

        // 4. Build the runtime container
        $container = $builder->build();

        // 5. Post-build initialization (Infrastructure init, Validation, Monitoring)
        $this->initializeInfrastructure($container);
        $this->validateConfiguration(container: $container);
        $this->initializeMonitoring(container: $container);

        return $container;
    }

    /**
     * Initialize core infrastructure components post-build.
     *
     * @param Container $container Container instance.
     * @see docs/Features/Operate/Boot/ContainerBootstrap.md#method-initializeinfrastructure
     */
    private function initializeInfrastructure(Container $container): void
    {
        // Placeholder for future infrastructure initialization (post-build)
    }

    /**
     * Configure core container bindings for configuration, caching, and logging.
     *
     * @param ContainerBuilder $builder Container builder instance.
     * @see docs/Features/Operate/Boot/ContainerBootstrap.md#method-configurebuilder
     */
    private function configureBuilder(ContainerBuilder $builder): void
    {
        // Bind core configuration
        $builder->instance(BootstrapProfile::class, $this->profile);
        $builder->instance(ContainerConfig::class, $this->profile->container);
        $builder->instance(TelemetryConfig::class, $this->profile->telemetry);

        // Bind infrastructure components
        $this->bindInfrastructure(builder: $builder);

        // Set up aliases
        $this->setupAliases(builder: $builder);
    }

    /**
     * Bind infrastructure integrations (cache, logging, repositories).
     *
     * @param ContainerBuilder $builder Container builder instance.
     *
     * @throws RuntimeException When cache or logger factories are missing.
     * @see docs/Features/Operate/Boot/ContainerBootstrap.md#method-bindinfrastructure
     */
    private function bindInfrastructure(ContainerBuilder $builder): void
    {
        // Cache system
        $builder->singleton(CacheManagerIntegration::class, function () {
            return new CacheManagerIntegration(
                cacheManager: $this->profile->container->withCacheAndLogging(
                    // These would be injected in real implementation
                    cacheManager: $this->createCacheManager(),
                    loggerFactory: $this->createLoggerFactory()
                )->cacheManager ?? throw new RuntimeException(message: 'Cache manager not configured'),
                config: $this->profile->container
            );
        });

        // Logging system
        $builder->singleton(LoggerFactoryIntegration::class, function () {
            return new LoggerFactoryIntegration(
                loggerFactory: $this->createLoggerFactory(),
                config: $this->profile->telemetry
            );
        });

        // Data layer
        if ($this->queryBuilder) {
            $qb = $this->queryBuilder;
            $builder->singleton(ServiceDefinitionRepository::class, static function () use ($qb) {
                return new ServiceDefinitionRepository(queryBuilder: $qb);
            });

            $builder->singleton(ServiceDependencyRepository::class, static function () use ($qb) {
                return new ServiceDependencyRepository(queryBuilder: $qb);
            });

            $builder->singleton(ServiceDiscovery::class, static function ($c) {
                return new ServiceDiscovery(repository: $c->get(ServiceDefinitionRepository::class));
            });

            $builder->singleton(ServiceValidator::class, function ($c) {
                return new ServiceValidator(
                    serviceRepo: $c->get(ServiceDefinitionRepository::class),
                    // Would need dependency repo too
                    dependencyRepo: $this->createDependencyRepository()
                );
            });
        }

        // Enhanced metrics
        $builder->singleton(EnhancedMetricsCollector::class, static function ($c) {
            return $c->get(LoggerFactoryIntegration::class)->createMetricsCollector();
        });

        // Prototype cache
        $builder->singleton(PrototypeCache::class, static function ($c) {
            return $c->get(CacheManagerIntegration::class)->createPrototypeCache();
        });
    }

    /**
     * Create a cache manager implementation.
     *
     * @return mixed Cache manager instance or null when unconfigured.
     */
    private function createCacheManager(): mixed
    {
        // Placeholder - would create actual CacheManager instance
        return null;
    }

    /**
     * Create a logger factory implementation.
     *
     * @return mixed Logger factory instance or null when unconfigured.
     */
    private function createLoggerFactory(): mixed
    {
        // Placeholder - would create actual LoggerFactory instance
        return null;
    }

    /**
     * Create a dependency repository implementation.
     *
     * @return mixed Dependency repository or null if database is unavailable.
     */
    private function createDependencyRepository(): mixed
    {
        // Placeholder - would create ServiceDependencyRepository
        $qb = $this->queryBuilder;
        return $qb ? new ServiceDependencyRepository(queryBuilder: $qb) : null;
    }

    /**
     * Set up container aliases for core services.
     *
     * @param ContainerBuilder $builder Container builder instance.
     * @see docs/Features/Operate/Boot/ContainerBootstrap.md#method-setupaliases
     */
    private function setupAliases(ContainerBuilder $builder): void
    {
        $builder->bind('cache', static fn($c) => $c->get(CacheManager::class));
        $builder->bind('logger', static fn($c) => $c->get(LoggerFactory::class));

        // Container components
        $builder->bind('metrics', static fn($c) => $c->get(EnhancedMetricsCollector::class));
        $builder->bind('services', static fn($c) => $c->get(ServiceDefinitionRepository::class));
        $builder->bind('discovery', static fn($c) => $c->get(ServiceDiscovery::class));
        $builder->bind('validator', static fn($c) => $c->get(ServiceValidator::class));
    }

    /**
     * Load service definitions from repository and register them into the container builder.
     *
     * @param ContainerBuilder $builder Container builder instance.
     * @see docs/Features/Operate/Boot/ContainerBootstrap.md#method-loadservicedefinitions
     */
    private function loadServiceDefinitions(ContainerBuilder $builder): void
    {
        if (! $this->queryBuilder) {
            return; // No database, skip
        }

        try {
            $qb = $this->queryBuilder;
            // Manually instantiate repo since container doesn't exist yet
            $serviceRepo = new ServiceDefinitionRepository(queryBuilder: $qb);
            $services    = $serviceRepo->findActiveServices($this->getCurrentEnvironment());

            foreach ($services as $serviceDef) {
                $this->registerService(builder: $builder, service: $serviceDef);
            }
        } catch (Throwable $e) {
            // Log but don't fail bootstrap
            $this->logBootstrapError(message: 'Failed to load service definitions', error: $e);
        }
    }

    /**
     * Resolve the current application environment.
     *
     * @return string|null Environment identifier or null if unset.
     */
    private function getCurrentEnvironment(): string|null
    {
        return getenv('APP_ENV') ?: null;
    }

    /**
     * Register a single service definition into the container builder.
     *
     * @param ContainerBuilder         $builder Container builder instance.
     * @param ServiceDefinitionEntity $service Service definition entity to register.
     *
     * @see docs/Features/Operate/Boot/ContainerBootstrap.md#method-registerservice
     */
    private function registerService(ContainerBuilder $builder, ServiceDefinitionEntity $service): void
    {
        $id    = $service->id;
        $class = $service->class;

        // Register based on lifetime
        match ($service->lifetime) {
            ServiceLifetime::Singleton => $builder->singleton($id, $class),
            ServiceLifetime::Scoped    => $builder->scoped($id, $class),
            default                    => $builder->bind($id, $class)
        };

        // Apply configuration if present
        if (! empty($service->config)) {
            $builder->when($class)->needs('$config')->give($service->config);
        }

        // Register tags
        if (! empty($service->tags)) {
            $builder->tag($id, $service->tags);
        }
    }

    /**
     * Log bootstrap errors when a logger is not yet available.
     *
     * @param string     $message Error message.
     * @param Throwable $error   Error instance.
     */
    private function logBootstrapError(string $message, Throwable $error): void
    {
        // Basic error logging if logger not available yet
        error_log("[BOOTSTRAP ERROR] {$message}: {$error->getMessage()}");
    }

    /**
     * Validate services and report warnings if validation fails.
     *
     * @param Container $container Container instance to validate.
     * @see docs/Features/Operate/Boot/ContainerBootstrap.md#method-validateconfiguration
     */
    private function validateConfiguration(Container $container): void
    {
        if (! $container->has(ServiceValidator::class)) {
            return; // No validator available
        }

        try {
            $validator = $container->get(ServiceValidator::class);
            $summary   = $validator->getValidationSummary();

            if ($summary['invalid_services'] > 0) {
                $this->logBootstrapWarning(
                    message: "Container has {$summary['invalid_services']} invalid services",
                    context: ['summary' => $summary]
                );
            }
        } catch (Throwable $e) {
            $this->logBootstrapError(message: 'Configuration validation failed', error: $e);
        }
    }

    /**
     * Log bootstrap warnings when a logger is not yet available.
     *
     * @param string               $message Warning message.
     * @param array<string, mixed> $context Context payload.
     */
    private function logBootstrapWarning(string $message, array $context = []): void
    {
        // Basic warning logging
        $contextStr = empty($context) ? '' : json_encode($context);
        error_log("[BOOTSTRAP WARNING] {$message} {$contextStr}");
    }

    /**
     * Initialize metrics and telemetry logging after bootstrap.
     *
     * @param Container $container Container instance to instrument.
     * @see docs/Features/Operate/Boot/ContainerBootstrap.md#method-initializemonitoring
     */
    private function initializeMonitoring(Container $container): void
    {
        if (! $this->profile->telemetry->enabled) {
            return;
        }

        try {
            // Log successful bootstrap
            $logger = $container->get(LoggerFactoryIntegration::class);
            $logger->logLifecycleEvent('bootstrap_completed', [
                'environment'     => $this->getCurrentEnvironment(),
                'services_loaded' => $container->has(ServiceDefinitionRepository::class) ?
                    $container->get(ServiceDefinitionRepository::class)->findAll()->count() : 0
            ]);

            // Set up performance monitoring
            $metrics = $container->get(EnhancedMetricsCollector::class);
            $container->instance(MetricsCollector::class, $metrics);
        } catch (Throwable $e) {
            $this->logBootstrapError(message: 'Monitoring initialization failed', error: $e);
        }
    }
}
