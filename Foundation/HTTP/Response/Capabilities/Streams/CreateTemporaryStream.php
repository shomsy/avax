<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Streams;

use GuzzleHttp\Psr7\Stream;
use http\Encoding\Stream;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

final class CreateTemporaryStream
{
    private const int MEMORY_LIMIT = 8192;

    public function __invoke(int $estimatedSize = 0) : StreamInterface
    {
        $uri    = $estimatedSize > self::MEMORY_LIMIT ? 'php://temp' : 'php://memory';
        $handle = fopen(filename: $uri, mode: 'r+');

        if ($handle === false) {
            throw new RuntimeException(message: "Unable to open temporary stream [{$uri}].");
        }

        return new Stream(stream: $handle);
    }
}
