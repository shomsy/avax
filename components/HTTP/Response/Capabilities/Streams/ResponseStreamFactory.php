<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Capabilities\Streams;

use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

final class ResponseStreamFactory
{
    public function createEmptyStream() : StreamInterface
    {
        return new CreateTemporaryStream()();
    }

    public function createStreamFromString(string $content = '') : StreamInterface
    {
        return new CreateStreamFromString()(content: $content);
    }

    public function createStreamFromResource(mixed $resource) : StreamInterface
    {
        if (! is_resource(value: $resource)) {
            throw new RuntimeException(message: 'Response streams can only be created from valid resources.');
        }

        return new Stream(stream: $resource);
    }

    public function openFileStream(string $path, string $mode = 'r') : StreamInterface
    {
        return new OpenFileStream()(path: $path, mode: $mode);
    }
}
