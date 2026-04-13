<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Composition\Compilation;

use Avax\Container\DI\Capabilities\Resolution\LifetimePlan;

/**
 * Typed compiled artifact metadata. This keeps the sidecar schema explicit.
 */
final readonly class ArtifactMetadata
{
    /**
     * @param array<string, string>                                       $entries
     * @param array<string, array{method: string, signature: string}>
     *                                                                   $services
     * @param array<string, string>                                       $sources
     * @param array<string, list<string>>                                 $dependencies
     * @param array<string, string>                                       $aliases
     * @param array<string, list<string>>                                 $tags
     * @param array<string, array{name: string, shared: bool, scoped: bool, transient: bool, pooled: bool, poolSize:
     *                                  int, poolResetBeforeReuse: bool}> $lifetimes
     * @param array<string, bool>                                         $deferred
     * @param array<string, int>                                          $decorations
     * @param array<string, array<string, mixed>>
     *                                                                   $ownership
     * @param array<string, array<string, mixed>>
     *                                                                   $slices
     * @param array<string, mixed>                                        $pruning
     * @param list<string>                                                $changedServices
     * @param list<string>                                                $invalidatedServices
     * @param list<string>                                                $validationIssues
     * @param list<string>                                                $invalidationReasons
     * @param array<string, int>                                          $statistics
     */
    public function __construct(
        public string                        $format,
        public int                           $schemaVersion,
        public string                        $compiledAt,
        public string                        $cacheVersion,
        #[\SensitiveParameter] public string $configHash,
        public string                        $settingsFingerprint,
        public string                        $environment,
        public string                        $compileMode,
        public string                        $executionMode,
        public string                        $pruneMode,
        public string                        $diagnosticsMode,
        public bool                          $strict,
        public string                        $fingerprint,
        public string                        $dependencyGraphRevision,
        public array                         $artifactPaths,
        public bool   $warmed,
        public string $benchmarkBuildMarker,
        public array  $entries,
        public array  $services,
        public array  $sources,
        public array  $dependencies,
        public array  $aliases,
        public array  $tags,
        public array  $lifetimes,
        public array  $deferred,
        public array  $decorations,
        public array  $ownership,
        public array  $slices,
        public array  $pruning,
        public array  $changedServices,
        public array  $invalidatedServices,
        public array  $validationIssues,
        public array  $invalidationReasons,
        public array  $statistics,
        public string $checksum
    ) {}

    /**
     * @param list<string> $serviceIds
     */
    public function includes(array $serviceIds) : bool
    {
        foreach (array_values(array_unique($serviceIds)) as $serviceId) {
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
        return array_keys($this->entries);
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

        ksort($plans);

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
        if (! is_array($state)) {
            return [];
        }

        $items = [];

        foreach ($state as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            $items[$key] = (string) $value;
        }

        ksort($items);

        return $items;
    }

    /**
     * @param mixed $state
     *
     * @return array<string, array{method: string, signature: string}>
     */
    private static function services(mixed $state) : array
    {
        if (! is_array($state)) {
            return [];
        }

        $services = [];

        foreach ($state as $serviceId => $service) {
            if (! is_string($serviceId) || ! is_array($service)) {
                continue;
            }

            $services[$serviceId] = [
                'method'    => (string) ($service['method'] ?? ''),
                'signature' => (string) ($service['signature'] ?? ''),
            ];
        }

        ksort($services);

        return $services;
    }

    /**
     * @param mixed $state
     *
     * @return array<string, list<string>>
     */
    private static function tags(mixed $state) : array
    {
        if (! is_array($state)) {
            return [];
        }

        $tags = [];

        foreach ($state as $tag => $serviceIds) {
            if (! is_string($tag) || ! is_array($serviceIds)) {
                continue;
            }

            $values = array_values(array_unique(array_map(
                                                    static fn (mixed $value) : string => (string) $value,
                                                    $serviceIds
                                                )));
            sort($values);
            $tags[$tag] = $values;
        }

        ksort($tags);

        return $tags;
    }

    /**
     * @param mixed $state
     *
     * @return array<string, array{name: string, shared: bool, scoped: bool, transient: bool}>
     */
    private static function lifetimes(mixed $state) : array
    {
        if (! is_array($state)) {
            return [];
        }

        $plans = [];

        foreach ($state as $serviceId => $plan) {
            if (! is_string($serviceId)) {
                continue;
            }

            $plans[$serviceId] = LifetimePlan::fromArray(
                serviceId: $serviceId,
                state    : is_array($plan) ? $plan : []
            )->toArray();
        }

        ksort($plans);

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
        if (! is_array($state)) {
            return [];
        }

        $items = [];

        foreach ($state as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            $items[$key] = (bool) $value;
        }

        ksort($items);

        return $items;
    }

    /**
     * @param mixed $state
     *
     * @return array<string, int>
     */
    private static function intMap(mixed $state) : array
    {
        if (! is_array($state)) {
            return [];
        }

        $items = [];

        foreach ($state as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            $items[$key] = (int) $value;
        }

        ksort($items);

        return $items;
    }

    /**
     * @param mixed $state
     *
     * @return array<string, array<string, mixed>>
     */
    private static function mapOfMaps(mixed $state) : array
    {
        if (! is_array($state)) {
            return [];
        }

        $items = [];

        foreach ($state as $key => $value) {
            if (! is_string($key) || ! is_array($value)) {
                continue;
            }

            ksort($value);
            $items[$key] = $value;
        }

        ksort($items);

        return $items;
    }

    /**
     * @param mixed $state
     *
     * @return array<string, mixed>
     */
    private static function map(mixed $state) : array
    {
        if (! is_array($state)) {
            return [];
        }

        $items = [];

        foreach ($state as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            $items[$key] = $value;
        }

        ksort($items);

        return $items;
    }

    /**
     * @param mixed $state
     *
     * @return list<string>
     */
    private static function stringList(mixed $state) : array
    {
        if (! is_array($state)) {
            return [];
        }

        $items = array_values(array_map(
                                  static fn (mixed $value) : string => (string) $value,
                                  $state
                              ));

        sort($items);

        return array_values(array_unique($items));
    }
}
