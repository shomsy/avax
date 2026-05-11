<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RuntimeBoundary;

use React\EventLoop\Loop;

final readonly class ReactPhpAdapter implements RuntimeAdapter
{
    public function name() : string
    {
        return 'reactphp';
    }

    public function capabilities() : array
    {
        return [
            'http_server' => $this->isAvailable(),
            'async_io'    => $this->isAvailable(),
            'streaming'   => true,
            'websockets'  => false,
        ];
    }

    public function isAvailable() : bool
    {
        return class_exists(Loop::class) || function_exists('stream_select');
    }
}
