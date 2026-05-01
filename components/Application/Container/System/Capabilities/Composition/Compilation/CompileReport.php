<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Composition\Compilation;

use JsonException;
use JsonSerializable;

/**
 * Stable machine-readable compile/runtime artifact report.
 */
final readonly class CompileReport implements JsonSerializable
{
    public const int SCHEMA_VERSION = 2;

    public ?ArtifactMetadata $metadata;

    public array $pruning;

    public array $statistics;

    public array $invalidationReasons;

    public array $compatibilityIssues;

    public array $validationIssues;

    public array $invalidatedServices;

    public array $changedServices;

    public array $entries;

    public array $lifetimePlanSummary;

    public int $providerBootPlanSize;

    public int $decorationMapSize;

    public int $aliasMapSize;

    public int $tagIndexSize;

    public int $lazyServicesCount;

    public int $deferredServicesCount;

    public int $invalidatedServicesCount;

    public int $reusedServicesCount;

    public int $compiledServicesCount;

    public int $totalServices;

    public bool $checksumValid;

    public string $fingerprint;

    public string $environment;

    public string $pruneMode;

    public string $executionMode;

    public string $compileMode;

    public string $cacheVersion;

    public string $metadataPath;

    public string $path;

    public array $warnings;

    public string $freshnessState;

    public bool $compatible;

    public bool $available;

    /**
     * @param list<string> $entries
     * @param list<string> $changedServices
     * @param list<string> $invalidatedServices
     * @param list<string> $validationIssues
     * @param list<string> $invalidationReasons
     * @param array<string, int> $statistics
     */
    public function __construct(
        bool $available,
        bool $compatible,
        string $freshnessState,
        array $warnings,
        string $path,
        string $metadataPath,
        string $cacheVersion,
        string $compileMode,
        string $executionMode,
        string $pruneMode,
        string $environment,
        string $fingerprint,
        bool $checksumValid,
        int $totalServices,
        int $compiledServicesCount,
        int $reusedServicesCount,
        int $invalidatedServicesCount,
        int $deferredServicesCount,
        int $lazyServicesCount,
        int $tagIndexSize,
        int $aliasMapSize,
        int $decorationMapSize,
        int $providerBootPlanSize,
        array $lifetimePlanSummary,
        array $entries,
        array $changedServices,
        array $invalidatedServices,
        array $validationIssues,
        array $compatibilityIssues,
        array $invalidationReasons,
        array $statistics,
        array $pruning,
        ArtifactMetadata $metadata = null,
    ) {
        $this->available                = $available;
        $this->compatible               = $compatible;
        $this->freshnessState           = $freshnessState;
        $this->warnings                 = $warnings;
        $this->path                     = $path;
        $this->metadataPath             = $metadataPath;
        $this->cacheVersion             = $cacheVersion;
        $this->compileMode              = $compileMode;
        $this->executionMode            = $executionMode;
        $this->pruneMode                = $pruneMode;
        $this->environment              = $environment;
        $this->fingerprint              = $fingerprint;
        $this->checksumValid            = $checksumValid;
        $this->totalServices            = $totalServices;
        $this->compiledServicesCount    = $compiledServicesCount;
        $this->reusedServicesCount      = $reusedServicesCount;
        $this->invalidatedServicesCount = $invalidatedServicesCount;
        $this->deferredServicesCount    = $deferredServicesCount;
        $this->lazyServicesCount        = $lazyServicesCount;
        $this->tagIndexSize             = $tagIndexSize;
        $this->aliasMapSize             = $aliasMapSize;
        $this->decorationMapSize        = $decorationMapSize;
        $this->providerBootPlanSize     = $providerBootPlanSize;
        $this->lifetimePlanSummary      = $lifetimePlanSummary;
        $this->entries                  = $entries;
        $this->changedServices          = $changedServices;
        $this->invalidatedServices      = $invalidatedServices;
        $this->validationIssues         = $validationIssues;
        $this->compatibilityIssues      = $compatibilityIssues;
        $this->invalidationReasons      = $invalidationReasons;
        $this->statistics               = $statistics;
        $this->pruning                  = $pruning;
        $this->metadata                 = $metadata;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'schemaVersion'            => self::SCHEMA_VERSION,
            'available'                => $this->available,
            'compatible'               => $this->compatible,
            'freshnessState'           => $this->freshnessState,
            'warnings'                 => $this->warnings,
            'path'                     => $this->path,
            'metadataPath'             => $this->metadataPath,
            'cacheVersion'             => $this->cacheVersion,
            'compileMode'              => $this->compileMode,
            'executionMode'            => $this->executionMode,
            'pruneMode'                => $this->pruneMode,
            'environment'              => $this->environment,
            'fingerprint'              => $this->fingerprint,
            'checksumValid'            => $this->checksumValid,
            'totalServices'            => $this->totalServices,
            'compiledServicesCount'    => $this->compiledServicesCount,
            'reusedServicesCount'      => $this->reusedServicesCount,
            'invalidatedServicesCount' => $this->invalidatedServicesCount,
            'deferredServicesCount'    => $this->deferredServicesCount,
            'lazyServicesCount'        => $this->lazyServicesCount,
            'tagIndexSize'             => $this->tagIndexSize,
            'aliasMapSize'             => $this->aliasMapSize,
            'decorationMapSize'        => $this->decorationMapSize,
            'providerBootPlanSize'     => $this->providerBootPlanSize,
            'lifetimePlanSummary'      => $this->lifetimePlanSummary,
            'serviceCount'             => count(value: $this->entries),
            'entries'                  => $this->entries,
            'changedServices'          => $this->changedServices,
            'invalidatedServices'      => $this->invalidatedServices,
            'validationIssues'         => $this->validationIssues,
            'compatibilityIssues'      => $this->compatibilityIssues,
            'invalidationReasons'      => $this->invalidationReasons,
            'statistics'               => $this->statistics,
            'pruning'                  => $this->pruning,
            'metadata'                 => $this->metadata?->toArray(),
        ];
    }

    public function toJson(): string
    {
        try {
            return (string) json_encode(value: $this, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return '{}';
        }
    }
}
