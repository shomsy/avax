<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection;

use RuntimeException;

/**
 * Compiled schema metadata — serializable representation of inspected DataShapes.
 *
 * Supports atomic writes, checksum validation, schema versioning, and source-change invalidation.
 */
final readonly class CompiledSchemaMetadata
{
    public const int SCHEMA_VERSION = 1;
    public const string FORMAT = 'data-transfer-schema';

    /**
     * @param string              $format         Must equal self::FORMAT
     * @param int                 $schemaVersion  Must equal self::SCHEMA_VERSION
     * @param string              $compiledAt     ISO 8601 timestamp
     * @param string              $configHash     DataTransferConfig fingerprint
     * @param string              $fingerprint    SHA1 of configHash + class list + entries
     * @param array<string, array<string, mixed>> $entries  class => serialized DataShape
     * @param array<string, int>  $sourceMtimes   class => source file mtime
     * @param string              $checksum       SHA1 of JSON body (excluding checksum itself)
     */
    public function __construct(
        public string $format,
        public int $schemaVersion,
        public string $compiledAt,
        public string $configHash,
        public string $fingerprint,
        public array $entries,
        public array $sourceMtimes,
        public string $checksum,
    ) {}

    /**
     * Compute checksum over the JSON body (without the checksum field itself).
     *
     * @param array<string, mixed> $body
     */
    public static function computeChecksum(array $body): string
    {
        $json = json_encode($body, JSON_THROW_ON_ERROR);

        return hash('sha256', $json);
    }

    /**
     * Reconstruct from array representation.
     *
     * @param array<string, mixed> $state
     * @throws RuntimeException on format or schema version mismatch
     */
    public static function fromArray(array $state): self
    {
        $format = $state['format'] ?? '';
        $schemaVersion = $state['schemaVersion'] ?? 0;

        if ($format !== self::FORMAT) {
            throw new RuntimeException(sprintf(
                'CompiledSchemaMetadata format mismatch: expected "%s", got "%s".',
                self::FORMAT,
                $format,
            ));
        }

        if ($schemaVersion !== self::SCHEMA_VERSION) {
            throw new RuntimeException(sprintf(
                'CompiledSchemaMetadata schema version mismatch: expected %d, got %d.',
                self::SCHEMA_VERSION,
                $schemaVersion,
            ));
        }

        return new self(
            format: $state['format'],
            schemaVersion: $state['schemaVersion'],
            compiledAt: $state['compiledAt'] ?? '',
            configHash: $state['configHash'] ?? '',
            fingerprint: $state['fingerprint'] ?? '',
            entries: $state['entries'] ?? [],
            sourceMtimes: $state['sourceMtimes'] ?? [],
            checksum: $state['checksum'] ?? '',
        );
    }

    /**
     * Convert to serializable array (including checksum in the body).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'format' => $this->format,
            'schemaVersion' => $this->schemaVersion,
            'compiledAt' => $this->compiledAt,
            'configHash' => $this->configHash,
            'fingerprint' => $this->fingerprint,
            'entries' => $this->entries,
            'sourceMtimes' => $this->sourceMtimes,
            'checksum' => $this->checksum,
        ];
    }

    /**
     * Serialize to JSON string.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }

    /**
     * Check if this metadata was compiled with the same config hash.
     */
    public function isValidForConfig(string $configHash): bool
    {
        return $this->configHash === $configHash;
    }

    /**
     * Check if the schema version is incompatible.
     */
    public function hasSchemaVersionMismatch(): bool
    {
        return $this->schemaVersion !== self::SCHEMA_VERSION;
    }

    /**
     * Validate checksum integrity.
     */
    public function isChecksumValid(): bool
    {
        $body = $this->toArray();
        $storedChecksum = $body['checksum'];
        unset($body['checksum']);

        return self::computeChecksum($body) === $storedChecksum;
    }
}
