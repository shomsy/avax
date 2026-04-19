<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles;

use Avax\HTTP\Response\Classes\Stream;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

/**
 * UploadedFile - State owner for a single uploaded file.
 */
final class UploadedFile implements UploadedFileInterface
{
    private bool $moved = false;

    public function __construct(
        private readonly string      $tmpName,
        private readonly int         $size,
        private readonly int         $error,
        private readonly string|null $name = null,
        private readonly string|null $type = null
    ) {}

    public function getStream() : StreamInterface
    {
        if ($this->moved) {
            throw new RuntimeException(message: 'Cannot retrieve stream after file has been moved.');
        }

        if ($this->error !== UPLOAD_ERR_OK) {
            throw new RuntimeException(message: 'Cannot retrieve stream for file with upload error.');
        }

        return new Stream(stream: fopen($this->tmpName, 'r'));
    }

    public function moveTo($targetPath) : void
    {
        if ($this->moved) {
            throw new RuntimeException(message: 'File has already been moved.');
        }

        if (! is_string($targetPath) || $targetPath === '') {
            throw new InvalidArgumentException(message: 'Invalid target path provided.');
        }

        if (PHP_SAPI === 'cli') {
            $success = rename($this->tmpName, $targetPath);
        } else {
            $success = move_uploaded_file($this->tmpName, $targetPath);
        }

        if (! $success) {
            throw new RuntimeException(message: 'Failed to move uploaded file.');
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
        return $this->name;
    }

    public function getClientMediaType() : string|null
    {
        return $this->type;
    }
}
