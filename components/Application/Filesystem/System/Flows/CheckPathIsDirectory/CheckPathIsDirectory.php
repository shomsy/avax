<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\CheckPathIsDirectory;

final readonly class CheckPathIsDirectory
{
    public function execute(string $path) : bool
    {
        $cleanPath = $this->sanitizePath($path);

        return is_dir($cleanPath);
    }

    private function sanitizePath(string $path) : string
    {
        return str_replace(["\0", "\n", "\r"], '', $path);
    }
}
