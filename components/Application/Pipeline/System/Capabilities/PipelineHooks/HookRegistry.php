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

    public function add(string $name, Closure $handler): void
    {
        if (! isset($this->hooks[$name])) {
            $this->hooks[$name] = [];
        }

        $this->hooks[$name][] = $handler;
    }

    public function execute(string $hook, mixed $data = null): mixed
    {
        $handlers = $this->hooks[$hook] ?? [];
        $result = $data;

        foreach ($handlers as $handler) {
            $result = $handler($result);
        }

        return $result;
    }

    public function has(string $hook): bool
    {
        return isset($this->hooks[$hook]) && $this->hooks[$hook] !== [];
    }

    public function count(string $hook): int
    {
        return count($this->hooks[$hook] ?? []);
    }

    /**
     * @return array<string, list<Closure>>
     */
    public function all(): array
    {
        return $this->hooks;
    }
}
