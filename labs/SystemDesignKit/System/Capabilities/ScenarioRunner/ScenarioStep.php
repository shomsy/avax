<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\ScenarioRunner;

/**
 * A single step within a V3 scenario.
 *
 * @experimental V3 labs
 */
final readonly class ScenarioStep
{
    public function __construct(
        public string  $action,
        public ?string $target = null,
        public mixed   $parameter = null,
    ) {}

    /**
     * @param array<int|string, mixed> $config
     */
    public static function fromConfig(array $config) : self
    {
        return new self(
            action   : (string) ($config['action'] ?? 'unknown'),
            target   : isset($config['target']) ? (string) $config['target'] : null,
            parameter: $config['parameter'] ?? null,
        );
    }
}
