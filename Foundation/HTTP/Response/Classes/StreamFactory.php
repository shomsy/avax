<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Classes;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Class StreamFactory
 *
 * Provides a factory for creating StreamInterface objects from various sources,
 * including strings, files, and resources.
 */
class StreamFactory implements StreamFactoryInterface
{
    /**
     * Defines the maximum allowed memory usage for the application.
     *
     * The value is set to 8KB, which balances sufficient memory allocation
     * with the need to prevent potential memory hogging or runaway processes.
     */
    private const int|float MEMORY_LIMIT = 1024 * 8; // 8KB

    /**
     * Creates a stream from a string, using `php://memory` for small data and `php://temp` for larger data.
     *
     * @param string $content Content to write to the stream.
     *
     * @throws RuntimeException
     */
    public function createStream(string $content = '') : StreamInterface
    {
        $resource = strlen(string: $content) <= self::MEMORY_LIMIT ? 'php://memory' : 'php://temp';
        $stream   = fopen(filename: $resource, mode: 'r+');

        if ($stream === false) {
            throw new RuntimeException(message: 'Failed to open stream resource: ' . $resource);
        }

        fwrite(stream: $stream, data: $content);
        rewind(stream: $stream);

        return new Stream(stream: $stream);
    }

    /**
     * Creates a new stream from a file.
     *
     * @param string $filename The file to read.
     * @param string $mode     The mode in which to open the file (e.g., 'r', 'w').
     *
     * @return StreamInterface New stream with the file content.
     */
    public function createStreamFromFile(string $filename, string $mode = 'r') : StreamInterface
    {
        $stream = fopen(filename: $filename, mode: $mode);
        if ($stream === false) {
            throw new RuntimeException(message: 'Failed to open file: ' . $filename);
        }

        return new Stream(stream: $stream);
    }

    /**
     * Creates a stream from an existing resource.
     *
     * @param resource $resource PHP resource to use as the stream.
     *
     * @return StreamInterface New stream using the given resource.
     */
    public function createStreamFromResource($resource) : StreamInterface
    {
        if (! is_resource(value: $resource)) {
            throw new RuntimeException(message: 'Invalid resource provided for stream creation.');
        }

        return new Stream(stream: $resource);
    }
}
