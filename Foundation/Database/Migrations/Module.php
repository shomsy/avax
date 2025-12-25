<?php

declare(strict_types=1);

namespace Avax\Migrations;

use Avax\Container\Containers\DependencyInjector as Container;
use Avax\Database\Lifecycle\LifecycleInterface;
use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;
use Avax\Migrations\Execution\Repository\MigrationRepository;
use Avax\Migrations\Execution\Runner\MigrationRunner;

/**
 * Functional module responsible for the database Migration feature.
 *
 * -- intent: coordinate the registration of migration repositories and runners.
 */
final readonly class Module implements LifecycleInterface
{
    /**
     * Constructor promoting the foundation container via PHP 8.3 features.
     *
     * -- intent: link the module to the central dependency injection system.
     *
     * @param Container $container The active DI vessel
     */
    public function __construct(
        private Container $container
    ) {}

    public static function declare() : array
    {
        return [
            'name'  => 'migrations',
            'class' => self::class
        ];
    }

    /**
     * Register Migration services into the foundation container.
     *
     * -- intent: define the resolution recipes for migration persistence and execution technicians.
     *
     * @return void
     */
    public function register() : void
    {
        $this->container->singleton(abstract: MigrationRepository::class, concrete: function ($c) {
            return new MigrationRepository(builder: $c->get(id: QueryBuilder::class));
        });

        $this->container->singleton(abstract: MigrationRunner::class, concrete: function ($c) {
            return new MigrationRunner(
                repository: $c->get(id: MigrationRepository::class),
                builder   : $c->get(id: QueryBuilder::class)
            );
        });
    }

    /**
     * Perform initialization logic for the migration feature.
     *
     * -- intent: ensure the feature is ready for use after registration.
     *
     * @return void
     */
    public function boot() : void
    {
        // No additional boot logic required for migrations
    }

    /**
     * Gracefully terminate the migration feature resources.
     *
     * -- intent: signal the end of the migration feature availability.
     *
     * @return void
     */
    public function shutdown() : void
    {
        // Shutdown logic if required
    }
}
