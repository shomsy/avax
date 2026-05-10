<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\CheckPathIsFile;

final readonly class CheckPathIsFile
{
    public function execute(string $path) : bool
    {
        $cleanPath = $this->sanitizePath($path);

        return is_file($cleanPath);
    }

    private function sanitizePath(string $path) : string
    {
        return str_replace(["\0", "\n", "\r"], '', $path);
    }
}
