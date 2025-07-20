<?php

declare(strict_types=1);

namespace Gemini\Container\Containers\Registry;

use Gemini\Container\Containers\LifecycleHook;
use Gemini\DataHandling\ArrayHandling\Arrhae;

/**
 * LifecycleHooks
 *
 * A specialized registry for managing lifecycle hooks, leveraging Arrhae for array-like behavior.
 *
 * This class inherits from Arrhae to use its array-like operations and provides functionality
 * to register, trigger, and clear lifecycle hooks. Each lifecycle hook corresponds to a set
 * of callbacks that are executed when the hook is triggered.
 */
class LifecycleHooks extends Arrhae
{
    /**
     * Initialize the registry with keys for each lifecycle hook.
     *
     * The constructor ensures that every possible lifecycle hook is
     * initialized with an empty array to avoid key existence checks later.
     */
    public function __construct()
    {
        parent::__construct();
        // Ensure every LifecycleHook is initialized with an empty array
        foreach (LifecycleHook::cases() as $hook) {
            $this[$hook->value] = [];
        }
    }

    /**
     * Register a callback for a specific lifecycle hook.
     *
     * The registered callback will be executed when the corresponding
     * lifecycle hook is triggered.
     *
     * @param LifecycleHook $lifecycleHook The lifecycle hook to register the callback for.
     * @param callable      $callback The callback function to be executed.
     */
    public function register(LifecycleHook $lifecycleHook, callable $callback) : void
    {
        $this[$lifecycleHook->value][] = $callback;
    }

    /**
     * Trigger a lifecycle hook.
     *
     * Executes all registered callbacks for the specified hook. This method
     * passes any provided arguments to the callback functions.
     *
     * @param LifecycleHook $lifecycleHook The lifecycle hook to trigger.
     * @param mixed         ...$args Arguments passed to the callbacks.
     */
    public function trigger(LifecycleHook $lifecycleHook, mixed ...$args) : void
    {
        $callbacks = $this[$lifecycleHook->value] ?? [];
        foreach ($callbacks as $callback) {
            $callback(...$args);
        }
    }

    /**
     * Clear all callbacks for a specific lifecycle hook.
     *
     * This method removes all registered callbacks for the given lifecycle hook,
     * effectively resetting it.
     *
     * @param LifecycleHook $lifecycleHook The lifecycle hook to clear callbacks for.
     */
    public function clear(LifecycleHook $lifecycleHook) : void
    {
        $this[$lifecycleHook->value] = [];
    }
}