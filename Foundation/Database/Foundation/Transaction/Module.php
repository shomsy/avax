<?php

declare(strict_types=1);

namespace Avax\Database\Transaction;

use Avax\Container\Read\DependencyInjector as Container;
use Avax\Database\Connection\Contracts\DatabaseConnection;
use Avax\Database\Lifecycle\LifecycleInterface;
use Avax\Database\Transaction\Contracts\TransactionManagerInterface;

/**
 * The "Safety Guard" Feature (Transaction Module).
 *
 * -- what is it?
 * This module adds "Transaction Management" to the database system.
 * Transactions make sure that a group of changes are all saved together
 * (Atomic), or none of them are saved if something goes wrong.
 *
 * -- how to imagine it:
 * Think of it as inviting a "Notary" or a "Safety Supervisor" to the
 * database party. The Supervisor watches over every change you make and
 * won't let the permanent records be updated until they are 100% sure
 * everything is correct.
 *
 * -- why this exists:
 * 1. Data Integrity: It protects your data. If you are transferring money
 *    from Account A to Account B, you need BOTH changes to happen. If the
 *    power goes out halfway through, the Transaction Module "rolls back"
 *    the first change so money isn't lost.
 * 2. Organization: It wires up the `Transaction` class so it can be
 *    easily requested by other parts of the system (like the
 *    `QueryOrchestrator`).
 * 3. Standardization: Like all features, it plugs into the system's
 *    `boot()` and `shutdown()` cycle.
 *
 * -- mental models:
 * - "ACID": The technical set of rules (Atomicity, Consistency, Isolation,
 *    Durability) that this module helps enforce to keep data safe.
 * - "Nesting": The ability to have a transaction "inside" another
 *    transaction (using Savepoints). This module tracks those levels.
 */
final readonly class Module implements LifecycleInterface
{
    /**
     * @param Container $container The "Toolbox" where we store the transaction recipes.
     */
    public function __construct(private Container $container) {}

    /**
     * Provide the "ID Card" (Metadata) for this feature.
     */
    public static function declare() : array
    {
        return [
            'name'  => 'transaction',
            'class' => self::class
        ];
    }

    /**
     * Set up the "Safety Supervisor" (Transaction Manager) in the toolbox.
     *
     * -- intent:
     * We tell the system: "Whenever someone asks for a `TransactionManager`,
     * create a new `Transaction` object and link it to the active
     * database connection."
     */
    public function register() : void
    {
        $this->container->singleton(abstract: TransactionManagerInterface::class, concrete: static function ($c) {
            return Transaction::on(connection: $c->get(id: DatabaseConnection::class));
        });
    }

    /**
     * Optional "Wake up" logic.
     */
    public function boot() : void
    {
        // No additional boot logic required for transactions.
    }

    /**
     * Optional "Cleanup" logic.
     */
    public function shutdown() : void
    {
        // Internal clean-up logic if required.
    }
}
