<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Registrations;

/**
 * Shared registration contract for the container write side.
 */
interface ServiceRegistryInterface
{
    public function bind(string $abstract, mixed $concrete = null) : ServiceRegistration;

    public function singleton(string $abstract, mixed $concrete = null) : ServiceRegistration;

    public function scoped(string $abstract, mixed $concrete = null) : ServiceRegistration;

    public function instance(string $abstract, object $instance) : void;

    public function extend(string $abstract, callable $closure) : void;

    public function when(string $consumer) : RegisterForTarget;

    public function tag(string|array $abstracts, string|array $tags) : void;
}
