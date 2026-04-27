<?php

declare(strict_types=1);

namespace Avax\Components\Container\System\PublicSurface;

use components\Container\DI\Container as RealContainer;
use components\Container\DI\ContainerInterface as RealContainerInterface;

/**
 * Public surface for the Container component.
 *
 * Delegates ALL logic to the real DI Container (Container/DI/Container.php).
 * The real container has: scopes, compilation, lazy proxies, decorators,
 * context containers, debug/governance tools, graph exports, and more.
 *
 * This class exists solely to provide the canonical Screaming Architecture
 * namespace. All power comes from the real DI engine.
 */
final class Container implements ContainerInterface
{
    public function __construct(
        private readonly RealContainerInterface $engine,
    ) {}

    /**
     * Create a Container wrapping the real DI engine.
     */
    public static function fromEngine(RealContainerInterface $engine) : self
    {
        return new self($engine);
    }

    // --- Core resolution ---

    public function get(string $id) : mixed
    {
        return $this->engine->get($id);
    }

    public function has(string $id) : bool
    {
        return $this->engine->has($id);
    }

    public function make(string $abstract, array $parameters = []) : object
    {
        return $this->engine->make($abstract, $parameters);
    }

    public function call(callable|string $callable, array $parameters = []) : mixed
    {
        return $this->engine->call($callable, $parameters);
    }

    // --- Registration ---

    public function bind(string $abstract, mixed $concrete = null) : mixed
    {
        return $this->engine->bind($abstract, $concrete);
    }

    public function singleton(string $abstract, mixed $concrete = null) : mixed
    {
        return $this->engine->singleton($abstract, $concrete);
    }

    public function scoped(string $abstract, mixed $concrete = null) : mixed
    {
        return $this->engine->scoped($abstract, $concrete);
    }

    public function instance(string $abstract, object $instance) : void
    {
        $this->engine->instance($abstract, $instance);
    }

    public function alias(string $alias, string $abstract) : void
    {
        $this->engine->alias($alias, $abstract);
    }

    // --- Tags ---

    public function tag(string|array $abstracts, string|array $tags) : void
    {
        $this->engine->tag($abstracts, $tags);
    }

    public function tagged(string $tag) : array
    {
        return $this->engine->tagged($tag);
    }

    // --- Providers ---

    public function bootProviders(array $providers) : void
    {
        $this->engine->bootProviders($providers);
    }

    // --- Scopes ---

    public function openScope(string $kind = 'operation', string $scopeId = '') : void
    {
        $this->engine->openScope($kind, $scopeId);
    }

    public function closeScope(string|null $kind = null) : void
    {
        $this->engine->closeScope($kind);
    }

    // --- Lifecycle ---

    public function flush() : void
    {
        $this->engine->flush();
    }

    public function reset() : void
    {
        $this->engine->reset();
    }

    // --- Advanced (compilation, debug, governance) ---

    public function compileContainer(array $serviceIds = []) : void
    {
        $this->engine->compileContainer($serviceIds);
    }

    public function exportGraph(string $format = 'json', string $kind = 'dependency', string $id = '') : string
    {
        return $this->engine->exportGraph($format, $kind, $id);
    }

    public function validate(array $serviceIds = []) : array
    {
        return $this->engine->validate($serviceIds);
    }

    /**
     * Access the underlying real DI engine for advanced operations.
     */
    public function engine() : RealContainerInterface
    {
        return $this->engine;
    }
}