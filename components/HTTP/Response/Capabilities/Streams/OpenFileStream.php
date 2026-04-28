<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Capabilities\Streams;

use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

final class OpenFileStream
{
    public function __invoke(string $path, string $mode = 'r') : StreamInterface
    {
        $handle = fopen(filename: $path, mode: $mode);

        if ($handle === false) {
            throw new RuntimeException(message: "Unable to open file stream [{$path}] using mode [{$mode}].");
        }

        return new Stream(stream: $handle);
    }
}
