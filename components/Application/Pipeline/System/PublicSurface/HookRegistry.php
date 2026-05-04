<?php

declare(strict_types=1);

namespace Avax\Components\Application\Pipeline\System\PublicSurface;

use Closure;

final class HookRegistry
{
    /** @var array<string, list<Closure>> */
    private array $hooks = [];

    public function add(string $name, Closure $handler): void
    {
        if (!isset($this->hooks[$name])) {
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

    public function all(): array
    {
        return $this->hooks;
    }
}
