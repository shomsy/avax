<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles;

use Avax\HTTP\Response\Capabilities\Streams\ResponseStreamFactory;
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
        private readonly string  $tmpName,
        private readonly int     $size,
        private readonly int     $error,
        private readonly ?string $name = null,
        private readonly ?string $type = null
    ) {}

    public function getStream() : StreamInterface
    {
        if ($this->moved) {
            throw new RuntimeException(message: 'File already moved.');
        }

        if ($this->error !== UPLOAD_ERR_OK) {
            throw new RuntimeException(message: 'Cannot retrieve stream for file with upload error.');
        }

        return new ResponseStreamFactory()->openFileStream(path: $this->tmpName);
    }

    public function moveTo(string $targetPath) : void
    {
        if ($this->moved) {
            throw new RuntimeException(message: 'File already moved.');
        }

        if ($this->error !== UPLOAD_ERR_OK) {
            throw new RuntimeException(message: 'Cannot move file with upload error.');
        }

        if (! is_string(value: $targetPath) || $targetPath === '') {
            throw new InvalidArgumentException(message: 'Invalid target path provided.');
        }

        if (PHP_SAPI === 'cli') {
            $success = rename(from: $this->tmpName, to: $targetPath);
        } else {
            $success = move_uploaded_file(from: $this->tmpName, to: $targetPath);
        }

        if (! $success) {
            throw new RuntimeException(message: 'Cannot move file with upload error.');
        }

        $this->moved = true;
    }

    public function getSize() : ?int
    {
        return $this->size;
    }

    public function getError() : int
    {
        return $this->error;
    }

    public function getClientFilename() : ?string
    {
        return $this->name;
    }

    public function getClientMediaType() : ?string
    {
        return $this->type;
    }
}
