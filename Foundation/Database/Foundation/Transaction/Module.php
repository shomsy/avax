<?php

declare(strict_types=1);

namespace Avax\Database\Transaction;

use Avax\Container\Containers\DependencyInjector as Container;
use Avax\Database\Connection\Contracts\DatabaseConnection;
use Avax\Database\Lifecycle\LifecycleInterface;
use Avax\Database\Transaction\Contracts\TransactionManagerInterface;

/**
 * Functional module responsible for the Transaction management feature.
 *
 * -- intent: enable atomic database operations through service registration and orchestration.
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
    public function __construct(private Container $container) {}

    /**
     * Declare the module metadata for self-registration.
     */
    public static function declare() : array
    {
        return [
            'name'  => 'transaction',
            'class' => self::class
        ];
    }

    /**
     * Register Transaction services into the foundation container.
     *
     * -- intent: define the resolution recipes for atomic operation managers.
     *
     * @return void
     */
    public function register() : void
    {
        $this->container->singleton(abstract: TransactionManagerInterface::class, concrete: static function ($c) {
            return new TransactionRunner(connection: $c->get(id: DatabaseConnection::class));
        });
    }

    /**
     * Perform initialization logic for the transaction feature.
     *
     * -- intent: ensure the feature is ready for use after registration.
     *
     * @return void
     */
    public function boot() : void
    {
        // No additional boot logic required for transactions
    }

    /**
     * Gracefully terminate the transaction feature resources.
     *
     * -- intent: ensure no dangling transactions remain before system shutdown.
     *
     * @return void
     */
    public function shutdown() : void
    {
        // Internal clean-up logic if required
    }
}
