<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles;

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

        if ($this->error !== UPLOAD_ERR_OK) {
            throw new RuntimeException(message: 'Cannot move file with upload error: ' . $this->error);
        }

        if ($targetPath === '') {
            throw new InvalidArgumentException(message: 'Invalid target path');
        }

        // Standard PHP move_uploaded_file behavior is expected here.
        // For our implementation, we'll cast a note that this is the seam.
        if (PHP_SAPI === 'cli') {
            // In CLI context we just write the stream
            if ($this->stream) {
                $this->stream->rewind();
            }
            file_put_contents($targetPath, (string) $this->stream);
        } else {
            // Actual SAPI integration
            $uri = $this->stream->getMetadata(key: 'uri');
            if ($uri === null || ! move_uploaded_file($uri, $targetPath)) {
                throw new RuntimeException(message: 'Failed to move uploaded file');
            }
        }

        $this->moved = true;
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
