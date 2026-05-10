<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RuntimeAdapters;

/**
 * RoadRunner adapter — ROADMAP until real dependency exists.
 */
final readonly class RoadRunnerAdapter implements RuntimeAdapter
{
    public function name(): string
    {
        return 'roadrunner';
    }

    public function isAvailable(): bool
    {
        return class_exists(\Spiral\RoadRunner\Http\PSR7Worker::class);
    }

    public function capabilities(): array
    {
        if (!$this->isAvailable()) {
            return [
                'http_server' => false,
                'async_io' => false,
                'streaming' => false,
                'websockets' => false,
            ];
        }

        return [
            'http_server' => true,
            'async_io' => false,
            'streaming' => true,
            'websockets' => false,
        ];
    }
}
