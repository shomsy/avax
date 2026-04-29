<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Composition\Compilation;

use Avax\Components\Application\Container\System\Capabilities\Resolution\LifetimePlan;
use SensitiveParameter;

/**
 * Typed compiled artifact metadata. This keeps the sidecar schema explicit.
 */
final readonly class ArtifactMetadata
{
    public string $checksum;
    public array  $statistics;
    public array  $invalidationReasons;
    public array  $validationIssues;
    public array  $invalidatedServices;
    public array  $changedServices;
    public array  $pruning;
    public array  $slices;
    public array  $ownership;
    public array  $decorations;
    public array  $deferred;
    public array  $lifetimes;
    public array  $tags;
    public array  $aliases;
    public array  $dependencies;
    public array  $sources;
    public array  $services;
    public array  $entries;
    public string $benchmarkBuildMarker;
    public bool   $warmed;
    public array  $artifactPaths;
    public string $dependencyGraphRevision;
    public string $fingerprint;
    public bool   $strict;
    public string $diagnosticsMode;
    public string $pruneMode;
    public string $executionMode;
    public string $compileMode;
    public string $environment;
    public string $settingsFingerprint;
    public string $configHash;
    public string $cacheVersion;
    public string $compiledAt;
    public int    $schemaVersion;
    public string $format;

    /**
     * @param array<string, string>                                       $entries
     * @param array<string, array{method: string, signature: string}>     $schemaVersion
     *                                                                   $services
     * @param array<string, string>                                       $sources
     * @param array<string, list<string>>                                 $dependencies
     * @param array<string, string>                                       $aliases
     * @param array<string, list<string>>                                 $tags
     * @param array<string, array{name: string, shared: bool, scoped: bool, transient: bool, pooled: bool, poolSize:
     *                                  int, poolResetBeforeReuse: bool}> $lifetimes
     * @param array<string, bool>                                         $deferred
     * @param array<string, int>                                          $decorations
     * @param array<string, array<string, mixed>>                         $pruneMode
     *                                                                   $ownership
     * @param array<string, array<string, mixed>>                         $diagnosticsMode
     *                                                                   $slices
     * @param array<string, mixed>                                        $pruning
     * @param list<string>                                                $changedServices
     * @param list<string>                                                $invalidatedServices
     * @param list<string>                                                $validationIssues
     * @param list<string>                                                $invalidationReasons
     * @param array<string, int>                                          $statistics
     */
    public function __construct(
        string                       $format,
        int                          $schemaVersion,
        string                       $compiledAt,
        string                       $cacheVersion,
        #[SensitiveParameter] string $configHash,
        string                       $settingsFingerprint,
        string                       $environment,
        string                       $compileMode,
        string                       $executionMode,
        string                       $pruneMode,
        string                       $diagnosticsMode,
        bool                         $strict,
        string                       $fingerprint,
        string                       $dependencyGraphRevision,
        array                        $artifactPaths,
        bool                         $warmed,
        string                       $benchmarkBuildMarker,
        array                        $entries,
        array                        $services,
        array                        $sources,
        array                        $dependencies,
        array                        $aliases,
        array                        $tags,
        array                        $lifetimes,
        array                        $deferred,
        array                        $decorations,
        array                        $ownership,
        array                        $slices,
        array                        $pruning,
        array                        $changedServices,
        array                        $invalidatedServices,
        array                        $validationIssues,
        array                        $invalidationReasons,
        array                        $statistics,
        string                       $checksum
    )
    {
        $this->format                  = $format;
        $this->schemaVersion           = $schemaVersion;
        $this->compiledAt              = $compiledAt;
        $this->cacheVersion            = $cacheVersion;
        $this->configHash              = $configHash;
        $this->settingsFingerprint     = $settingsFingerprint;
        $this->environment             = $environment;
        $this->compileMode             = $compileMode;
        $this->executionMode           = $executionMode;
        $this->pruneMode               = $pruneMode;
        $this->diagnosticsMode         = $diagnosticsMode;
        $this->strict                  = $strict;
        $this->fingerprint             = $fingerprint;
        $this->dependencyGraphRevision = $dependencyGraphRevision;
        $this->artifactPaths           = $artifactPaths;
        $this->warmed                  = $warmed;
        $this->benchmarkBuildMarker    = $benchmarkBuildMarker;
        $this->entries                 = $entries;
        $this->services                = $services;
        $this->sources                 = $sources;
        $this->dependencies            = $dependencies;
        $this->aliases                 = $aliases;
        $this->tags                    = $tags;
        $this->lifetimes               = $lifetimes;
        $this->deferred                = $deferred;
        $this->decorations             = $decorations;
        $this->ownership               = $ownership;
        $this->slices                  = $slices;
        $this->pruning                 = $pruning;
        $this->changedServices         = $changedServices;
        $this->invalidatedServices     = $invalidatedServices;
        $this->validationIssues        = $validationIssues;
        $this->invalidationReasons     = $invalidationReasons;
        $this->statistics              = $statistics;
        $this->checksum                = $checksum;
    }

    /**
     * @param list<string> $serviceIds
     */
    public function includes(array $serviceIds) : bool
    {
        foreach (array_values(array: array_unique(array: $serviceIds)) as $serviceId) {
            if (! $this->hasEntry(serviceId: $serviceId)) {
                return false;
            }
        }

        return true;
    }

    public function hasEntry(string $serviceId) : bool
    {
        return isset($this->entries[$serviceId]);
    }

    /**
     * @return list<string>
     */
    public function entryIds() : array
    {
        return array_keys(array: $this->entries);
    }

    /**
     * @return array<string, LifetimePlan>
     */
    public function lifetimePlans() : array
    {
        $plans = [];

        foreach ($this->lifetimes as $serviceId => $plan) {
            $plans[$serviceId] = LifetimePlan::fromArray(serviceId: $serviceId, state: $plan);
        }

        ksort(array: $plans);

        return $plans;
    }

    /**
     * @param array<string, mixed> $state
     */
    public static function fromArray(array $state) : self
    {
        return new self(
            format                 : (string) ($state['format'] ?? ''),
            schemaVersion          : (int) ($state['schemaVersion'] ?? 0),
            compiledAt             : (string) ($state['compiledAt'] ?? ''),
            cacheVersion           : (string) ($state['cacheVersion'] ?? ''),
            configHash             : (string) ($state['configHash'] ?? ''),
            settingsFingerprint    : (string) ($state['settingsFingerprint'] ?? ''),
            environment            : (string) ($state['environment'] ?? ''),
            compileMode            : (string) ($state['compileMode'] ?? ''),
            executionMode          : (string) ($state['executionMode'] ?? ''),
            pruneMode              : (string) ($state['pruneMode'] ?? ''),
            diagnosticsMode        : (string) ($state['diagnosticsMode'] ?? ''),
            strict                 : (bool) ($state['strict'] ?? false),
            fingerprint            : (string) ($state['fingerprint'] ?? ''),
            dependencyGraphRevision: (string) ($state['dependencyGraphRevision'] ?? ''),
            artifactPaths          : self::stringMap(state: $state['artifactPaths'] ?? []),
            warmed                 : (bool) ($state['warmed'] ?? false),
            benchmarkBuildMarker   : (string) ($state['benchmarkBuildMarker'] ?? ''),
            entries                : self::stringMap(state: $state['entries'] ?? []),
            services               : self::services(state: $state['services'] ?? []),
            sources                : self::stringMap(state: $state['sources'] ?? []),
            dependencies           : self::tags(state: $state['dependencies'] ?? []),
            aliases                : self::stringMap(state: $state['aliases'] ?? []),
            tags                   : self::tags(state: $state['tags'] ?? []),
            lifetimes              : self::lifetimes(state: $state['lifetimes'] ?? []),
            deferred               : self::boolMap(state: $state['deferred'] ?? []),
            decorations            : self::intMap(state: $state['decorations'] ?? []),
            ownership              : self::mapOfMaps(state: $state['ownership'] ?? []),
            slices                 : self::mapOfMaps(state: $state['slices'] ?? []),
            pruning                : self::map(state: $state['pruning'] ?? []),
            changedServices        : self::stringList(state: $state['changedServices'] ?? []),
            invalidatedServices    : self::stringList(state: $state['invalidatedServices'] ?? []),
            validationIssues       : self::stringList(state: $state['validationIssues'] ?? []),
            invalidationReasons    : self::stringList(state: $state['invalidationReasons'] ?? []),
            statistics             : self::intMap(state: $state['statistics'] ?? []),
            checksum               : (string) ($state['checksum'] ?? '')
        );
    }

    /**
     * @param mixed $state
     *
     * @return array<string, string>
     */
    private static function stringMap(mixed $state) : array
    {
        if (! is_array(value: $state)) {
            return [];
        }

        $items = [];

        foreach ($state as $key => $value) {
            if (! is_string(value: $key)) {
                continue;
            }

            $items[$key] = (string) $value;
        }

        ksort(array: $items);

        return $items;
    }

    /**
     * @param mixed $state
     *
     * @return array<string, array{method: string, signature: string}>
     */
    private static function services(mixed $state) : array
    {
        if (! is_array(value: $state)) {
            return [];
        }

        $services = [];

        foreach ($state as $serviceId => $service) {
            if (! is_string(value: $serviceId) || ! is_array(value: $service)) {
                continue;
            }

            $services[$serviceId] = [
                'method'    => (string) ($service['method'] ?? ''),
                'signature' => (string) ($service['signature'] ?? ''),
            ];
        }

        ksort(array: $services);

        return $services;
    }

    /**
     * @param mixed $state
     *
     * @return array<string, list<string>>
     */
    private static function tags(mixed $state) : array
    {
        if (! is_array(value: $state)) {
            return [];
        }

        $tags = [];

        foreach ($state as $tag => $serviceIds) {
            if (! is_string(value: $tag) || ! is_array(value: $serviceIds)) {
                continue;
            }

            $values = array_map(
                    callback: static fn (mixed $value) : string => (string) $value,
                    array   : $serviceIds
                )
                    |> array_unique(...)
                    |> array_values(...);
            sort(array: $values);
            $tags[$tag] = $values;
        }

        ksort(array: $tags);

        return $tags;
    }

    /**
     * @param mixed $state
     *
     * @return array<string, array{name: string, shared: bool, scoped: bool, transient: bool}>
     */
    private static function lifetimes(mixed $state) : array
    {
        if (! is_array(value: $state)) {
            return [];
        }

        $plans = [];

        foreach ($state as $serviceId => $plan) {
            if (! is_string(value: $serviceId)) {
                continue;
            }

            $plans[$serviceId] = LifetimePlan::fromArray(
                serviceId: $serviceId,
                state    : is_array(value: $plan) ? $plan : []
            )->toArray();
        }

        ksort(array: $plans);

        return $plans;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return [
            'format'                  => $this->format,
            'schemaVersion'           => $this->schemaVersion,
            'compiledAt'              => $this->compiledAt,
            'cacheVersion'            => $this->cacheVersion,
            'configHash'              => $this->configHash,
            'settingsFingerprint'     => $this->settingsFingerprint,
            'environment'             => $this->environment,
            'compileMode'             => $this->compileMode,
            'executionMode'           => $this->executionMode,
            'pruneMode'               => $this->pruneMode,
            'diagnosticsMode'         => $this->diagnosticsMode,
            'strict'                  => $this->strict,
            'fingerprint'             => $this->fingerprint,
            'dependencyGraphRevision' => $this->dependencyGraphRevision,
            'artifactPaths'           => $this->artifactPaths,
            'warmed'                  => $this->warmed,
            'benchmarkBuildMarker'    => $this->benchmarkBuildMarker,
            'entries'                 => $this->entries,
            'services'                => $this->services,
            'sources'                 => $this->sources,
            'dependencies'            => $this->dependencies,
            'aliases'                 => $this->aliases,
            'tags'                    => $this->tags,
            'lifetimes'               => $this->lifetimes,
            'deferred'                => $this->deferred,
            'decorations'             => $this->decorations,
            'ownership'               => $this->ownership,
            'slices'                  => $this->slices,
            'pruning'                 => $this->pruning,
            'changedServices'         => $this->changedServices,
            'invalidatedServices'     => $this->invalidatedServices,
            'validationIssues'        => $this->validationIssues,
            'invalidationReasons'     => $this->invalidationReasons,
            'statistics'              => $this->statistics,
            'checksum'                => $this->checksum,
        ];
    }

    /**
     * @param mixed $state
     *
     * @return array<string, bool>
     */
    private static function boolMap(mixed $state) : array
    {
        if (! is_array(value: $state)) {
            return [];
        }

        $items = [];

        foreach ($state as $key => $value) {
            if (! is_string(value: $key)) {
                continue;
            }

            $items[$key] = (bool) $value;
        }

        ksort(array: $items);

        return $items;
    }

    /**
     * @param mixed $state
     *
     * @return array<string, int>
     */
    private static function intMap(mixed $state) : array
    {
        if (! is_array(value: $state)) {
            return [];
        }

        $items = [];

        foreach ($state as $key => $value) {
            if (! is_string(value: $key)) {
                continue;
            }

            $items[$key] = (int) $value;
        }

        ksort(array: $items);

        return $items;
    }

    /**
     * @param mixed $state
     *
     * @return array<string, array<string, mixed>>
     */
    private static function mapOfMaps(mixed $state) : array
    {
        if (! is_array(value: $state)) {
            return [];
        }

        $items = [];

        foreach ($state as $key => $value) {
            if (! is_string(value: $key) || ! is_array(value: $value)) {
                continue;
            }

            ksort(array: $value);
            $items[$key] = $value;
        }

        ksort(array: $items);

        return $items;
    }

    /**
     * @param mixed $state
     *
     * @return array<string, mixed>
     */
    private static function map(mixed $state) : array
    {
        if (! is_array(value: $state)) {
            return [];
        }

        $items = [];

        foreach ($state as $key => $value) {
            if (! is_string(value: $key)) {
                continue;
            }

            $items[$key] = $value;
        }

        ksort(array: $items);

        return $items;
    }

    /**
     * @param mixed $state
     *
     * @return list<string>
     */
    private static function stringList(mixed $state) : array
    {
        if (! is_array(value: $state)) {
            return [];
        }

        $items = array_values(array: array_map(
                                         callback: static fn (mixed $value) : string => (string) $value,
                                         array   : $state
                                     ));

        sort(array: $items);

        return array_values(array: array_unique(array: $items));
    }
}
