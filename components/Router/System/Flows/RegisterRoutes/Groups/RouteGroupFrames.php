<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\Flows\RegisterRoutes\Groups;

/**
 * Instance-based stack for managing route group contexts.
 */
final class RouteGroupFrames
{
    private array $stack = [];

    public function push(RouteGroupContext $group) : void
    {
        $this->stack[] = $group;
    }

    public function pop() : void
    {
        array_pop($this->stack);
    }

    public function current() : RouteGroupContext|null
    {
        return end($this->stack) ?: null;
    }

    public function isEmpty() : bool
    {
        return empty($this->stack);
    }

    public function snapshot() : array
    {
        return $this->stack;
    }

    public function restore(array $stack) : void
    {
        $this->stack = $stack;
    }

    public function clear() : void
    {
        $this->stack = [];
    }
}
