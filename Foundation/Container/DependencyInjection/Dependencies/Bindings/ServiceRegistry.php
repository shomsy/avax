<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Dependencies\Bindings;

use Avax\Container\DependencyInjection\Scopes\Lifetimes\ScopedLifetime;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\SharedLifetime;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\TransientLifetime;
use Closure;
use LogicException;

/**
 * Store service registrations, tags, extenders, and target-specific overrides.
 */
final class ServiceRegistry implements ServiceRegistryInterface
{
    /** @var array<string, ServiceRegistration> */
    private array $services = [];

    /** @var array<string, string> */
    private array $aliases = [];

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

    private int $revision = 0;

    public function alias(string $alias, string $abstract) : void
    {
        if ($alias === '' || $abstract === '') {
            return;
        }

        if ($alias === $abstract || $this->aliasChainContains(alias: $alias, target: $abstract)) {
            throw new LogicException(message: "Alias cycle detected for [{$alias}] -> [{$abstract}].");
        }

        $target = $this->resolveAlias(abstract: $abstract);
        if ($alias === $target) {
            return;
        }

        $this->aliases[$alias] = $target;
        $this->touch();
    }

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

    public function decorate(string $abstract, callable|object|string $decorator) : void
    {
        if (is_callable($decorator)) {
            $this->extend(abstract: $abstract, closure: $decorator(...));
            return;
        }

        $this->addExtender(
            abstract: $abstract,
            extender: static function (mixed $instance, mixed $container = null) use ($decorator) : mixed {
                $resolved = $decorator;

                if (is_string($resolved) && class_exists($resolved)) {
                    $resolved = $container?->make($resolved, ['inner' => $instance, 'decorated' => $instance])
                        ?? new $resolved($instance);
                }

                if (is_object($resolved) && method_exists($resolved, 'decorate')) {
                    return $resolved->decorate($instance, $container);
                }

                if (is_callable($resolved)) {
                    return $resolved($instance, $container);
                }

                return $instance;
            }
        );
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
        $this->touch();
    }

    public function has(string $abstract) : bool
    {
        return isset($this->services[$this->resolveAlias(abstract: $abstract)]);
    }

    public function get(string $abstract) : ServiceRegistration|null
    {
        return $this->services[$this->resolveAlias(abstract: $abstract)] ?? null;
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
        $needs = $this->resolveAlias(abstract: $needs);
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
        $needs = $this->resolveAlias(abstract: $needs);

        if (str_contains($consumer, '*')) {
            $this->wildcardContextual[$consumer][$needs] = $give;
        } else {
            $this->contextual[$consumer][$needs] = $give;
        }

        $this->touch();
    }

    public function addExtender(string $abstract, Closure $extender) : void
    {
        $this->extenders[$this->resolveAlias(abstract: $abstract)][] = $extender;
        $this->touch();
    }

    /**
     * @return list<Closure>
     */
    public function getExtenders(string $abstract) : array
    {
        return $this->extenders[$this->resolveAlias(abstract: $abstract)] ?? [];
    }

    public function addTags(string $abstract, string|array $tags) : void
    {
        $abstract = $this->resolveAlias(abstract: $abstract);

        if (! isset($this->services[$abstract])) {
            return;
        }

        $this->services[$abstract]->tag(tags: $tags);
        $this->touch();
    }

    /**
     * @return array<string, ServiceRegistration>
     */
    public function all() : array
    {
        return $this->services;
    }

    /**
     * @return array<string, string>
     */
    public function allAliases() : array
    {
        return $this->aliases;
    }

    public function hasExtenders(string $abstract) : bool
    {
        return ($this->extenders[$this->resolveAlias(abstract: $abstract)] ?? []) !== [];
    }

    public function revision() : int
    {
        return $this->revision;
    }

    public function resolveAlias(string $abstract) : string
    {
        $seen = [];
        $current = $abstract;

        while (isset($this->aliases[$current])) {
            if (isset($seen[$current])) {
                break;
            }

            $seen[$current] = true;
            $current = $this->aliases[$current];
        }

        return $current;
    }

    public function flush() : void
    {
        $this->services = [];
        $this->aliases = [];
        $this->contextual = [];
        $this->wildcardContextual = [];
        $this->resolvedCache = [];
        $this->classHierarchyCache = [];
        $this->extenders = [];
        $this->touch();
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

    private function touch() : void
    {
        $this->resolvedCache = [];
        $this->revision++;
    }

    private function aliasChainContains(string $alias, string $target) : bool
    {
        $seen = [];
        $current = $target;

        while (isset($this->aliases[$current])) {
            if ($current === $alias || isset($seen[$current])) {
                return true;
            }

            $seen[$current] = true;
            $current = $this->aliases[$current];
        }

        return $current === $alias;
    }
}
