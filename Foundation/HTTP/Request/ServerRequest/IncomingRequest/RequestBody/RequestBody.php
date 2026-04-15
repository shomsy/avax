<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestBody;

use Psr\Http\Message\StreamInterface;

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
        if (!$this->stream->isSeekable()) {
            return $this->stream->getContents();
        }

        $originalPosition = $this->stream->tell();
        $this->stream->rewind();
        $content = $this->stream->getContents();
        $this->stream->seek($originalPosition);

        return $content;
    }
}
