<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Registrations;

use Avax\Container\DependencyInjection\Scopes\Lifetimes\ScopedLifetime;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\SharedLifetime;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\TransientLifetime;
use Closure;

/**
 * Store service registrations, tags, extenders, and target-specific overrides.
 */
final class ServiceRegistry implements ServiceRegistryInterface
{
    /** @var array<string, ServiceRegistration> */
    private array $services = [];

    /** @var array<string, array<string, mixed>> */
    private array $contextual = [];

    /** @var array<string, array<string, mixed>> */
    private array $wildcardContextual = [];

    /** @var array<string, mixed> */
    private array $resolvedCache = [];

    /** @var array<string, array{parents: string[], interfaces: string[]}> */
    private array $classHierarchyCache = [];

    /** @var array<string, list<Closure>> */
    private array $extenders = [];

    public function bind(string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->register(abstract: $abstract, concrete: $concrete, lifetime: TransientLifetime::NAME);
    }

    public function singleton(string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->register(abstract: $abstract, concrete: $concrete, lifetime: SharedLifetime::NAME);
    }

    public function scoped(string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->register(abstract: $abstract, concrete: $concrete, lifetime: ScopedLifetime::NAME);
    }

    public function instance(string $abstract, object $instance) : void
    {
        $registration = new ServiceRegistration(abstract: $abstract);
        $registration->concrete = $instance;
        $registration->lifetime = SharedLifetime::NAME;

        $this->add(definition: $registration);
    }

    public function extend(string $abstract, callable $closure) : void
    {
        $this->addExtender(abstract: $abstract, extender: Closure::fromCallable($closure));
    }

    public function when(string $consumer) : RegisterForTarget
    {
        return new RegisterForTarget(registry: $this, consumer: $consumer);
    }

    public function tag(string|array $abstracts, string|array $tags) : void
    {
        foreach ((array) $abstracts as $abstract) {
            $this->addTags(abstract: $abstract, tags: $tags);
        }
    }

    public function add(ServiceRegistration $definition) : void
    {
        $this->services[$definition->abstract] = $definition;
        $this->resolvedCache = [];
    }

    public function has(string $abstract) : bool
    {
        return isset($this->services[$abstract]);
    }

    public function get(string $abstract) : ServiceRegistration|null
    {
        return $this->services[$abstract] ?? null;
    }

    /**
     * @return list<string>
     */
    public function getTaggedIds(string $tag) : array
    {
        $tagged = [];

        foreach ($this->services as $abstract => $service) {
            if (in_array($tag, $service->tags, true)) {
                $tagged[] = $abstract;
            }
        }

        return array_values(array_unique($tagged));
    }

    public function getContextualMatch(string $consumer, string $needs) : mixed
    {
        $cacheKey = $consumer . '@' . $needs;
        if (array_key_exists($cacheKey, $this->resolvedCache)) {
            return $this->resolvedCache[$cacheKey];
        }

        if (isset($this->contextual[$consumer][$needs])) {
            return $this->resolvedCache[$cacheKey] = $this->contextual[$consumer][$needs];
        }

        foreach ($this->wildcardContextual as $pattern => $rules) {
            if (isset($rules[$needs]) && fnmatch($pattern, $consumer)) {
                return $this->resolvedCache[$cacheKey] = $rules[$needs];
            }
        }

        $hierarchy = $this->getClassHierarchy(class: $consumer);
        foreach ($hierarchy['parents'] as $parent) {
            if (isset($this->contextual[$parent][$needs])) {
                return $this->resolvedCache[$cacheKey] = $this->contextual[$parent][$needs];
            }
        }
        foreach ($hierarchy['interfaces'] as $interface) {
            if (isset($this->contextual[$interface][$needs])) {
                return $this->resolvedCache[$cacheKey] = $this->contextual[$interface][$needs];
            }
        }

        return $this->resolvedCache[$cacheKey] = null;
    }

    public function addContextual(string $consumer, string $needs, mixed $give) : void
    {
        if (str_contains($consumer, '*')) {
            $this->wildcardContextual[$consumer][$needs] = $give;
        } else {
            $this->contextual[$consumer][$needs] = $give;
        }

        $this->resolvedCache = [];
    }

    public function addExtender(string $abstract, Closure $extender) : void
    {
        $this->extenders[$abstract][] = $extender;
    }

    /**
     * @return list<Closure>
     */
    public function getExtenders(string $abstract) : array
    {
        return $this->extenders[$abstract] ?? [];
    }

    public function addTags(string $abstract, string|array $tags) : void
    {
        if (! isset($this->services[$abstract])) {
            return;
        }

        $this->services[$abstract]->tag(tags: $tags);
    }

    /**
     * @return array<string, ServiceRegistration>
     */
    public function getAllDefinitions() : array
    {
        return $this->services;
    }

    /**
     * @return array<string, ServiceRegistration>
     */
    public function all() : array
    {
        return $this->services;
    }

    /**
     * @return array{parents: string[], interfaces: string[]}
     */
    private function getClassHierarchy(string $class) : array
    {
        if (isset($this->classHierarchyCache[$class])) {
            return $this->classHierarchyCache[$class];
        }

        return $this->classHierarchyCache[$class] = [
            'parents' => class_exists($class) ? array_values(class_parents($class)) : [],
            'interfaces' => class_exists($class) || interface_exists($class)
                ? array_values(class_implements($class))
                : [],
        ];
    }

    private function register(string $abstract, mixed $concrete, string $lifetime) : ServiceRegistration
    {
        $registration = $this->services[$abstract] ?? new ServiceRegistration(abstract: $abstract);
        $registration->concrete = $concrete ?? $abstract;
        $registration->lifetime = $lifetime;

        $this->add(definition: $registration);

        return $registration;
    }
}
