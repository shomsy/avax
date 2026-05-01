<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability;

use Avax\Components\Application\Container\System\Capabilities\Composition\Compilation\CompileReport;
use JsonException;
use JsonSerializable;
use Override;

/**
 * Stable machine-readable runtime state report.
 */
final readonly class RuntimeReport implements JsonSerializable
{
    public const int SCHEMA_VERSION = 4;

    /**
     * @param list<string>          $lazyServices
     * @param array<string, string> $aliases
     * @param array<string, string> $deferredProviders
     * @param array<string, int>    $metrics
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
    public function __construct(public int $registrationRevision, public int $compiledRevision, public bool $compiledAttached, public bool $warmedUp, public string $executionMode, public string $asyncTarget, public string $sliceBoundaryMode, public string $diagnosticsMode, public bool $timelineEnabled, public array $lazyServices, public array $aliases, public array $deferredProviders, public int $sharedServiceCount, public int $scopedServiceCount, public array $metrics, public array $timeline, public array $scopes, public array $hotPath, public ?CompileReport $compileReport = null) {}

    #[Override]
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
            'schemaVersion'      => self::SCHEMA_VERSION,
            'registrationRevision' => $this->registrationRevision,
            'compiledRevision'   => $this->compiledRevision,
            'compiledAttached'   => $this->compiledAttached,
            'warmedUp'           => $this->warmedUp,
            'executionMode'      => $this->executionMode,
            'asyncTarget'        => $this->asyncTarget,
            'sliceBoundaryMode'  => $this->sliceBoundaryMode,
            'diagnosticsMode'    => $this->diagnosticsMode,
            'timelineEnabled'    => $this->timelineEnabled,
            'lazyServices'       => $this->lazyServices,
            'aliases'            => $this->aliases,
            'deferredProviders'  => $this->deferredProviders,
            'sharedServiceCount' => $this->sharedServiceCount,
            'scopedServiceCount' => $this->scopedServiceCount,
            'metrics'            => $this->metrics,
            'timeline'           => $this->timeline,
            'scopes'             => $this->scopes,
            'hotPath'            => $this->hotPath,
            'compiled'           => $this->compileReport?->toArray(),
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
