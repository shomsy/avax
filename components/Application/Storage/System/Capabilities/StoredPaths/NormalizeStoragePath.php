<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Capabilities\StoredPaths;

use Avax\Components\Application\Storage\System\Foundation\Failure\InvalidStoragePath;

final readonly class NormalizeStoragePath
{
    public function execute(string $path) : string
    {
        $path       = str_replace("\\", "/", $path);
        $normalized = preg_replace('#/+#', '/', $path);
        $cleanPath  = $normalized ?? $path;
        $cleanPath  = trim($cleanPath, '/');

        return $cleanPath;
    }
}