<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Classes;

use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Throwable;

/**
 * PSR-7 Compliant Stream Implementation.
 *
 * Provides a stream of data, supporting readable, writable, and seekable operations.
 *
 * This class is designed to wrap a PHP stream resource and make it fully compatible
 * with PSR-7 StreamInterface, enabling interaction with HTTP messages.
 */
class Stream implements StreamInterface
{
    /** @var resource A PHP stream resource */
    private $stream;

    /** @var bool Whether the stream is readable */
    private bool $readable;

    /** @var bool Whether the stream is writable */
    private bool $writable;

    /** @var bool Whether the stream is seekable */
    private bool $seekable;

    /** @var int|null Cached size of the stream, if known */
    private int|null $size = null;

    /**
     * Stream constructor.
     *
     * @param resource $stream A valid PHP stream resource.
     *
     * Ensure that the provided stream is a valid resource and initialize its metadata.
     */
    public function __construct($stream)
    {
        $this->ensureIsResource(stream: $stream);
        $this->stream = $stream;
        $this->initializeStreamMetadata();
    }

    /**
     * Ensure the provided argument is a valid resource.
     *
     * @param mixed $stream The potential resource to validate.
     *
     * @throws RuntimeException If the argument is not a resource.
     */
    private function ensureIsResource(mixed $stream) : void
    {
        if (! is_resource(value: $stream)) {
            throw new RuntimeException(message: 'Stream must be a valid resource.');
        }
    }

    /**
     * Initialize stream metadata.
     *
     * Set the readability, writability, and seekability of the stream based on its metadata.
     */
    private function initializeStreamMetadata() : void
    {
        $meta           = stream_get_meta_data(stream: $this->stream);
        $this->seekable = $meta['seekable'] ?? false;
        $mode           = str_split(string: $meta['mode']);
        $this->readable = in_array(needle: 'r', haystack: $mode) || in_array(needle: '+', haystack: $mode);
        $this->writable = in_array(needle: 'w', haystack: $mode) || in_array(needle: 'a', haystack: $mode) || in_array(needle: '+', haystack: $mode);
    }

    /**
     * Create a Stream instance from a string.
     *
     * @param string $content The content to be written into the stream.
     *
     * @throws RuntimeException If the temporary stream cannot be opened.
     */
    public static function fromString(string $content) : self
    {
        $stream = fopen(filename: 'php://temp', mode: 'r+');
        if ($stream === false) {
            throw new RuntimeException(message: 'Failed to open temporary stream.');
        }

        fwrite(stream: $stream, data: $content);
        rewind(stream: $stream);

        return new self(stream: $stream);
    }

    /**
     * Close the stream.
     *
     * Close the stream resource and reset the metadata properties.
     */
    public function close() : void
    {
        if (is_resource(value: $this->stream)) {
            fclose(stream: $this->stream);
        }

        $this->stream   = null;
        $this->readable = false;
        $this->writable = false;
        $this->seekable = false;
    }

    /**
     * Detach the underlying stream resource.
     *
     * @return resource|null The detached stream resource, or null if none is available.
     */
    public function detach()
    {
        $stream       = $this->stream;
        $this->stream = null;

        $this->readable = false;
        $this->writable = false;
        $this->seekable = false;

        return $stream;
    }

    /**
     * Get the size of the stream, if known.
     *
     * @return int|null The size in bytes or null if unknown.
     */
    public function getSize() : int|null
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if (! $this->stream) {
            return null;
        }

        $stats = fstat(stream: $this->stream);

        return $stats['size'] ?? null;
    }

    /**
     * Get the current position of the stream.
     *
     * @return int The current position of the stream.
     *
     * @throws RuntimeException If the position cannot be determined.
     */
    public function tell() : int
    {
        $this->ensureStreamIsOpen();
        $position = ftell(stream: $this->stream);
        if ($position === false) {
            throw new RuntimeException(message: 'Unable to determine the position of the stream.');
        }

        return $position;
    }

    /**
     * Ensure the stream is open.
     *
     * @throws RuntimeException If the stream is not open.
     */
    private function ensureStreamIsOpen() : void
    {
        if (! $this->stream) {
            throw new RuntimeException(message: 'Stream is not open.');
        }
    }

    /**
     * Check if the stream is at end-of-file.
     *
     * @return bool True if at end-of-file, false otherwise.
     */
    public function eof() : bool
    {
        return ! $this->stream || feof(stream: $this->stream);
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The data to write.
     *
     * @return int The number of bytes written.
     *
     * @throws RuntimeException If the stream is not writable or the write fails.
     */
    public function write(string $string) : int
    {
        if (! $this->isWritable()) {
            throw new RuntimeException(message: 'Stream is not writable.');
        }

        $result = fwrite(stream: $this->stream, data: $string);
        if ($result === false) {
            throw new RuntimeException(message: 'Failed to write to the stream.');
        }

        $this->size = null;

        return $result;
    }

    /**
     * Check if the stream is writable.
     *
     * @return bool True if the stream is writable, false otherwise.
     */
    public function isWritable() : bool
    {
        return $this->writable;
    }

    /**
     * Read data from the stream.
     *
     * @param int $length The maximum number of bytes to read.
     *
     * @return string The data read from the stream.
     *
     * @throws RuntimeException If the stream is not readable or the read fails.
     */
    public function read(int $length) : string
    {
        $this->ensureStreamIsOpen();

        if (! $this->isReadable()) {
            throw new StreamNotReadableException(message: 'Attempted to read from a non-readable stream.');
        }

        $result = fread(stream: $this->stream, length: $length);
        if ($result === false) {
            throw new RuntimeException(message: 'Failed to read from the stream.');
        }

        return $result;
    }

    /**
     * Check if the stream is readable.
     *
     * @return bool True if the stream is readable, false otherwise.
     */
    public function isReadable() : bool
    {
        return $this->readable;
    }

    /**
     * Retrieve the stream metadata.
     *
     * @param string|null $key Optional metadata key to retrieve.
     *
     * @return mixed The metadata value if $key is specified, or an associative array if $key is null.
     */
    public function getMetadata(string|null $key = null) : mixed
    {
        if (! $this->stream) {
            return $key !== null && $key !== '' && $key !== '0' ? null : [];
        }

        $meta = stream_get_meta_data(stream: $this->stream);

        return $key === null ? $meta : ($meta[$key] ?? null);
    }

    /**
     * Convert the stream to a string.
     *
     * @return string The entire content of the stream, or an empty string on error.
     */
    public function __toString() : string
    {
        if (! $this->stream) {
            return '';
        }

        try {
            if ($this->isSeekable()) {
                $this->rewind();
            }

            return $this->getContents();
        } catch (Throwable $throwable) {
            throw new RuntimeException(
                message : 'Unable to read the stream.',
                code    : $throwable->getCode(),
                previous: $throwable,
            );
        }
    }

    /**
     * Check if the stream is seekable.
     *
     * @return bool True if the stream is seekable, false otherwise.
     */
    public function isSeekable() : bool
    {
        return $this->seekable;
    }

    /**
     * Rewind the stream to the beginning.
     */
    public function rewind() : void
    {
        $this->seek(offset: 0);
    }

    /**
     * Seek to a position within the stream.
     *
     * @param int $offset The stream offset to seek to.
     * @param int $whence The seek method (SEEK_SET, SEEK_CUR, SEEK_END).
     *
     * @throws RuntimeException If the stream is not seekable or the seek operation fails.
     */
    public function seek(int $offset, int $whence = SEEK_SET) : void
    {
        if (! $this->isSeekable()) {
            throw new RuntimeException(message: 'Stream is not seekable.');
        }

        if (fseek(stream: $this->stream, offset: $offset, whence: $whence) === -1) {
            throw new RuntimeException(message: 'Failed to seek within the stream.');
        }
    }

    /**
     * Get the remaining contents of the stream.
     *
     * @return string The remaining contents of the stream.
     *
     * @throws RuntimeException If the stream is not readable or the read fails.
     */
    public function getContents() : string
    {
        if (! $this->isReadable()) {
            throw new RuntimeException(message: 'Stream is not readable.');
        }

        $contents = stream_get_contents(stream: $this->stream);
        if ($contents === false) {
            throw new RuntimeException(message: 'Failed to get contents of the stream.');
        }

        return $contents;
    }
}
