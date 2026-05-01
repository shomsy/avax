<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Flows\RegisterDependencies;

use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DecoratorInterface;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistration;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistry;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistryContract;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\RegisterForTarget;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency;

/**
 * Public write-side flow for registering bindings into the container.
 */
final readonly class RegisterDependencies implements DependencyRegistryContract
{
    public function __construct(private ResolveDependency $resolveDependency)
    {
    }

    public function alias(string $alias, string $abstract) : void
    {
        $this->registrations()->alias(alias: $alias, abstract: $abstract);
    }

    private function registrations() : DependencyRegistry
    {
        return $this->resolveDependency->registrations();
    }

    public function bind(string $abstract, mixed $concrete = null) : DependencyRegistration
    {
        return $this->registrations()->bind(abstract: $abstract, concrete: $concrete);
    }

    public function defer(string $abstract, mixed $concrete = null) : DependencyRegistration
    {
        return $this->registrations()->defer(abstract: $abstract, concrete: $concrete);
    }

    public function singleton(string $abstract, mixed $concrete = null) : DependencyRegistration
    {
        return $this->registrations()->singleton(abstract: $abstract, concrete: $concrete);
    }

    public function scoped(string $abstract, mixed $concrete = null) : DependencyRegistration
    {
        return $this->registrations()->scoped(abstract: $abstract, concrete: $concrete);
    }

    public function when(string $consumer) : RegisterForTarget
    {
        return $this->registrations()->when(consumer: $consumer);
    }

    public function extend(string $abstract, callable $closure) : void
    {
        $this->registrations()->extend(abstract: $abstract, closure: $closure);
    }

    /**
     * Registers one explicit decorator for one service id.
     */
    public function decorate(string $abstract, callable|DecoratorInterface|string $decorator) : void
    {
        $this->registrations()->decorate(abstract: $abstract, decorator: $decorator);
    }

    public function tag(string|array $abstracts, string|array $tags) : void
    {
        $this->registrations()->tag(abstracts: $abstracts, tags: $tags);
    }

    public function instance(string $abstract, object $instance) : void
    {
        $this->resolveDependency->instance(abstract: $abstract, instance: $instance);
    }
}
