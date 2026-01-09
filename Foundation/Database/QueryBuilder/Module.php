<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder;

use Avax\Container\Read\DependencyInjector as Container;
use Avax\Database\Connection\Contracts\DatabaseConnection;
use Avax\Database\Identity\IdentityMap;
use Avax\Database\Lifecycle\LifecycleInterface;
use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;
use Avax\Database\QueryBuilder\Core\Executor\PDOExecutor;
use Avax\Database\QueryBuilder\Core\Grammar\MySQLGrammar;
use Avax\Database\Transaction\Contracts\TransactionManagerInterface;

/**
 * The "Sentence Builder" Feature (QueryBuilder Module).
 *
 * -- what is it?
 * This is the module that adds the `QueryBuilder` functionality to the
 * database system. It's like adding a new "Chapter" or "Skill" to your
 * application's brain.
 *
 * -- how to imagine it:
 * Think of it as an "App-within-an-App". When the database system starts
 * up, this module is invited to the party. It brings along its tools: the
 * "Grammar" (how to speak SQL), the "Executor" (how to run SQL), and the
 * "Identity Map" (how to remember data).
 *
 * -- why this exists:
 * 1. Modular Design: If you don't need a Query Builder (maybe you only
 *    use raw SQL), you could theoretically remove this module without
 *    breaking the rest of the database system.
 * 2. Organization: It centralizes all the "Wiring" (Dependency Injection)
 *    needed to make a QueryBuilder work. You don't have to manually connect
 *    the Grammar to the Executor; the Module does it for you.
 * 3. Standardization: It follows the `LifecycleInterface` rulebook, so
 *    it fits perfectly into the system's `boot()` and `shutdown()` process.
 *
 * -- mental models:
 * - "Wiring": Connecting different electronic components (Grammar, Executor)
 *    so they work together as one device (QueryBuilder).
 * - "Singleton": A "Unique Tool". We only ever want ONE QueryBuilder instance
 *    running to keep things consistent.
 */
final readonly class Module implements LifecycleInterface
{
    /**
     * @param Container $container The "Toolbox" where the feature will store its recipes.
     */
    public function __construct(
        private Container $container
    ) {}

    /**
     * Provide the "ID Card" (Metadata) for this feature.
     *
     * -- intent:
     * This is how the system recognizes this class as a valid Feature Module.
     */
    public static function declare() : array
    {
        return [
            'name'  => 'queryBuilder',
            'class' => self::class
        ];
    }

    /**
     * Set up the recipes for the QueryBuilder tools in the toolbox.
     *
     * -- intent:
     * We tell the system: "Whenever someone asks for a `QueryBuilder`, here
     * is how you build one: connect the MySQL language (Grammar), the
     * PDO engine (Executor), and the Identity Map together."
     *
     * @throws \ReflectionException
     */
    public function register() : void
    {
        $this->container->singleton(abstract: QueryBuilder::class, concrete: static function ($c) {
            return new QueryBuilder(
                grammar           : new MySQLGrammar(),
                executor          : new PDOExecutor(connection: $c->get(id: DatabaseConnection::class)),
                transactionManager: $c->get(id: TransactionManagerInterface::class),
                identityMap       : $c->get(id: IdentityMap::class)
            );
        });
    }

    /**
     * Optional "Wake up" logic.
     */
    public function boot() : void
    {
        // No additional boot logic required for query builder.
    }

    /**
     * Optional "Cleanup" logic.
     */
    public function shutdown() : void
    {
        // Shutdown logic if required.
    }
}
