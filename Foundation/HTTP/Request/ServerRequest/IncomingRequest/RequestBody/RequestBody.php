<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody;

use Psr\Http\Message\StreamInterface;

/**
 * RequestBody
 *
 * State owner for the raw request body stream.
 *
 * Contract:
 * - stream() returns the underlying PSR-7 stream
 * - content() reads the full body content
 * - for seekable streams, content() restores the original cursor position
 * - for non-seekable streams, content() reads from the current cursor position
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
        $stream = $this->stream;

        if (! $stream->isSeekable()) {
            return $stream->getContents();
        }

        $originalPosition = $stream->tell();

        try {
            $stream->rewind();

            return $stream->getContents();
        } finally {
            $stream->seek(offset: $originalPosition);
        }
    }
}