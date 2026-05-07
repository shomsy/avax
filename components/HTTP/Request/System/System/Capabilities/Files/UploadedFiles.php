<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\System\Capabilities\Files;

final readonly class UploadedFiles
{
    public function __construct(
        /** @var UploadedFile[] */
        private array $files = []
    ) {}

    public function all() : array
    {
        return $this->files;
    }
}
