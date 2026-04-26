<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Streams;

use Psr\Http\Message\StreamInterface;

final class CreateStreamFromString
{
    public function __invoke(string $content = '') : StreamInterface
    {
        $stream = new CreateTemporaryStream()(estimatedSize: strlen(string: $content));
        $stream->write(string: $content);
        $stream->rewind();

        return $stream;
    }
}
