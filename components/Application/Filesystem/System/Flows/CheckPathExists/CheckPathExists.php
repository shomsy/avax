<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\CheckPathExists;

final readonly class CheckPathExists
{
    public function execute(string $path) : bool
    {
        $cleanPath = $this->sanitizePath($path);

        return file_exists($cleanPath);
    }

    private function sanitizePath(string $path) : string
    {
        return str_replace(["\0", "\n", "\r"], '', $path);
    }
}