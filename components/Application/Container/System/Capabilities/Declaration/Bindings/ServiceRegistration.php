<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings;

use Avax\Components\Application\Container\System\Capabilities\Declaration\Ownership\RegistrationCategory;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Ownership\RegistrationMetadata;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Ownership\RegistrationVisibility;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\Lifetimes\JobLifetime;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\Lifetimes\OperationLifetime;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\Lifetimes\PooledLifetime;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\Lifetimes\RequestLifetime;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\Lifetimes\TenantLifetime;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\Lifetimes\TransientLifetime;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ScopeKind;

/**
 * One stored service registration.
 */
final class ServiceRegistration
{
    /**
     * Stores the concrete binding target.
     */
    public mixed $concrete = null;

    /**
     * Stores the configured service lifetime name.
     */
    public string $lifetime = TransientLifetime::NAME;

    /**
     * Marks the registration as deferred.
     */
    public bool $deferred = false;

    /**
     * Marks one shared registration for eager warmup.
     */
    public bool $warm = false;

    /**
     * Marks one shared registration for lazy singleton behavior.
     */
    public bool $lazy = false;

    /**
     * Marks the runtime instance as explicitly disposable.
     */
    public bool $disposable = false;

    public int $poolSize = 8;

    public bool $poolResetBeforeReuse = true;

    public string $poolScopeKind = ScopeKind::Operation->value;

    public string|null $group = null;

    public int $groupOrder = 0;

    /** @var list<string> */
    public array $tags = [];

    /** @var array<string, mixed> */
    public array $arguments = [];

    public RegistrationMetadata $metadata;

    public readonly string $abstract;

    public function __construct(
        string $abstract
    ) {
        $this->abstract = $abstract;
        $this->metadata = RegistrationMetadata::for(unitId: $abstract);
    }

    /**
     * Restores the registration from generated PHP state.
     */
    public static function __set_state(array $array): self
    {
        $registration = new self(abstract: $array['abstract']);
        $registration->concrete = $array['concrete'] ?? null;
        $registration->lifetime = $array['lifetime'] ?? TransientLifetime::NAME;
        $registration->deferred = $array['deferred'] ?? false;
        $registration->warm = $array['warm'] ?? false;
        $registration->lazy = $array['lazy'] ?? false;
        $registration->disposable = $array['disposable'] ?? false;
        $registration->poolSize = max(1, (int) ($array['poolSize'] ?? 8));
        $registration->poolResetBeforeReuse = (bool) ($array['poolResetBeforeReuse'] ?? true);
        $registration->poolScopeKind = ScopeKind::normalize(
            kind: (string) ($array['poolScopeKind'] ?? ScopeKind::Operation->value)
        )->value;
        $registration->group = is_string(value: $array['group'] ?? null) ? $array['group'] : null;
        $registration->groupOrder = (int) ($array['groupOrder'] ?? 0);
        $registration->tags = $array['tags'] ?? [];
        $registration->arguments = $array['arguments'] ?? [];
        $metadata = $array['metadata'] ?? null;
        if ($metadata instanceof RegistrationMetadata) {
            $registration->metadata = $metadata;
        } elseif (is_array(value: $metadata)) {
            $registration->metadata = RegistrationMetadata::fromArray(state: $metadata);
        }

        return $registration;
    }

    /**
     * Sets the concrete binding target.
     */
    public function to(string|callable|null $concrete): self
    {
        $this->concrete = $concrete;

        return $this;
    }

    /**
     * Adds one or more tags.
     */
    public function tag(string|array $tags): self
    {
        $this->tags = array_merge($this->tags, (array) $tags)
                |> array_unique(...)
                |> array_values(...);

        return $this;
    }

    /**
     * Adds one named argument override.
     */
    public function withArgument(string $name, mixed $value): self
    {
        return $this->withArguments(arguments: [$name => $value]);
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    public function withArguments(array $arguments): self
    {
        $this->arguments = array_merge($this->arguments, $arguments);

        return $this;
    }

    /**
     * Marks the registration as deferred or eager.
     */
    public function defer(bool $deferred = true): self
    {
        $this->deferred = $deferred;

        return $this;
    }

    public function profiles(string|array $profiles): self
    {
        $this->metadata = $this->metadata->withProfiles(profiles: $this->stringList(values: $profiles));

        return $this;
    }

    /**
     * @return list<string>
     */
    private function stringList(mixed $values): array
    {
        $items = array_map(
            callback: static fn (mixed $value): string => is_string(value: $value) ? trim(string: $value) : '',
            array: (array) $values
        )
                |> (static fn ($x) => array_filter(array: $x, callback: static fn (string $value): bool => $value !== ''))
                |> array_values(...);

        $items = array_values(array: array_unique(array: $items));
        sort(array: $items);

        return $items;
    }

    public function flags(string|array $flags): self
    {
        $this->metadata = $this->metadata->withFlags(flags: $this->stringList(values: $flags));

        return $this;
    }

    public function tenants(string|array $tenants): self
    {
        $this->metadata = $this->metadata->withTenants(tenants: $this->stringList(values: $tenants));

        return $this;
    }

    public function regions(string|array $regions): self
    {
        $this->metadata = $this->metadata->withRegions(regions: $this->stringList(values: $regions));

        return $this;
    }

    public function modes(string|array $modes): self
    {
        $this->metadata = $this->metadata->withModes(modes: $this->stringList(values: $modes));

        return $this;
    }

    public function overrideSource(string $source): self
    {
        $this->metadata = $this->metadata->withOverrideSource(overrideSource: $source);

        return $this;
    }

    public function because(string $reason): self
    {
        $this->metadata = $this->metadata->withReason(reason: $reason);

        return $this;
    }

    public function provenance(string $provenance): self
    {
        $this->metadata = $this->metadata->withProvenance(provenance: $provenance);

        return $this;
    }

    public function export(bool $exported = true): self
    {
        $this->metadata = $this->metadata->withExported(exported: $exported);

        return $this;
    }

    public function import(string|array $slices): self
    {
        $this->metadata = $this->metadata->withImports(imports: $this->stringList(values: $slices));

        return $this;
    }

    public function concept(string $concept): self
    {
        $this->metadata = $this->metadata->withConcept(concept: $concept);

        return $this;
    }

    public function fallback(bool $fallback = true): self
    {
        $this->metadata = $this->metadata->withFallback(fallback: $fallback);

        return $this;
    }

    public function lockOwnership(string $ownerSlice, string $category): self
    {
        $this->metadata = $this->metadata->lockOwnership(
            ownerSlice: $ownerSlice,
            category: $category
        );

        return $this;
    }

    public function asFlow(string $ownerSlice): self
    {
        return $this
            ->ownedBy(ownerSlice: $ownerSlice)
            ->category(category: RegistrationCategory::FLOW);
    }

    public function category(string $category): self
    {
        $this->metadata = $this->metadata->withCategory(category: $category);

        return $this;
    }

    public function ownedBy(string $ownerSlice): self
    {
        $this->metadata = $this->metadata->withOwnerSlice(ownerSlice: $ownerSlice);

        return $this;
    }

    public function asCapability(string $ownerSlice): self
    {
        return $this
            ->ownedBy(ownerSlice: $ownerSlice)
            ->category(category: RegistrationCategory::CAPABILITY);
    }

    public function asConfiguration(string $ownerSlice): self
    {
        return $this
            ->ownedBy(ownerSlice: $ownerSlice)
            ->category(category: RegistrationCategory::CONFIGURATION);
    }

    public function asFoundation(string $ownerSlice): self
    {
        return $this
            ->ownedBy(ownerSlice: $ownerSlice)
            ->category(category: RegistrationCategory::FOUNDATION);
    }

    public function asPrivate(): self
    {
        return $this->visibility(visibility: RegistrationVisibility::PRIVATE);
    }

    public function visibility(string $visibility): self
    {
        $this->metadata = $this->metadata->withVisibility(visibility: $visibility);

        return $this;
    }

    public function asShared(): self
    {
        return $this->visibility(visibility: RegistrationVisibility::SHARED);
    }

    public function asPublic(): self
    {
        return $this->visibility(visibility: RegistrationVisibility::PUBLIC);
    }

    public function asInternal(): self
    {
        return $this->visibility(visibility: RegistrationVisibility::INTERNAL);
    }

    public function entry(bool $entry = true): self
    {
        return $this->intent(intent: $entry ? 'entry' : 'standard');
    }

    public function intent(string $intent): self
    {
        $this->metadata = $this->metadata->withIntent(intent: $intent);

        return $this;
    }

    public function operation(): self
    {
        $this->lifetime = OperationLifetime::NAME;

        return $this;
    }

    public function request(): self
    {
        $this->lifetime = RequestLifetime::NAME;

        return $this;
    }

    public function job(): self
    {
        $this->lifetime = JobLifetime::NAME;

        return $this;
    }

    public function tenant(): self
    {
        $this->lifetime = TenantLifetime::NAME;

        return $this;
    }

    public function warm(bool $warm = true): self
    {
        $this->warm = $warm;

        return $this;
    }

    public function lazy(bool $lazy = true): self
    {
        $this->lazy = $lazy;

        return $this;
    }

    public function pooled(int|null $maxSize = null, string|null $scopeKind = null,
        bool $resetBeforeReuse = true
    ): self {
        $maxSize ??= 8;
        $scopeKind ??= ScopeKind::Operation->value;
        $this->lifetime = PooledLifetime::NAME;
        $this->poolSize = max(1, $maxSize);
        $this->poolScopeKind = ScopeKind::normalize(kind: $scopeKind)->value;
        $this->poolResetBeforeReuse = $resetBeforeReuse;
        $this->warm = false;

        return $this;
    }

    public function dispose(bool $disposable = true): self
    {
        $this->disposable = $disposable;

        return $this;
    }

    public function group(string $group, int $order = 0): self
    {
        $normalized = trim(string: $group);
        $this->group = $normalized !== '' ? $normalized : null;
        $this->groupOrder = $order;

        return $this;
    }
}
