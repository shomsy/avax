<?php

declare(strict_types=1);

namespace Avax\Container;

/**
 * Stable public contract for scope lifecycle control.
 */
interface ScopeManagerInterface
{
    public function has(string $abstract) : bool;

    public function get(string $abstract) : mixed;

    public function set(string $abstract, mixed $instance) : void;

    public function instance(string $abstract, mixed $instance) : void;

    public function run(callable $callback) : mixed;

    public function beginScope() : void;

    public function endScope() : void;

    public function terminate() : void;
}
