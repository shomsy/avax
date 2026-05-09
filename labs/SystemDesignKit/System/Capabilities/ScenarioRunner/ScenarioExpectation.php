<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\ScenarioRunner;

/**
 * Expected outcome assertion within a V3 scenario.
 *
 * @experimental V3 labs
 */
final readonly class ScenarioExpectation
{
    public function __construct(
        public string $assertion,
        public mixed  $threshold = null,
    ) {}

    /**
     * @param array<int|string, mixed> $config
     */
    public static function fromConfig(array $config) : self
    {
        return new self(
            assertion: (string) ($config['assertion'] ?? 'unknown'),
            threshold: $config['threshold'] ?? null,
        );
    }
}
