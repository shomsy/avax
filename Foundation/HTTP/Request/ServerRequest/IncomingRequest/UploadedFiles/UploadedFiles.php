<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\IncomingHttp\IncomingRequest\UploadedFiles;

/**
 * Capability Owner: Manages the uploaded files tree.
 */
final readonly class UploadedFiles
{
    private array $files;

    /**
     * @param array $files Tree of UploadedFileInterface objects.
     */
    public function __construct(
        array $files = []
    )
    {
        $this->files = $files;
    }

    /**
     * @return array Tree of UploadedFileInterface objects.
     */
    public function all() : array
    {
        return $this->files;
    }

    /**
     * @param array $files
     */
    public function with(array $files) : self
    {
        return new self(files: $files);
    }
}
