<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Scopes;

/**
 * Stable public contract for scope lifecycle control.
 */
interface ScopeInterface
{
    public function has(string $abstract) : bool;

    public function get(string $abstract) : mixed;

    public function set(string $abstract, mixed $instance) : void;

    public function instance(string $abstract, mixed $instance) : void;

    public function withinScope(callable $callback, string $kind = ScopeKind::OPERATION, string $scopeId = '') : mixed;

    public function openScope(string $kind = ScopeKind::OPERATION, string $scopeId = '') : void;

    public function closeScope(string|null $kind = null) : void;

    public function terminate() : void;
}
