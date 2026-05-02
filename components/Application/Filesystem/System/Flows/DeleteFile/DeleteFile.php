<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\DeleteFile;

use Avax\Components\Application\Filesystem\System\Flows\Files\FileDeleteFailed;
use Avax\Components\Application\Filesystem\System\PublicSurface\Storage;
use Throwable;

final class DeleteFile
{
    public static function execute(string $path): bool
    {
        try {
            return Storage::delete($path);
        } catch (Throwable $throwable) {
            throw new FileDeleteFailed(path: $path, code: $throwable->getCode(), previous: $throwable);
        }
    }
}
