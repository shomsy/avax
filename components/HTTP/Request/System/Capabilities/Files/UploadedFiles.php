<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Capabilities\Files;

final class UploadedFiles
{
    /** @var UploadedFile[] */
    private array $files;

    public function __construct(array $files = [])
    {
        $this->files = $files;
    }

    public function all(): array
    {
        return $this->files;
    }
}
