<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading;

use RuntimeException;

/**
 * CompiledAttributeMetadata — serializable attribute metadata for any class.
 *
 * Unlike CompiledSchemaMetadata (which is DataShape-specific), this model
 * captures class-level and property-level attributes for any PHP class.
 *
 * Used by ORM, Validation, and Container to avoid per-request reflection.
 */
final readonly class CompiledAttributeMetadata
{
    public const int    METADATA_VERSION = 1;
    public const string FORMAT           = 'attribute-metadata';

    /**
     * @param string                              $format             Must equal FORMAT constant
     * @param int                                 $metadataVersion    Must equal METADATA_VERSION constant
     * @param string                              $compiledAt         ISO-8601 timestamp
     * @param string                              $configHash         Hash identifying the configuration context
     * @param string                              $className          Fully qualified class name
     * @param array<string, mixed>                $classAttributes    Class-level attributes (short name => args)
     * @param array<string, array<string, mixed>> $propertyAttributes Property-level attributes (property name =>
     *                                    [short name => args])
     * @param array<string, mixed>                $methodAttributes   Method-level attributes (method name => [short
     *                                            name => args])
     * @param int                                 $sourceMtime        Source file mtime at compilation time
     * @param string                              $checksum           SHA-256 over JSON body (excluding checksum
     *                                                                itself)
     */
    public function __construct(
        public string $format,
        public int    $metadataVersion,
        public string $compiledAt,
        public string $configHash,
        public string $className,
        public array  $classAttributes,
        public array  $propertyAttributes,
        public array  $methodAttributes,
        public int    $sourceMtime,
        public string $checksum,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data) : self
    {
        $required = ['format', 'metadataVersion', 'compiledAt', 'configHash', 'className', 'classAttributes', 'propertyAttributes', 'methodAttributes', 'sourceMtime', 'checksum'];
        foreach ($required as $key) {
            if (! array_key_exists($key, $data)) {
                throw new RuntimeException("Missing required field: {$key}");
            }
        }

        return new self(
            format            : (string) $data['format'],
            metadataVersion   : (int) $data['metadataVersion'],
            compiledAt        : (string) $data['compiledAt'],
            configHash        : (string) $data['configHash'],
            className         : (string) $data['className'],
            classAttributes   : (array) $data['classAttributes'],
            propertyAttributes: (array) $data['propertyAttributes'],
            methodAttributes  : (array) $data['methodAttributes'],
            sourceMtime       : (int) $data['sourceMtime'],
            checksum          : (string) $data['checksum'],
        );
    }

    public function toJson() : string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return [
            'format'             => $this->format,
            'metadataVersion'    => $this->metadataVersion,
            'compiledAt'         => $this->compiledAt,
            'configHash'         => $this->configHash,
            'className'          => $this->className,
            'classAttributes'    => $this->classAttributes,
            'propertyAttributes' => $this->propertyAttributes,
            'methodAttributes'   => $this->methodAttributes,
            'sourceMtime'        => $this->sourceMtime,
            'checksum'           => $this->checksum,
        ];
    }

    public function isValidForConfig(string $configHash) : bool
    {
        return $this->configHash === $configHash;
    }

    public function hasVersionMismatch() : bool
    {
        return $this->metadataVersion !== self::METADATA_VERSION;
    }

    public function isChecksumValid() : bool
    {
        $body = $this->toArray();
        unset($body['checksum']);

        return self::computeChecksum($body) === $this->checksum;
    }

    /**
     * Compute checksum over the metadata body (excluding checksum field).
     *
     * @param array<string, mixed> $body
     */
    public static function computeChecksum(array $body) : string
    {
        $json = json_encode($body, JSON_THROW_ON_ERROR);

        return hash('sha256', $json);
    }
}
