<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\DeleteFile;

use Avax\Components\Application\Filesystem\System\PublicSurface\Storage;
use Avax\Components\Application\Filesystem\System\Flows\Files\FileDeleteFailed;

final class DeleteFile
{
    public static function execute(string $path): bool
    {
        try {
            return Storage::delete($path);
        } catch (\Throwable $e) {
            throw new FileDeleteFailed(path: $path, previous: $e);
        }
    }
}