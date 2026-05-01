<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\TracingTimeline;

/**
 * Represents a single event in the request timeline.
 */
final readonly class RuntimeEvent
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $name,
        public float  $timestampMS,
        public ?float $durationMS = null,
        public ?string $category = null,
        public array  $metadata = [],
    ) {}

    /**
     * @param array<string, mixed> $metadata
     */
    public static function make(
        string $name,
        ?float  $durationMS = null,
        ?string $category = null,
        array  $metadata = [],
    ) : self
    {
        return new self(
            name       : $name,
            timestampMS: microtime(true) * 1000,
            durationMS : $durationMS,
            category   : $category,
            metadata   : $metadata,
        );
    }
}
