<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Dependencies\Ownership;

/**
 * Canonical authored ownership metadata for one registration.
 */
final class RegistrationMetadata
{
    public readonly string $unitId;

    public readonly string $ownerSlice;

    public readonly string $category;

    public readonly string $visibility;

    /** @var list<string> */
    public readonly array $profiles;

    /** @var list<string> */
    public readonly array $flags;

    /** @var list<string> */
    public readonly array $tenants;

    /** @var list<string> */
    public readonly array $regions;

    /** @var list<string> */
    public readonly array $modes;

    public readonly string|null $overrideSource;

    public readonly string $reason;

    public readonly string $intent;

    public readonly string $provenance;

    public readonly bool $exported;

    /** @var list<string> */
    public readonly array $imports;

    public readonly string $concept;

    public readonly bool $fallback;

    /**
     * @param list<string> $profiles
     * @param list<string> $flags
     * @param list<string> $tenants
     * @param list<string> $regions
     * @param list<string> $modes
     * @param list<string> $imports
     */
    public function __construct(
        string $unitId,
        string $ownerSlice = 'default',
        string $category = RegistrationCategory::CONFIGURATION,
        string $visibility = RegistrationVisibility::PUBLIC,
        array $profiles = [],
        array $flags = [],
        array $tenants = [],
        array $regions = [],
        array $modes = [],
        string|null $overrideSource = null,
        string $reason = 'registered service',
        string $intent = 'standard',
        string $provenance = 'manual registration',
        bool $exported = false,
        array $imports = [],
        string $concept = '',
        bool $fallback = false
    ) {
        $this->unitId = $unitId;
        $this->ownerSlice = self::normalizeSlice(slice: $ownerSlice);
        $this->category = RegistrationCategory::normalize(category: $category);
        $this->visibility = RegistrationVisibility::normalize(visibility: $visibility);
        $this->profiles = self::stringList(values: $profiles);
        $this->flags = self::stringList(values: $flags);
        $this->tenants = self::stringList(values: $tenants);
        $this->regions = self::stringList(values: $regions);
        $this->modes = self::stringList(values: $modes);
        $this->overrideSource = self::normalizeNullable(value: $overrideSource);
        $this->reason = self::normalizeText(value: $reason, fallback: 'registered service');
        $this->intent = self::normalizeText(value: $intent, fallback: 'standard');
        $this->provenance = self::normalizeText(value: $provenance, fallback: 'manual registration');
        $this->exported = $exported;
        $this->imports = self::stringList(values: $imports);
        $this->concept = self::normalizeText(
            value   : $concept,
            fallback: self::derivedConcept(unitId: $unitId)
        );
        $this->fallback = $fallback;
    }

    public static function for(string $unitId) : self
    {
        return new self(unitId: $unitId);
    }

    /**
     * @param array<string, mixed> $state
     */
    public static function fromArray(array $state) : self
    {
        return new self(
            unitId        : (string) ($state['unitId'] ?? ''),
            ownerSlice    : (string) ($state['ownerSlice'] ?? 'default'),
            category      : (string) ($state['category'] ?? RegistrationCategory::CONFIGURATION),
            visibility    : (string) ($state['visibility'] ?? RegistrationVisibility::PUBLIC),
            profiles      : self::stringList(values: $state['profiles'] ?? []),
            flags         : self::stringList(values: $state['flags'] ?? []),
            tenants       : self::stringList(values: $state['tenants'] ?? []),
            regions       : self::stringList(values: $state['regions'] ?? []),
            modes         : self::stringList(values: $state['modes'] ?? []),
            overrideSource: is_string($state['overrideSource'] ?? null)
                ? $state['overrideSource']
                : null,
            reason        : (string) ($state['reason'] ?? 'registered service'),
            intent        : (string) ($state['intent'] ?? 'standard'),
            provenance    : (string) ($state['provenance'] ?? 'manual registration'),
            exported      : (bool) ($state['exported'] ?? false),
            imports       : self::stringList(values: $state['imports'] ?? []),
            concept       : (string) ($state['concept'] ?? ''),
            fallback      : (bool) ($state['fallback'] ?? false)
        );
    }

    /**
     * @param array<string, mixed> $state
     */
    public static function __set_state(array $state) : self
    {
        return self::fromArray(state: $state);
    }

    public function withOwnerSlice(string $ownerSlice) : self
    {
        return $this->copy(overrides: ['ownerSlice' => $ownerSlice]);
    }

    public function withCategory(string $category) : self
    {
        return $this->copy(overrides: ['category' => $category]);
    }

    public function withVisibility(string $visibility) : self
    {
        return $this->copy(overrides: ['visibility' => $visibility]);
    }

    /**
     * @param list<string> $profiles
     */
    public function withProfiles(array $profiles) : self
    {
        return $this->copy(overrides: ['profiles' => $profiles]);
    }

    /**
     * @param list<string> $flags
     */
    public function withFlags(array $flags) : self
    {
        return $this->copy(overrides: ['flags' => $flags]);
    }

    /**
     * @param list<string> $tenants
     */
    public function withTenants(array $tenants) : self
    {
        return $this->copy(overrides: ['tenants' => $tenants]);
    }

    /**
     * @param list<string> $regions
     */
    public function withRegions(array $regions) : self
    {
        return $this->copy(overrides: ['regions' => $regions]);
    }

    /**
     * @param list<string> $modes
     */
    public function withModes(array $modes) : self
    {
        return $this->copy(overrides: ['modes' => $modes]);
    }

    public function withOverrideSource(string|null $overrideSource) : self
    {
        return $this->copy(overrides: ['overrideSource' => $overrideSource]);
    }

    public function withReason(string $reason) : self
    {
        return $this->copy(overrides: ['reason' => $reason]);
    }

    public function withIntent(string $intent) : self
    {
        return $this->copy(overrides: ['intent' => $intent]);
    }

    public function withProvenance(string $provenance) : self
    {
        return $this->copy(overrides: ['provenance' => $provenance]);
    }

    public function withExported(bool $exported) : self
    {
        return $this->copy(overrides: ['exported' => $exported]);
    }

    /**
     * @param list<string> $imports
     */
    public function withImports(array $imports) : self
    {
        return $this->copy(overrides: ['imports' => $imports]);
    }

    public function withConcept(string $concept) : self
    {
        return $this->copy(overrides: ['concept' => $concept]);
    }

    public function withFallback(bool $fallback) : self
    {
        return $this->copy(overrides: ['fallback' => $fallback]);
    }

    public function supportsEnvironment(string $environment) : bool
    {
        if ($this->profiles === [] || $environment === '') {
            return true;
        }

        return in_array($environment, $this->profiles, true);
    }

    /**
     * @param list<string> $activeFlags
     */
    public function supportsFlags(array $activeFlags) : bool
    {
        if ($this->flags === []) {
            return true;
        }

        foreach ($this->flags as $flag) {
            if (! in_array($flag, $activeFlags, true)) {
                return false;
            }
        }

        return true;
    }

    public function supportsTenant(string $tenant) : bool
    {
        return $this->tenants === [] || in_array($tenant, $this->tenants, true);
    }

    public function supportsRegion(string $region) : bool
    {
        return $this->regions === [] || in_array($region, $this->regions, true);
    }

    public function supportsMode(string $mode) : bool
    {
        return $this->modes === [] || in_array($mode, $this->modes, true);
    }

    public function exportsSurface() : bool
    {
        return $this->exported || in_array(
            $this->visibility,
            [RegistrationVisibility::PUBLIC, RegistrationVisibility::SHARED],
            true
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return [
            'unitId' => $this->unitId,
            'ownerSlice' => $this->ownerSlice,
            'category' => $this->category,
            'visibility' => $this->visibility,
            'profiles' => $this->profiles,
            'flags' => $this->flags,
            'tenants' => $this->tenants,
            'regions' => $this->regions,
            'modes' => $this->modes,
            'overrideSource' => $this->overrideSource,
            'reason' => $this->reason,
            'intent' => $this->intent,
            'provenance' => $this->provenance,
            'exported' => $this->exported,
            'imports' => $this->imports,
            'concept' => $this->concept,
            'fallback' => $this->fallback,
        ];
    }

    /**
     * @param mixed $values
     * @return list<string>
     */
    private static function stringList(mixed $values) : array
    {
        if (! is_array($values)) {
            return [];
        }

        $items = [];

        foreach ($values as $value) {
            if (! is_string($value)) {
                continue;
            }

            $normalized = trim($value);
            if ($normalized === '') {
                continue;
            }

            $items[] = $normalized;
        }

        $items = array_values(array_unique($items));
        sort($items);

        return $items;
    }

    private static function normalizeSlice(string $slice) : string
    {
        return self::normalizeText(value: $slice, fallback: 'default');
    }

    private static function normalizeText(string $value, string $fallback) : string
    {
        $normalized = trim($value);

        return $normalized !== '' ? $normalized : $fallback;
    }

    private static function normalizeNullable(string|null $value) : string|null
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized !== '' ? $normalized : null;
    }

    private static function derivedConcept(string $unitId) : string
    {
        $normalized = str_replace(['\\', '/', '@', ':'], '.', $unitId);
        $segments = array_values(array_filter(explode('.', $normalized), static fn(string $segment) : bool => $segment !== ''));
        $last = $segments !== [] ? $segments[array_key_last($segments)] : $unitId;

        return strtolower(trim($last)) ?: strtolower($unitId);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function copy(array $overrides = []) : self
    {
        return new self(
            unitId        : $this->unitId,
            ownerSlice    : (string) ($overrides['ownerSlice'] ?? $this->ownerSlice),
            category      : (string) ($overrides['category'] ?? $this->category),
            visibility    : (string) ($overrides['visibility'] ?? $this->visibility),
            profiles      : $overrides['profiles'] ?? $this->profiles,
            flags         : $overrides['flags'] ?? $this->flags,
            tenants       : $overrides['tenants'] ?? $this->tenants,
            regions       : $overrides['regions'] ?? $this->regions,
            modes         : $overrides['modes'] ?? $this->modes,
            overrideSource: $overrides['overrideSource'] ?? $this->overrideSource,
            reason        : (string) ($overrides['reason'] ?? $this->reason),
            intent        : (string) ($overrides['intent'] ?? $this->intent),
            provenance    : (string) ($overrides['provenance'] ?? $this->provenance),
            exported      : (bool) ($overrides['exported'] ?? $this->exported),
            imports       : $overrides['imports'] ?? $this->imports,
            concept       : (string) ($overrides['concept'] ?? $this->concept),
            fallback      : (bool) ($overrides['fallback'] ?? $this->fallback)
        );
    }
}
