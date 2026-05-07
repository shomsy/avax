<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Configuration;

/**
 * Configuration for the SystemDesignKit capacity engine.
 *
 * @experimental V3 labs
 */
final readonly class CapacityEngineConfiguration
{
    public function __construct(
        public string $schemaDir = '',
        public bool   $strictMode = true,
    ) {}

    /**
     * @param array<int|string, mixed> $config
     */
    public static function fromArray(array $config) : self
    {
        return new self(
            schemaDir : (string) ($config['schema_dir'] ?? ''),
            strictMode: (bool) ($config['strict_mode'] ?? true),
        );
    }
}
