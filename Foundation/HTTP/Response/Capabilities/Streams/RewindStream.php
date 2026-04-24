<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Streams;

use Psr\Http\Message\StreamInterface;

final class RewindStream
{
    public function __invoke(StreamInterface $stream) : StreamInterface
    {
        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        return $stream;
    }
}
