<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Composition\Compilation;

use JsonException;
use JsonSerializable;

/**
 * Stable machine-readable compile/runtime artifact report.
 */
final readonly class CompileReport implements JsonSerializable
{
    public const SCHEMA_VERSION = 2;

    /**
     * @param list<string> $entries
     * @param list<string> $changedServices
     * @param list<string> $invalidatedServices
     * @param list<string> $validationIssues
     * @param list<string> $invalidationReasons
     * @param array<string, int> $statistics
     */
    public function __construct(
        public bool $available,
        public bool $compatible,
        public string $freshnessState,
        public array $warnings,
        public string $path,
        public string $metadataPath,
        public string $cacheVersion,
        public string $compileMode,
        public string $executionMode,
        public string $pruneMode,
        public string $environment,
        public string $fingerprint,
        public bool $checksumValid,
        public int $totalServices,
        public int $compiledServicesCount,
        public int $reusedServicesCount,
        public int $invalidatedServicesCount,
        public int $deferredServicesCount,
        public int $lazyServicesCount,
        public int $tagIndexSize,
        public int $aliasMapSize,
        public int $decorationMapSize,
        public int $providerBootPlanSize,
        public array $lifetimePlanSummary,
        public array $entries,
        public array $changedServices,
        public array $invalidatedServices,
        public array $validationIssues,
        public array $compatibilityIssues,
        public array $invalidationReasons,
        public array $statistics,
        public array $pruning,
        public ArtifactMetadata|null $metadata = null
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return [
            'schemaVersion' => self::SCHEMA_VERSION,
            'available' => $this->available,
            'compatible' => $this->compatible,
            'freshnessState' => $this->freshnessState,
            'warnings' => $this->warnings,
            'path' => $this->path,
            'metadataPath' => $this->metadataPath,
            'cacheVersion' => $this->cacheVersion,
            'compileMode' => $this->compileMode,
            'executionMode' => $this->executionMode,
            'pruneMode' => $this->pruneMode,
            'environment' => $this->environment,
            'fingerprint' => $this->fingerprint,
            'checksumValid' => $this->checksumValid,
            'totalServices' => $this->totalServices,
            'compiledServicesCount' => $this->compiledServicesCount,
            'reusedServicesCount' => $this->reusedServicesCount,
            'invalidatedServicesCount' => $this->invalidatedServicesCount,
            'deferredServicesCount' => $this->deferredServicesCount,
            'lazyServicesCount' => $this->lazyServicesCount,
            'tagIndexSize' => $this->tagIndexSize,
            'aliasMapSize' => $this->aliasMapSize,
            'decorationMapSize' => $this->decorationMapSize,
            'providerBootPlanSize' => $this->providerBootPlanSize,
            'lifetimePlanSummary' => $this->lifetimePlanSummary,
            'serviceCount' => count($this->entries),
            'entries' => $this->entries,
            'changedServices' => $this->changedServices,
            'invalidatedServices' => $this->invalidatedServices,
            'validationIssues' => $this->validationIssues,
            'compatibilityIssues' => $this->compatibilityIssues,
            'invalidationReasons' => $this->invalidationReasons,
            'statistics' => $this->statistics,
            'pruning' => $this->pruning,
            'metadata' => $this->metadata?->toArray(),
        ];
    }

    public function jsonSerialize() : array
    {
        return $this->toArray();
    }

    public function toJson() : string
    {
        try {
            return (string) json_encode($this, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return '{}';
        }
    }
}
