<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\DeleteDirectory;

use Avax\Components\Application\Filesystem\System\PublicSurface\Storage;
use Avax\Components\Application\Filesystem\System\Flows\Directories\DirectoryDeleteFailed;

final class DeleteDirectory
{
    public static function execute(string $path): bool
    {
        try {
            return Storage::deleteDirectory($path);
        } catch (\Throwable $e) {
            throw new DirectoryDeleteFailed(path: $path, previous: $e);
        }
    }
}