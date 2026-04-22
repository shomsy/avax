<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles;

/**
 * Capability Owner: Manages the uploaded files tree.
 */
final readonly class UploadedFiles
{
    public function __construct(private array $files = [])
    {
        (new GuardUploadedFiles)->execute(files: $files);
    }

    /**
     * @return array Tree of UploadedFileInterface objects.
     */
    public function all() : array
    {
        return $this->files;
    }

    public function with(array $files) : self
    {
        return new self(files: $files);
    }
}
