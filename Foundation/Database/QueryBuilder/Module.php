<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder;

use Avax\Container\Containers\DependencyInjector as Container;
use Avax\Database\Connection\Contracts\DatabaseConnection;
use Avax\Database\Identity\IdentityMap;
use Avax\Database\Lifecycle\LifecycleInterface;
use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;
use Avax\Database\QueryBuilder\Core\Executor\PDOExecutor;
use Avax\Database\QueryBuilder\Core\Grammar\MySQLGrammar;
use Avax\Database\Transaction\Contracts\TransactionManagerInterface;

/**
 * Functional module responsible for the domain-fluent QueryBuilder feature.
 *
 * -- intent: coordinate the registration of SQL grammars, executors, and builder recipes.
 */
final class Module implements LifecycleInterface
{
    /**
     * Constructor promoting the foundation container via PHP 8.3 features.
     *
     * -- intent: link the module to the central dependency injection system.
     *
     * @param Container $container The active DI vessel
     */
    public function __construct(
        private readonly Container $container
    ) {}

    public static function declare() : array
    {
        return [
            'name'  => 'queryBuilder',
            'class' => self::class
        ];
    }

    /**
     * Register QueryBuilder services into the foundation container.
     *
     * -- intent: define the resolution recipes for compilers, executors, and the builder.
     *
     * @return void
     */
    public function register() : void
    {
        $this->container->singleton(abstract: QueryBuilder::class, concrete: function ($c) {
            return new QueryBuilder(
                grammar           : new MySQLGrammar(),
                executor          : new PDOExecutor(connection: $c->get(id: DatabaseConnection::class)),
                transactionManager: $c->get(id: TransactionManagerInterface::class),
                identityMap       : $c->get(id: IdentityMap::class)
            );
        });
    }

    /**
     * Perform initialization logic for the query builder feature.
     *
     * -- intent: ensure the feature is ready for use after registration.
     *
     * @return void
     */
    public function boot() : void
    {
        // No additional boot logic required for query builder
    }

    /**
     * Gracefully terminate the query builder feature resources.
     *
     * -- intent: signal the end of the query builder's availability.
     *
     * @return void
     */
    public function shutdown() : void
    {
        // Shutdown logic if required
    }
}
