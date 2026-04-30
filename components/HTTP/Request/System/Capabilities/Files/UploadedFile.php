<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Capabilities\Files;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

final class UploadedFile implements UploadedFileInterface
{
    public function __construct(
        private string $file,
        private ?int $size,
        private int $error,
        private ?string $clientFilename = null,
        private ?string $clientMediaType = null,
        private bool $moved = false,
    ) {}

    public function getStream(): StreamInterface
    {
        return Utils::streamFor(fopen($this->file, 'r'));
    }

    public function moveTo($targetPath): void
    {
        if (move_uploaded_file($this->file, $targetPath)) {
            $this->moved = true;
        }
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
        return $this->clientFilename;
    }

    public function getClientMediaType() : ?string
    {
        return $this->clientMediaType;
    }
}
