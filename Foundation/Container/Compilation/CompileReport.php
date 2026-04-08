<?php

declare(strict_types=1);

namespace Avax\Container\Compilation;

use JsonException;
use JsonSerializable;

/**
 * Stable machine-readable compile/runtime artifact report.
 */
final readonly class CompileReport implements JsonSerializable
{
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
        public string $path,
        public string $metadataPath,
        public string $cacheVersion,
        public string $compileMode,
        public string $environment,
        public string $fingerprint,
        public bool $checksumValid,
        public array $entries,
        public array $changedServices,
        public array $invalidatedServices,
        public array $validationIssues,
        public array $invalidationReasons,
        public array $statistics,
        public ArtifactMetadata|null $metadata = null
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return [
            'available' => $this->available,
            'path' => $this->path,
            'metadataPath' => $this->metadataPath,
            'cacheVersion' => $this->cacheVersion,
            'compileMode' => $this->compileMode,
            'environment' => $this->environment,
            'fingerprint' => $this->fingerprint,
            'checksumValid' => $this->checksumValid,
            'serviceCount' => count($this->entries),
            'entries' => $this->entries,
            'changedServices' => $this->changedServices,
            'invalidatedServices' => $this->invalidatedServices,
            'validationIssues' => $this->validationIssues,
            'invalidationReasons' => $this->invalidationReasons,
            'statistics' => $this->statistics,
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
