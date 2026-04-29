<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\CreateDirectory;

use Avax\Components\Application\Filesystem\System\PublicSurface\Storage;
use Avax\Components\Application\Filesystem\System\Flows\Directories\DirectoryCreateFailed;

final class CreateDirectory
{
    public static function execute(string $path, int $permissions = 0755): bool
    {
        try {
            return Storage::makeDirectory($path, $permissions);
        } catch (\Throwable $e) {
            throw new DirectoryCreateFailed(path: $path, previous: $e);
        }
    }
}