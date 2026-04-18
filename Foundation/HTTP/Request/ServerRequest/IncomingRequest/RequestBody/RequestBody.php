<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody;

use Psr\Http\Message\StreamInterface;

/**
 * RequestBody - State owner for the raw request body stream.
 *
 * Contract:
 * - content() reads the full stream content regardless of current cursor position
 * - content() restores cursor to its original position after reading (for seekable streams)
 * - content() is safe to call multiple times
 * - stream() returns the raw PSR-7 StreamInterface
 */
final readonly class RequestBody
{
    public function __construct(
        private StreamInterface $stream
    ) {}

    public function stream(): StreamInterface
    {
        return $this->stream;
    }

    /**
     * Read the full body content.
     *
     * Preserves cursor position for seekable streams.
     * Safe for repeated reads.
     */
    public function content(): string
    {
        if ($this->stream->isSeekable()) {
            $originalPosition = $this->stream->tell();
            $this->stream->rewind();
            $content = $this->stream->getContents();
            $this->stream->seek($originalPosition);

            return $content;
        }

        return $this->stream->getContents();
    }
}
