<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Flows;

use Avax\Container\DependencyInjection\Dependencies\Bindings\RegisterForTarget;
use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistration;
use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistry;
use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistryInterface;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ServiceResolver;

/**
 * Public write-side flow for registering bindings into the container.
 */
final readonly class RegisterServices implements ServiceRegistryInterface
{
    public function __construct(
        private ServiceResolver $resolver
    ) {}

    public function alias(string $alias, string $abstract) : void
    {
        $this->registrations()->alias(alias: $alias, abstract: $abstract);
    }

    public function bind(string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->registrations()->bind(abstract: $abstract, concrete: $concrete);
    }

    public function singleton(string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->registrations()->singleton(abstract: $abstract, concrete: $concrete);
    }

    public function scoped(string $abstract, mixed $concrete = null) : ServiceRegistration
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

    public function decorate(string $abstract, callable|object|string $decorator) : void
    {
        $this->registrations()->decorate(abstract: $abstract, decorator: $decorator);
    }

    public function tag(string|array $abstracts, string|array $tags) : void
    {
        $this->registrations()->tag(abstracts: $abstracts, tags: $tags);
    }

    public function instance(string $abstract, object $instance) : void
    {
        $this->resolver->instance(abstract: $abstract, instance: $instance);
    }

    private function registrations() : ServiceRegistry
    {
        return $this->resolver->registrations();
    }
}
