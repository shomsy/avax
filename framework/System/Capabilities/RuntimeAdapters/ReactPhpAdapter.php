<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RuntimeAdapters;

final readonly class ReactPhpAdapter implements RuntimeAdapter
{
    public function name(): string
    {
        return 'reactphp';
    }

    public function isAvailable(): bool
    {
        return class_exists(\React\EventLoop\Loop::class) || function_exists('stream_select');
    }

    public function capabilities(): array
    {
        return [
            'http_server' => $this->isAvailable(),
            'async_io' => $this->isAvailable(),
            'streaming' => true,
            'websockets' => false,
        ];
    }
}
