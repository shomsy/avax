<?php

declare(strict_types=1);

namespace Avax\Database\Lifecycle;

/**
 * The "Life Rules" (Lifecycle Contract) for database features.
 *
 * -- what is it?
 * This is an Interface (a contract). It defines the three most important
 * moments in every database feature's life: Birth, Work, and Death.
 *
 * -- how to imagine it:
 * Think of the "Daily Schedule" for a worker. The schedule says:
 * 1. Morning (`register`): Get your tools ready and put them in your locker.
 * 2. Day (`boot`): Start your tasks and talk to your coworkers.
 * 3. Evening (`shutdown`): Turn off the machines, clean up your desk, and
 *    go home.
 *
 * -- why this exists:
 * So that the `Kernel` (the boss) can manage every database feature (like
 * "Transactions" or "Query Builder") exactly the same way. The boss
 * doesn't need to know what a worker does, only that they follows this
 * three-step daily schedule. This ensures the system starts up and shuts
 * down perfectly every single time.
 */
interface LifecycleInterface
{
    /**
     * The "Birth" phase. Get your tools ready.
     *
     * -- intent:
     * This is where a module tells the system what "Service Recipes" it has.
     * It adds its tools to the toolbox but doesn't start using them yet.
     */
    public function register() : void;

    /**
     * The "Working" phase. Start your engines.
     *
     * -- intent:
     * This is where the module actually "Wakes up". It can now talk to
     * other modules and start performing its duties.
     */
    public function boot() : void;

    /**
     * The "Cleanup" phase. Turn off the lights.
     *
     * -- intent:
     * This is the final step. The module must close any open files or
     * database connections and make sure everything is clean before
     * it is destroyed.
     */
    public function shutdown() : void;
}
