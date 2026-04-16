<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody;

use NoDiscard;
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

    #[NoDiscard]
    public function content() : string
    {
        if (! $this->stream) {
            return $this->stream->getContents();
        }

        $originalPosition = $this->stream->tell();
        $this->stream->rewind();
        $content = $this->stream->getContents();
        $this->stream->seek(offset: $originalPosition);

        return $content;
    }
}
