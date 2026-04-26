<?php

declare(strict_types=1);

namespace components\Container\DI\Capabilities\Diagnostics\Observability;

use components\Container\DI\Capabilities\Composition\Compilation\CompileReport;
use JsonException;
use JsonSerializable;

/**
 * Stable machine-readable runtime state report.
 */
final readonly class RuntimeReport implements JsonSerializable
{
    public const int SCHEMA_VERSION = 4;
    public CompileReport|null $compiled;
    public array              $hotPath;
    public array              $scopes;
    public array              $timeline;
    public array              $metrics;
    public int                $scopedServiceCount;
    public int                $sharedServiceCount;
    public array              $deferredProviders;
    public array              $aliases;
    public array              $lazyServices;
    public bool               $timelineEnabled;
    public string             $diagnosticsMode;
    public string             $sliceBoundaryMode;
    public string             $asyncTarget;
    public string             $executionMode;
    public bool               $warmedUp;
    public bool               $compiledAttached;
    public int                $compiledRevision;
    public int                $registrationRevision;

    /**
     * @param list<string>                                                                 $lazyServices
     * @param array<string, string>                                                        $aliases
     * @param array<string, string>                                                        $deferredProviders
     * @param array<string, int>                                                           $metrics
     * @param list<array{time: float, action: string, serviceId: string, outcome: string}> $timeline
     * @param array{
     *     shared: array<string, string>,
     *     scopedDepth: int,
     *     scoped: array<int, array<string, string>>,
     *     pooled: array<string, list<string>>,
     *     pooledAvailable: array<string, list<string>>,
     *     pooledStats: array<string, int>,
     *     frames: array<int, array{kind: string, id: string, services: list<string>, pooledServices: list<string>}>
     * }                                                                                   $scopes
     */
    public function __construct(
        int                $registrationRevision,
        int                $compiledRevision,
        bool               $compiledAttached,
        bool               $warmedUp,
        string             $executionMode,
        string             $asyncTarget,
        string             $sliceBoundaryMode,
        string             $diagnosticsMode,
        bool               $timelineEnabled,
        array              $lazyServices,
        array              $aliases,
        array              $deferredProviders,
        int                $sharedServiceCount,
        int                $scopedServiceCount,
        array              $metrics,
        array              $timeline,
        array              $scopes,
        array              $hotPath,
        CompileReport|null $compiled = null
    )
    {
        $this->registrationRevision = $registrationRevision;
        $this->compiledRevision     = $compiledRevision;
        $this->compiledAttached     = $compiledAttached;
        $this->warmedUp             = $warmedUp;
        $this->executionMode        = $executionMode;
        $this->asyncTarget          = $asyncTarget;
        $this->sliceBoundaryMode    = $sliceBoundaryMode;
        $this->diagnosticsMode      = $diagnosticsMode;
        $this->timelineEnabled      = $timelineEnabled;
        $this->lazyServices         = $lazyServices;
        $this->aliases              = $aliases;
        $this->deferredProviders    = $deferredProviders;
        $this->sharedServiceCount   = $sharedServiceCount;
        $this->scopedServiceCount   = $scopedServiceCount;
        $this->metrics              = $metrics;
        $this->timeline             = $timeline;
        $this->scopes               = $scopes;
        $this->hotPath              = $hotPath;
        $this->compiled             = $compiled;
    }

    public function jsonSerialize() : array
    {
        return $this->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return [
            'schemaVersion'        => self::SCHEMA_VERSION,
            'registrationRevision' => $this->registrationRevision,
            'compiledRevision'     => $this->compiledRevision,
            'compiledAttached'     => $this->compiledAttached,
            'warmedUp'             => $this->warmedUp,
            'executionMode'        => $this->executionMode,
            'asyncTarget'          => $this->asyncTarget,
            'sliceBoundaryMode'    => $this->sliceBoundaryMode,
            'diagnosticsMode'      => $this->diagnosticsMode,
            'timelineEnabled'      => $this->timelineEnabled,
            'lazyServices'         => $this->lazyServices,
            'aliases'              => $this->aliases,
            'deferredProviders'    => $this->deferredProviders,
            'sharedServiceCount'   => $this->sharedServiceCount,
            'scopedServiceCount'   => $this->scopedServiceCount,
            'metrics'              => $this->metrics,
            'timeline'             => $this->timeline,
            'scopes'               => $this->scopes,
            'hotPath'              => $this->hotPath,
            'compiled'             => $this->compiled?->toArray(),
        ];
    }

    public function toJson() : string
    {
        try {
            return (string) json_encode(value: $this, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return '{}';
        }
    }
}
