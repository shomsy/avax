<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\DeleteDirectory;

use Avax\Components\Application\Filesystem\System\Flows\Directories\DirectoryDeleteFailed;
use Avax\Components\Application\Filesystem\System\PublicSurface\Storage;
use Throwable;

final class DeleteDirectory
{
    public static function execute(string $path): bool
    {
        try {
            return Storage::deleteDirectory($path);
        } catch (Throwable $throwable) {
            throw new DirectoryDeleteFailed(path: $path, code: $throwable->getCode(), previous: $throwable);
        }
    }
}
