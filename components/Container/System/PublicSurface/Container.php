<?php

declare(strict_types=1);

namespace Avax\Components\Container\System\PublicSurface;

use Avax\Components\Container\System\Capabilities\Bindings\BindingRegistry;
use Avax\Components\Container\System\Capabilities\Providers\ProviderRegistry;
use Avax\Components\Container\System\Capabilities\Resolution\ServiceResolver;

/**
 * Public surface for the Container component.
 * Delegating all logic to internal capabilities per Screaming Architecture.
 */
final class Container implements ContainerInterface
{
    private BindingRegistry  $bindings;
    private ServiceResolver  $resolver;
    private ProviderRegistry $providerRegistry;

    public function __construct()
    {
        $this->bindings         = new BindingRegistry();
        $this->resolver         = new ServiceResolver($this->bindings);
        $this->providerRegistry = new ProviderRegistry($this);
    }

    public function make(string $abstract, array $parameters = []) : mixed
    {
        return $this->resolver->resolve($abstract, $parameters);
    }

    public function get(string $id) : mixed
    {
        return $this->resolver->resolve($id);
    }

    public function has(string $id): bool
    {
        return $this->bindings->has($id);
    }

    public function call(callable $callback, array $parameters = []) : mixed
    {
        return $this->resolver->call($callback, $parameters);
    }

    public function bind(string $abstract, mixed $concrete = null, bool $shared = false) : void
    {
        $this->bindings->bind($abstract, $concrete, $shared);
    }

    public function singleton(string $abstract, mixed $concrete = null) : void
    {
        $this->bindings->singleton($abstract, $concrete);
    }

    public function scoped(string $abstract, mixed $concrete = null) : void
    {
        $this->bindings->scoped($abstract, $concrete);
    }

    public function instance(string $abstract, object $instance) : void
    {
        $this->bindings->instance($abstract, $instance);
    }

    public function alias(string $alias, string $abstract) : void
    {
        $this->bindings->alias($alias, $abstract);
    }

    public function tag(string|array $abstracts, string|array $tags) : void
    {
        $this->bindings->tag($abstracts, $tags);
    }

    public function tagged(string $tag) : array
    {
        $abstracts = $this->bindings->tagged($tag);
        $results   = [];

        foreach ($abstracts as $abstract) {
            $results[] = $this->get($abstract);
        }

        return $results;
    }

    public function registerProvider(string $providerClass) : void
    {
        $this->providerRegistry->register($providerClass);
    }

    public function boot() : void
    {
        $this->providerRegistry->boot();
    }

    public function flush() : void
    {
        $this->bindings->clear();
    }

    public function flushScoped() : void
    {
        $this->bindings->clearScoped();
    }
}