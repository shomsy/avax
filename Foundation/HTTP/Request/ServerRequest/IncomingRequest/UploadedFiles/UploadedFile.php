<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\IncomingHttp\IncomingRequest\UploadedFiles;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

/**
 * Unit: Concrete implementation of a PSR-7 uploaded file.
 */
final class UploadedFile implements UploadedFileInterface
{
    private bool                     $moved = false;
    private readonly string|null     $clientMediaType;
    private readonly string|null     $clientFilename;
    private readonly int             $error;
    private readonly int|null        $size;
    private readonly StreamInterface $stream;

    public function __construct(
        StreamInterface $stream,
        int|null        $size,
        int             $error,
        string|null     $clientFilename = null,
        string|null     $clientMediaType = null
    )
    {
        $this->stream          = $stream;
        $this->size            = $size;
        $this->error           = $error;
        $this->clientFilename  = $clientFilename;
        $this->clientMediaType = $clientMediaType;
    }

    public function getStream() : StreamInterface
    {
        if ($this->moved) {
            throw new RuntimeException(message: 'File already moved');
        }

        return $this->stream;
    }

    public function moveTo(string $targetPath) : void
    {
        if ($this->moved) {
            throw new RuntimeException(message: 'File already moved');
        }

        if ($targetPath === '') {
            throw new InvalidArgumentException(message: 'Invalid target path');
        }

        // Standard PHP move_uploaded_file behavior is expected here.
        // For our implementation, we'll cast a note that this is the seam.
        if (PHP_SAPI === 'cli') {
            // In CLI context we just write the stream
            file_put_contents($targetPath, $this->stream->getContents());
        } else {
            // Actual SAPI integration
            if (! move_uploaded_file($this->stream->getMetadata(key: 'uri'), $targetPath)) {
                throw new RuntimeException(message: 'Failed to move uploaded file');
            }
        }

        $this->moved = true;

        // Technically, a readonly class cannot have mutable properties like $moved.
        // PSR-7 UploadedFileInterface usually implies state. 
        // We'll treat this as a simplified model for the rewrite.
    }

    public function getSize() : int|null
    {
        return $this->size;
    }

    public function getError() : int
    {
        return $this->error;
    }

    public function getClientFilename() : string|null
    {
        return $this->clientFilename;
    }

    public function getClientMediaType() : string|null
    {
        return $this->clientMediaType;
    }
}
