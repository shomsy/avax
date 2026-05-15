<?php

declare(strict_types=1);

namespace Avax\Components\Application\Pipeline\System\Capabilities\PipelineHooks;

use Closure;

/**
 * HookRegistry — internal mutable registry for pipeline stage hooks.
 *
 * Not a public API entry point. Registered by PipelineServiceProvider
 * and injected into the Pipeline facade during boot.
 */
final class HookRegistry
{
    /** @var array<string, list<Closure>> */
    private array $hooks = [];

    /**
     * Register a handler for a named hook point.
     *
     * @param string $name The hook point name (e.g. beforeRoute, afterController).
     * @param Closure $handler The handler closure to execute when the hook fires.
     */
    public function add(string $name, Closure $handler): void
    {
        if (! isset($this->hooks[$name])) {
            $this->hooks[$name] = [];
        }

        $this->hooks[$name][] = $handler;
    }

    /**
     * Execute all handlers for a given hook point in registration order.
     *
     * @param string $hook The hook point name to execute.
     * @param mixed $data Optional data passed through each handler.
     * @return mixed The final data after all handlers have executed.
     */
    public function execute(string $hook, mixed $data = null): mixed
    {
        $handlers = $this->hooks[$hook] ?? [];
        $result = $data;

        foreach ($handlers as $handler) {
            $result = $handler($result);
        }

        return $result;
    }

    /**
     * Check whether any handlers are registered for a hook point.
     *
     * @param string $hook The hook point name to check.
     * @return bool True if at least one handler is registered.
     */
    public function has(string $hook): bool
    {
        return isset($this->hooks[$hook]) && $this->hooks[$hook] !== [];
    }

    /**
     * Count handlers registered for a hook point.
     *
     * @param string $hook The hook point name to count.
     * @return int The number of handlers registered.
     */
    public function count(string $hook): int
    {
        return count($this->hooks[$hook] ?? []);
    }

    /**
     * Get all registered hooks grouped by hook name.
     *
     * @return array<string, list<Closure>> Map of hook names to handler lists.
     */
    public function all(): array
    {
        return $this->hooks;
    }
}
