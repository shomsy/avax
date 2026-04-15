<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestBody;

use psr\http\message\StreamInterface;

/**
 * Capability Owner: Manages the raw request body stream.
 */
final readonly class RequestBody
{

    public function __construct(private StreamInterface $stream) {}

    public function stream() : StreamInterface
    {
        return $this->stream;
    }

    public function content() : string
    {
        if ($this->stream->isSeekable()) {
            $this->stream->rewind();
        }

        return $this->stream->getContents();
    }
}
