<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings;

use Avax\Components\Application\Container\System\Capabilities\Declaration\Ownership\RegistrationCategory;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Ownership\RegistrationMetadata;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Ownership\RegistrationVisibility;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\Lifetimes\ScopedLifetime;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\Lifetimes\SingletonLifetime;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\Lifetimes\TransientLifetime;
use Closure;
use LogicException;

/**
 * Store service registrations, tags, extenders, and target-specific overrides.
 */
final class DependencyRegistry implements DependencyRegistryContract
{
    /** @var array<string, DependencyRegistration> */
    private array $services = [];

    /** @var array<string, DependencyRegistration> */
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

    /** @var array<string, list<array<string, mixed>>> */
    private array $overrideHistory = [];

    private int $revision = 0;

    /**
     * @throws LogicException
     */
    public function alias(string $alias, string $abstract): void
    {
        if ($alias === '' || $abstract === '') {
            return;
        }

        if ($alias === $abstract || $this->aliasChainContains(alias: $alias, target: $abstract)) {
            throw new LogicException(message: sprintf('Alias cycle detected for [%s] -> [%s].', $alias, $abstract));
        }

        $target = $this->resolveAlias(abstract: $abstract);
        if ($alias === $target) {
            return;
        }

        $this->aliases[$alias] = $target;
        $this->touch();
    }

    private function aliasChainContains(string $alias, string $target): bool
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

    public function resolveAlias(string $abstract): string
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

    private function touch(): void
    {
        $this->resolvedCache = [];
        $this->revision++;
    }

    public function bind(string $abstract, mixed $concrete = null): DependencyRegistration
    {
        return $this->register(abstract: $abstract, concrete: $concrete, lifetime: TransientLifetime::NAME);
    }

    private function register(string $abstract, mixed $concrete, string $lifetime, bool $deferred = false): DependencyRegistration
    {
        $registration = $this->services[$abstract] ?? new DependencyRegistration(abstract: $abstract);
        if (isset($this->services[$abstract])) {
            $this->overrideHistory[$abstract][] = $this->registrationState(registration: $registration);
        }

        $registration->concrete = $concrete ?? $abstract;
        $registration->lifetime = $lifetime;
        $registration->deferred = $deferred;

        $this->add(dependencyRegistration: $registration);

        return $registration;
    }

    /**
     * @return array<string, mixed>
     */
    private function registrationState(DependencyRegistration $dependencyRegistration): array
    {
        return [
            'abstract' => $dependencyRegistration->abstract,
            'concrete' => is_object(value: $dependencyRegistration->concrete)
                ? $dependencyRegistration->concrete::class
                : $dependencyRegistration->concrete,
            'lifetime' => $dependencyRegistration->lifetime,
            'deferred' => $dependencyRegistration->deferred,
            'warm' => $dependencyRegistration->warm,
            'lazy' => $dependencyRegistration->lazy,
            'disposable' => $dependencyRegistration->disposable,
            'poolSize' => $dependencyRegistration->poolSize,
            'poolResetBeforeReuse' => $dependencyRegistration->poolResetBeforeReuse,
            'poolScopeKind' => $dependencyRegistration->poolScopeKind,
            'group' => $dependencyRegistration->group,
            'groupOrder' => $dependencyRegistration->groupOrder,
            'tags' => $dependencyRegistration->tags,
            'metadata' => $dependencyRegistration->metadata->toArray(),
        ];
    }

    public function add(DependencyRegistration $dependencyRegistration): void
    {
        $existing = $this->services[$dependencyRegistration->abstract] ?? null;
        if ($existing instanceof DependencyRegistration && $existing !== $dependencyRegistration) {
            $this->overrideHistory[$dependencyRegistration->abstract][] = $this->registrationState(registration: $existing);
        }

        $this->services[$dependencyRegistration->abstract] = $dependencyRegistration;
        $this->touch();
    }

    public function defer(string $abstract, mixed $concrete = null): DependencyRegistration
    {
        return $this->register(
            abstract: $abstract,
            concrete: $concrete,
            lifetime: TransientLifetime::NAME,
            deferred: true,
        );
    }

    public function singleton(string $abstract, mixed $concrete = null): DependencyRegistration
    {
        return $this->register(abstract: $abstract, concrete: $concrete, lifetime: SingletonLifetime::NAME);
    }

    public function scoped(string $abstract, mixed $concrete = null): DependencyRegistration
    {
        return $this->register(abstract: $abstract, concrete: $concrete, lifetime: ScopedLifetime::NAME);
    }

    public function instance(string $abstract, object $instance): void
    {
        $dependencyRegistration = new DependencyRegistration(abstract: $abstract);
        $dependencyRegistration->concrete = $instance;
        $dependencyRegistration->lifetime = SingletonLifetime::NAME;

        $this->add(dependencyRegistration: $dependencyRegistration);
    }

    public function bootstrapInstance(string $abstract, object $instance): void
    {
        $dependencyRegistration = new DependencyRegistration(abstract: $abstract);
        $dependencyRegistration->concrete = $instance;
        $dependencyRegistration->lifetime = SingletonLifetime::NAME;

        $this->addSystem(definition: $dependencyRegistration);
    }

    private function addSystem(DependencyRegistration $dependencyRegistration): void
    {
        if ($dependencyRegistration->metadata->ownerSlice === 'default') {
            $dependencyRegistration->metadata = $dependencyRegistration->metadata
                ->withOwnerSlice(ownerSlice: 'foundation.system')
                ->withCategory(category: RegistrationCategory::FOUNDATION)
                ->withVisibility(visibility: RegistrationVisibility::INTERNAL)
                ->withReason(reason: 'bootstrapped system service')
                ->withIntent(intent: 'system')
                ->withProvenance(provenance: 'CreateContainer');
        }

        $this->systemServices[$dependencyRegistration->abstract] = $dependencyRegistration;
    }

    public function bootstrap(DependencyRegistration $dependencyRegistration): void
    {
        $this->addSystem(definition: $dependencyRegistration);
    }

    public function extend(string $abstract, callable $closure): void
    {
        $this->addExtender(
            abstract  : $abstract,
            extender  : Closure::fromCallable(callback: $closure),
            descriptor: 'extender',
        );
    }

    /**
     * Adds one extender to the decoration chain.
     */
    public function addExtender(string $abstract, Closure $extender, string $descriptor = 'extender'): void
    {
        $resolved = $this->resolveAlias(abstract: $abstract);
        $this->extenders[$resolved][] = $extender;
        $this->decorationDescriptors[$resolved][] = $descriptor;
        $this->touch();
    }

    /**
     * Registers one explicit decorator chain step.
     *
     * @throws LogicException
     */
    public function decorate(string $abstract, callable|DecoratorInterface|string $decorator): void
    {
        if (is_callable(value: $decorator)) {
            $this->addExtender(
                abstract  : $abstract,
                extender  : Closure::fromCallable(callback: $decorator),
                descriptor: $this->describeDecorator(decorator: $decorator),
            );

            return;
        }

        $this->addExtender(
            abstract  : $abstract,
            extender  : static function (mixed $instance, mixed $container = null) use ($decorator): mixed {
                $resolved = $decorator;

                if (is_string(value: $resolved) && class_exists(class: $resolved)) {
                    $resolved = $container?->make($resolved, ['inner' => $instance, 'decorated' => $instance])
                        ?? new $resolved($instance);
                }

                if ($resolved instanceof DecoratorInterface) {
                    return $resolved->decorate(instance: $instance, container: $container);
                }

                if (is_callable(value: $resolved)) {
                    return $resolved($instance, $container);
                }

                throw new LogicException(message: 'Decorator must be callable, implement DecoratorInterface, or resolve to one of them.');
            },
            descriptor: $this->describeDecorator(decorator: $decorator),
        );
    }

    private function describeDecorator(callable|object|string $decorator): string
    {
        if (is_string(value: $decorator) && $decorator !== '') {
            return $decorator;
        }

        if (is_object(value: $decorator) && ! $decorator instanceof Closure) {
            return $decorator::class;
        }

        return 'callable';
    }

    public function when(string $consumer): RegisterForTarget
    {
        return new RegisterForTarget(consumer: $consumer, registry: $this);
    }

    public function tag(string|array $abstracts, string|array $tags): void
    {
        foreach ((array) $abstracts as $abstract) {
            $this->addTags(abstract: $abstract, tags: $tags);
        }
    }

    public function addTags(string $abstract, string|array $tags): void
    {
        $abstract = $this->resolveAlias(abstract: $abstract);

        if (! isset($this->services[$abstract])) {
            return;
        }

        $this->services[$abstract]->tag(tags: $tags);
        $this->touch();
    }

    public function has(string $abstract): bool
    {
        $abstract = $this->resolveAlias(abstract: $abstract);

        return isset($this->services[$abstract]) || isset($this->systemServices[$abstract]);
    }

    /**
     * @return list<string>
     */
    public function getTaggedIds(string $tag): array
    {
        $tagged = [];

        foreach ($this->all() as $abstract => $dependencyRegistration) {
            if (in_array(needle: $tag, haystack: $dependencyRegistration->tags, strict: true)) {
                $tagged[] = $abstract;
            }
        }

        $tagged = array_values(array: array_unique(array: $tagged));
        sort(array: $tagged);

        return $tagged;
    }

    /**
     * @return array<string, DependencyRegistration>
     */
    public function all(): array
    {
        $services = $this->services;
        ksort(array: $services);

        return $services;
    }

    /**
     * @return list<string>
     */
    public function getGroupedIds(string $group): array
    {
        $items = [];

        foreach ($this->all() as $abstract => $dependencyRegistration) {
            if ($dependencyRegistration->group !== $group) {
                continue;
            }

            $items[] = [
                'serviceId' => $abstract,
                'order' => $dependencyRegistration->groupOrder,
            ];
        }

        usort(
            array   : $items,
            callback: static fn (array $left, array $right): int => [$left['order'], $left['serviceId']]
                <=> [$right['order'], $right['serviceId']],
        );

        return array_column(array: $items, column_key: 'serviceId');
    }

    /**
     * Returns the matching target-specific binding for one consumer and need.
     */
    public function getContextualMatch(string $consumer, string $needs): mixed
    {
        $needs = $this->resolveAlias(abstract: $needs);
        $cacheKey = $consumer.'@'.$needs;
        if (array_key_exists(key: $cacheKey, array: $this->resolvedCache)) {
            return $this->resolvedCache[$cacheKey];
        }

        if (isset($this->contextual[$consumer][$needs])) {
            return $this->resolvedCache[$cacheKey] = $this->contextual[$consumer][$needs];
        }

        foreach ($this->wildcardContextual as $pattern => $rules) {
            if (isset($rules[$needs]) && fnmatch(pattern: $pattern, filename: $consumer)) {
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
     * @return array{parents: string[], interfaces: string[]}
     */
    private function getClassHierarchy(string $class): array
    {
        return $this->classHierarchyCache[$class] ?? $this->classHierarchyCache[$class] = [
            'parents' => class_exists(class: $class) ? array_values(array: class_parents(object_or_class: $class)) : [],
            'interfaces' => class_exists(class: $class) || interface_exists(interface: $class)
                ? array_values(array: class_implements(object_or_class: $class))
                : [],
        ];
    }

    /**
     * Adds one target-specific binding rule.
     */
    public function addContextual(string $consumer, string $needs, mixed $give): void
    {
        $needs = $this->resolveAlias(abstract: $needs);

        if (str_contains(haystack: $consumer, needle: '*')) {
            $this->wildcardContextual[$consumer][$needs] = $give;
        } else {
            $this->contextual[$consumer][$needs] = $give;
        }

        $this->touch();
    }

    /**
     * @return list<Closure>
     */
    public function getExtenders(string $abstract): array
    {
        return $this->extenders[$this->resolveAlias(abstract: $abstract)] ?? [];
    }

    /**
     * @return array<string, DependencyRegistration>
     */
    public function allIncludingSystem(): array
    {
        $services = $this->services + $this->systemServices;
        ksort(array: $services);

        return $services;
    }

    /**
     * @return array<string, string>
     */
    public function allAliases(): array
    {
        $aliases = $this->aliases;
        ksort(array: $aliases);

        return $aliases;
    }

    public function hasAlias(string $alias): bool
    {
        return isset($this->aliases[$alias]);
    }

    public function ownership(string $abstract) : RegistrationMetadata|null
    {
        return $this->get(abstract: $abstract)?->metadata;
    }

    public function get(string $abstract) : DependencyRegistration|null
    {
        $abstract = $this->resolveAlias(abstract: $abstract);

        return $this->services[$abstract] ?? $this->systemServices[$abstract] ?? null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function ownershipMap(): array
    {
        $ownership = [];

        foreach ($this->all() as $abstract => $dependencyRegistration) {
            $ownership[$abstract] = $dependencyRegistration->metadata->toArray();
        }

        ksort(array: $ownership);

        return $ownership;
    }

    public function allowsSliceAccess(string $viewerSlice, string $serviceId): bool
    {
        $normalized = trim(string: $viewerSlice);
        if ($normalized === '') {
            return false;
        }

        $manifest = $this->sliceManifest(slice: $normalized);
        if ($manifest === null) {
            return false;
        }

        $registrationMetadata = $this->metadataFor(serviceId: $serviceId);
        if ($registrationMetadata->ownerSlice === $normalized || $registrationMetadata->visibility === RegistrationVisibility::PUBLIC) {
            return true;
        }

        return $registrationMetadata->visibility === RegistrationVisibility::SHARED
            && $registrationMetadata->exported
            && in_array(needle: $registrationMetadata->ownerSlice, haystack: $manifest['imports'] ?? [], strict: true);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function sliceManifest(string $slice) : array|null
    {
        $normalized = trim(string: $slice);
        if ($normalized === '') {
            return null;
        }

        return $this->sliceManifests()[$normalized] ?? null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function sliceManifests(): array
    {
        $manifests = [];

        foreach ($this->all() as $abstract => $dependencyRegistration) {
            $metadata = $dependencyRegistration->metadata;
            $slice = $metadata->ownerSlice;

            $manifests[$slice] ??= [
                'slice' => $slice,
                'category' => $metadata->category,
                'categories' => [],
                'services' => [],
                'exports' => [],
                'public' => [],
                'shared' => [],
                'private' => [],
                'internal' => [],
                'imports' => [],
            ];

            $manifests[$slice]['categories'][$metadata->category] = true;
            $manifests[$slice]['services'][] = $abstract;
            $manifests[$slice]['imports'] = array_merge(
                $manifests[$slice]['imports'],
                $metadata->imports,
            )
                    |> array_unique(...)
                    |> array_values(...);

            if ($metadata->exported) {
                $manifests[$slice]['exports'][] = $abstract;
            }

            $manifests[$slice][$metadata->visibility][] = $abstract;
        }

        foreach ($manifests as $slice => $manifest) {
            foreach (['services', 'exports', 'public', 'shared', 'private', 'internal', 'imports'] as $key) {
                $values = array_values(array: array_unique(array: $manifest[$key]));
                sort(array: $values);
                $manifests[$slice][$key] = $values;
            }

            $categories = array_keys(array: $manifest['categories']);
            sort(array: $categories);
            $manifests[$slice]['categories'] = $categories;
            $manifests[$slice]['category'] = count(value: $categories) === 1 ? $categories[0] : 'mixed';
        }

        ksort(array: $manifests);

        return $manifests;
    }

    private function metadataFor(string $serviceId): RegistrationMetadata
    {
        return $this->get(abstract: $serviceId)?->metadata ?? RegistrationMetadata::for(unitId: $serviceId);
    }

    /**
     * @return array<string, mixed>
     */
    public function sliceView(string $slice): array
    {
        $manifest = $this->sliceManifest(slice: $slice);
        $visible = [];
        $hidden = [];

        foreach ($this->all() as $serviceId => $dependencyRegistration) {
            $access = $this->sliceAccessTo(viewerSlice: $slice, serviceId: $serviceId);
            $row = [
                'serviceId' => $serviceId,
                'ownerSlice' => $dependencyRegistration->metadata->ownerSlice,
                'visibility' => $dependencyRegistration->metadata->visibility,
                'reason' => $access['reason'],
            ];

            if ($access['allowed']) {
                $visible[] = $row;

                continue;
            }

            $hidden[] = $row;
        }

        usort(
            array   : $visible,
            callback: static fn (array $left, array $right): int => $left['serviceId'] <=> $right['serviceId'],
        );
        usort(
            array   : $hidden,
            callback: static fn (array $left, array $right): int => $left['serviceId'] <=> $right['serviceId'],
        );

        return [
            'slice' => trim(string: $slice),
            'exists' => $manifest !== null,
            'manifest' => $manifest,
            'visible' => $visible,
            'hidden' => $hidden,
        ];
    }

    /**
     * @return array{allowed: bool, reason: string, viewer: array<string, mixed>, dependency: array<string, mixed>}
     */
    public function sliceAccessTo(string $viewerSlice, string $serviceId): array
    {
        $normalized = trim(string: $viewerSlice);
        $manifest = $this->sliceManifest(slice: $normalized);
        $registrationMetadata = $this->metadataFor(serviceId: $serviceId);

        $viewer = [
            'slice' => $normalized,
            'exists' => $manifest !== null,
            'category' => (string) ($manifest['category'] ?? ''),
            'imports' => $manifest['imports'] ?? [],
            'exports' => $manifest['exports'] ?? [],
        ];

        if ($manifest === null) {
            return [
                'allowed' => false,
                'reason' => sprintf('slice view [%s] is not part of the current composition', $normalized),
                'viewer' => $viewer,
                'dependency' => $registrationMetadata->toArray(),
            ];
        }

        if ($registrationMetadata->ownerSlice === $normalized) {
            return [
                'allowed' => true,
                'reason' => 'service belongs to the active slice view',
                'viewer' => $viewer,
                'dependency' => $registrationMetadata->toArray(),
            ];
        }

        if ($registrationMetadata->visibility === RegistrationVisibility::PUBLIC) {
            return [
                'allowed' => true,
                'reason' => 'service is part of the public surface',
                'viewer' => $viewer,
                'dependency' => $registrationMetadata->toArray(),
            ];
        }

        if (
            $registrationMetadata->visibility === RegistrationVisibility::SHARED
            && $registrationMetadata->exported
            && in_array(needle: $registrationMetadata->ownerSlice, haystack: $manifest['imports'] ?? [], strict: true)
        ) {
            return [
                'allowed' => true,
                'reason' => 'service is shared, exported, and imported by the active slice view',
                'viewer' => $viewer,
                'dependency' => $registrationMetadata->toArray(),
            ];
        }

        return [
            'allowed' => false,
            'reason' => match ($registrationMetadata->visibility) {
                RegistrationVisibility::PRIVATE => 'private services stay inside their owning slice',
                RegistrationVisibility::INTERNAL => 'internal services are implementation details of their owning slice',
                RegistrationVisibility::SHARED => $registrationMetadata->exported
                    ? sprintf('active slice [%s] does not import [%s]', $normalized, $registrationMetadata->ownerSlice)
                    : 'shared service is not exported by its owning slice',
                default => 'service is not visible from the active slice view',
            },
            'viewer' => $viewer,
            'dependency' => $registrationMetadata->toArray(),
        ];
    }

    /**
     * @return list<array{concept: string, services: list<array{serviceId: string, ownerSlice: string, visibility:
     *                             string}>}>
     */
    public function duplicateConcepts(): array
    {
        $concepts = [];

        foreach ($this->all() as $abstract => $dependencyRegistration) {
            $metadata = $dependencyRegistration->metadata;
            $concepts[$metadata->concept][] = [
                'serviceId' => $abstract,
                'ownerSlice' => $metadata->ownerSlice,
                'visibility' => $metadata->visibility,
            ];
        }

        $duplicates = [];

        foreach ($concepts as $concept => $services) {
            if (count(value: $services) <= 1) {
                continue;
            }

            usort(
                array   : $services,
                callback: static fn (array $left, array $right): int => [$left['ownerSlice'], $left['serviceId']]
                    <=> [$right['ownerSlice'], $right['serviceId']],
            );

            $duplicates[] = [
                'concept' => $concept,
                'services' => $services,
            ];
        }

        usort(
            array   : $duplicates,
            callback: static fn (array $left, array $right): int => $left['concept'] <=> $right['concept'],
        );

        return $duplicates;
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function overrideHistory(string $abstract = ''): array
    {
        if ($abstract !== '') {
            $resolved = $this->resolveAlias(abstract: $abstract);

            return [$resolved => $this->overrideHistory[$resolved] ?? []];
        }

        $history = $this->overrideHistory;
        ksort(array: $history);

        return $history;
    }

    public function hasExtenders(string $abstract): bool
    {
        return ($this->extenders[$this->resolveAlias(abstract: $abstract)] ?? []) !== [];
    }

    /**
     * @return array{allowed: bool, reason: string, consumer: array<string, mixed>, dependency: array<string, mixed>}
     */
    public function accessTo(string $consumerId, string $dependencyId): array
    {
        $registrationMetadata = $this->metadataFor(serviceId: $consumerId);
        $dependency = $this->metadataFor(serviceId: $dependencyId);

        if ($registrationMetadata->ownerSlice === $dependency->ownerSlice) {
            return [
                'allowed' => true,
                'reason' => 'consumer and dependency live in the same slice',
                'consumer' => $registrationMetadata->toArray(),
                'dependency' => $dependency->toArray(),
            ];
        }

        if ($dependency->ownerSlice === 'foundation.system') {
            return [
                'allowed' => true,
                'reason' => 'foundation system services remain injectable infrastructure for the assembled runtime',
                'consumer' => $registrationMetadata->toArray(),
                'dependency' => $dependency->toArray(),
            ];
        }

        if ($dependency->visibility === RegistrationVisibility::PUBLIC) {
            return [
                'allowed' => true,
                'reason' => 'dependency is part of the public surface',
                'consumer' => $registrationMetadata->toArray(),
                'dependency' => $dependency->toArray(),
            ];
        }

        if ($dependency->visibility === RegistrationVisibility::SHARED) {
            if (! $dependency->exported) {
                return [
                    'allowed' => false,
                    'reason' => 'shared dependency is not exported by its owning slice',
                    'consumer' => $registrationMetadata->toArray(),
                    'dependency' => $dependency->toArray(),
                ];
            }

            if (! in_array(needle: $dependency->ownerSlice, haystack: $registrationMetadata->imports, strict: true)) {
                return [
                    'allowed' => false,
                    'reason' => sprintf('consumer slice [%s] does not declare an import for [%s]', $registrationMetadata->ownerSlice, $dependency->ownerSlice),
                    'consumer' => $registrationMetadata->toArray(),
                    'dependency' => $dependency->toArray(),
                ];
            }

            return [
                'allowed' => true,
                'reason' => 'dependency is explicitly exported and the consumer slice imports it',
                'consumer' => $registrationMetadata->toArray(),
                'dependency' => $dependency->toArray(),
            ];
        }

        return [
            'allowed' => false,
            'reason' => match ($dependency->visibility) {
                RegistrationVisibility::PRIVATE => 'private dependencies cannot cross slice boundaries',
                RegistrationVisibility::INTERNAL => 'internal dependencies cannot be used outside their owning slice',
                default => 'dependency is not accessible from the consumer slice',
            },
            'consumer' => $registrationMetadata->toArray(),
            'dependency' => $dependency->toArray(),
        ];
    }

    public function allowsAccess(string $consumerId, string $dependencyId): bool
    {
        $registrationMetadata = $this->metadataFor(serviceId: $consumerId);
        $dependency = $this->metadataFor(serviceId: $dependencyId);

        if ($registrationMetadata->ownerSlice === $dependency->ownerSlice || $dependency->ownerSlice === 'foundation.system' || $dependency->visibility === RegistrationVisibility::PUBLIC) {
            return true;
        }

        return $dependency->visibility === RegistrationVisibility::SHARED
            && $dependency->exported
            && in_array(needle: $dependency->ownerSlice, haystack: $registrationMetadata->imports, strict: true);
    }

    /**
     * @return array{allowed: bool, reason: string, dependency: array<string, mixed>}
     */
    public function topLevelAccessTo(string $serviceId): array
    {
        $registrationMetadata = $this->metadataFor(serviceId: $serviceId);

        if ($registrationMetadata->ownerSlice === 'foundation.system') {
            return [
                'allowed' => true,
                'reason' => 'foundation system services remain available to internal container flows and diagnostics',
                'dependency' => $registrationMetadata->toArray(),
            ];
        }

        if (
            $registrationMetadata->category === RegistrationCategory::FLOW
            && $registrationMetadata->intent === 'entry'
        ) {
            return [
                'allowed' => true,
                'reason' => 'flow entry owners remain valid top-level entry points even when their internals stay local',
                'dependency' => $registrationMetadata->toArray(),
            ];
        }

        if ($registrationMetadata->ownerSlice === 'default' && $registrationMetadata->visibility === RegistrationVisibility::PUBLIC) {
            return [
                'allowed' => true,
                'reason' => 'service uses the default public registration posture',
                'dependency' => $registrationMetadata->toArray(),
            ];
        }

        if ($registrationMetadata->visibility === RegistrationVisibility::PUBLIC) {
            return [
                'allowed' => true,
                'reason' => 'service is part of the public surface',
                'dependency' => $registrationMetadata->toArray(),
            ];
        }

        if ($registrationMetadata->visibility === RegistrationVisibility::SHARED && $registrationMetadata->exported) {
            return [
                'allowed' => true,
                'reason' => 'service is shared and explicitly exported',
                'dependency' => $registrationMetadata->toArray(),
            ];
        }

        return [
            'allowed' => false,
            'reason' => $registrationMetadata->category === RegistrationCategory::FLOW && $registrationMetadata->intent !== 'entry'
                ? 'flow-local services must be marked entry() before they become top-level surface'
                : match ($registrationMetadata->visibility) {
                    RegistrationVisibility::PRIVATE => 'private services are not part of the top-level container surface',
                    RegistrationVisibility::INTERNAL => 'internal services are implementation details of their owning slice',
                    default => 'shared services must be exported before they become top-level surface',
                },
            'dependency' => $registrationMetadata->toArray(),
        ];
    }

    public function allowsTopLevelAccess(string $serviceId): bool
    {
        $registrationMetadata = $this->metadataFor(serviceId: $serviceId);

        if ($registrationMetadata->ownerSlice === 'foundation.system') {
            return true;
        }

        if (
            $registrationMetadata->category === RegistrationCategory::FLOW
            && $registrationMetadata->intent === 'entry'
        ) {
            return true;
        }

        if ($registrationMetadata->visibility === RegistrationVisibility::PUBLIC) {
            return true;
        }

        return $registrationMetadata->visibility === RegistrationVisibility::SHARED
            && $registrationMetadata->exported;
    }

    /**
     * @return list<string>
     */
    public function aliasChain(string $abstract): array
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
    public function decorationChain(string $abstract): array
    {
        $resolved = $this->resolveAlias(abstract: $abstract);

        return $this->decorationDescriptors[$resolved] ?? [];
    }

    public function revision(): int
    {
        return $this->revision;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function contextual(): array
    {
        return $this->contextual + $this->wildcardContextual;
    }

    /**
     * @return array<string, list<string>>
     */
    public function tagIndex(): array
    {
        $index = [];

        foreach ($this->all() as $abstract => $dependencyRegistration) {
            foreach ($dependencyRegistration->tags as $tag) {
                $index[$tag][] = $abstract;
            }
        }

        foreach ($index as $tag => $ids) {
            $values = array_values(array: array_unique(array: $ids));
            sort(array: $values);
            $index[$tag] = $values;
        }

        ksort(array: $index);

        return $index;
    }

    /**
     * @return array<string, list<array{serviceId: string, order: int}>>
     */
    public function groupIndex(): array
    {
        $index = [];

        foreach ($this->all() as $abstract => $dependencyRegistration) {
            if ($dependencyRegistration->group === null) {
                continue;
            }

            if ($dependencyRegistration->group === '') {
                continue;
            }

            $index[$dependencyRegistration->group][] = [
                'serviceId' => $abstract,
                'order' => $dependencyRegistration->groupOrder,
            ];
        }

        foreach ($index as $group => $items) {
            usort(
                array   : $items,
                callback: static fn (array $left, array $right): int => [$left['order'], $left['serviceId']]
                    <=> [$right['order'], $right['serviceId']],
            );
            $index[$group] = $items;
        }

        ksort(array: $index);

        return $index;
    }

    /**
     * @return array<string, string>
     */
    public function lifetimeMap(): array
    {
        $lifetimes = [];

        foreach ($this->all() as $abstract => $dependencyRegistration) {
            $lifetimes[$abstract] = $dependencyRegistration->lifetime;
        }

        ksort(array: $lifetimes);

        return $lifetimes;
    }

    /**
     * @return array<string, bool>
     */
    public function deferredMap(): array
    {
        $deferred = [];

        foreach ($this->all() as $abstract => $dependencyRegistration) {
            $deferred[$abstract] = $dependencyRegistration->deferred;
        }

        ksort(array: $deferred);

        return $deferred;
    }

    /**
     * @return array<string, int>
     */
    public function decorationChains(): array
    {
        $chains = [];

        foreach ($this->extenders as $abstract => $extenders) {
            $chains[$abstract] = count(value: $extenders);
        }

        ksort(array: $chains);

        return $chains;
    }

    public function flush(): void
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
    public function resetDerivedState(): void
    {
        $this->resolvedCache = [];
        $this->classHierarchyCache = [];
    }
}
