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

    /** @var array<string, ServiceRegistration> */
    private array $systemServices = [];

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

    /** @var array<string, list<string>> */
    private array $decorationDescriptors = [];

    private int $revision = 0;

    /**
     * @throws LogicException
     */
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

    public function defer(string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->register(
            abstract: $abstract,
            concrete: $concrete,
            lifetime : TransientLifetime::NAME,
            deferred : true
        );
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

    public function bootstrapInstance(string $abstract, object $instance) : void
    {
        $registration = new ServiceRegistration(abstract: $abstract);
        $registration->concrete = $instance;
        $registration->lifetime = SharedLifetime::NAME;

        $this->addSystem(definition: $registration);
    }

    public function bootstrap(ServiceRegistration $definition) : void
    {
        $this->addSystem(definition: $definition);
    }

    public function extend(string $abstract, callable $closure) : void
    {
        $this->addExtender(
            abstract  : $abstract,
            extender  : Closure::fromCallable($closure),
            descriptor: 'extender'
        );
    }

    /**
     * Registers one explicit decorator chain step.
     *
     * @throws LogicException
     */
    public function decorate(string $abstract, callable|DecoratorInterface|string $decorator) : void
    {
        if (is_callable($decorator)) {
            $this->addExtender(
                abstract  : $abstract,
                extender  : Closure::fromCallable($decorator),
                descriptor: $this->describeDecorator(decorator: $decorator)
            );
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

                if ($resolved instanceof DecoratorInterface) {
                    return $resolved->decorate($instance, $container);
                }

                if (is_callable($resolved)) {
                    return $resolved($instance, $container);
                }

                throw new LogicException(message: 'Decorator must be callable, implement DecoratorInterface, or resolve to one of them.');
            },
            descriptor: $this->describeDecorator(decorator: $decorator)
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
        $abstract = $this->resolveAlias(abstract: $abstract);

        return isset($this->services[$abstract]) || isset($this->systemServices[$abstract]);
    }

    public function get(string $abstract) : ServiceRegistration|null
    {
        $abstract = $this->resolveAlias(abstract: $abstract);

        return $this->services[$abstract] ?? $this->systemServices[$abstract] ?? null;
    }

    /**
     * @return list<string>
     */
    public function getTaggedIds(string $tag) : array
    {
        $tagged = [];

        foreach ($this->all() as $abstract => $service) {
            if (in_array($tag, $service->tags, true)) {
                $tagged[] = $abstract;
            }
        }

        $tagged = array_values(array_unique($tagged));
        sort($tagged);

        return $tagged;
    }

    /**
     * Returns the matching target-specific binding for one consumer and need.
     */
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

    /**
     * Adds one target-specific binding rule.
     */
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

    /**
     * Adds one extender to the decoration chain.
     */
    public function addExtender(string $abstract, Closure $extender, string $descriptor = 'extender') : void
    {
        $resolved = $this->resolveAlias(abstract: $abstract);
        $this->extenders[$resolved][] = $extender;
        $this->decorationDescriptors[$resolved][] = $descriptor;
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
     * @return array<string, ServiceRegistration>
     */
    public function allIncludingSystem() : array
    {
        return $this->services + $this->systemServices;
    }

    /**
     * @return array<string, string>
     */
    public function allAliases() : array
    {
        $aliases = $this->aliases;
        ksort($aliases);

        return $aliases;
    }

    public function hasAlias(string $alias) : bool
    {
        return isset($this->aliases[$alias]);
    }

    public function hasExtenders(string $abstract) : bool
    {
        return ($this->extenders[$this->resolveAlias(abstract: $abstract)] ?? []) !== [];
    }

    /**
     * @return list<string>
     */
    public function aliasChain(string $abstract) : array
    {
        if (! isset($this->aliases[$abstract])) {
            return [];
        }

        $chain = [$abstract];
        $seen = [];
        $current = $abstract;

        while (isset($this->aliases[$current]) && ! isset($seen[$current])) {
            $seen[$current] = true;
            $current = $this->aliases[$current];
            $chain[] = $current;
        }

        return $chain;
    }

    /**
     * @return list<string>
     */
    public function decorationChain(string $abstract) : array
    {
        $resolved = $this->resolveAlias(abstract: $abstract);

        return $this->decorationDescriptors[$resolved] ?? [];
    }

    public function revision() : int
    {
        return $this->revision;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function contextual() : array
    {
        return $this->contextual + $this->wildcardContextual;
    }

    /**
     * @return array<string, list<string>>
     */
    public function tagIndex() : array
    {
        $index = [];

        foreach ($this->all() as $abstract => $registration) {
            foreach ($registration->tags as $tag) {
                $index[$tag][] = $abstract;
            }
        }

        foreach ($index as $tag => $ids) {
            $values = array_values(array_unique($ids));
            sort($values);
            $index[$tag] = $values;
        }

        ksort($index);

        return $index;
    }

    /**
     * @return array<string, string>
     */
    public function lifetimeMap() : array
    {
        $lifetimes = [];

        foreach ($this->all() as $abstract => $registration) {
            $lifetimes[$abstract] = $registration->lifetime;
        }

        ksort($lifetimes);

        return $lifetimes;
    }

    /**
     * @return array<string, bool>
     */
    public function deferredMap() : array
    {
        $deferred = [];

        foreach ($this->all() as $abstract => $registration) {
            $deferred[$abstract] = $registration->deferred;
        }

        ksort($deferred);

        return $deferred;
    }

    /**
     * @return array<string, int>
     */
    public function decorationChains() : array
    {
        $chains = [];

        foreach ($this->extenders as $abstract => $extenders) {
            $chains[$abstract] = count($extenders);
        }

        ksort($chains);

        return $chains;
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
        $this->decorationDescriptors = [];
        $this->touch();
    }

    /**
     * Clears derived lookup caches without mutating canonical registrations.
     */
    public function resetDerivedState() : void
    {
        $this->resolvedCache = [];
        $this->classHierarchyCache = [];
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

    private function register(string $abstract, mixed $concrete, string $lifetime, bool $deferred = false) : ServiceRegistration
    {
        $registration = $this->services[$abstract] ?? new ServiceRegistration(abstract: $abstract);
        $registration->concrete = $concrete ?? $abstract;
        $registration->lifetime = $lifetime;
        $registration->deferred = $deferred;

        $this->add(definition: $registration);

        return $registration;
    }

    private function addSystem(ServiceRegistration $definition) : void
    {
        $this->systemServices[$definition->abstract] = $definition;
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

    private function describeDecorator(callable|object|string $decorator) : string
    {
        if (is_string($decorator) && $decorator !== '') {
            return $decorator;
        }

        if (is_object($decorator) && ! $decorator instanceof Closure) {
            return $decorator::class;
        }

        return 'callable';
    }
}
