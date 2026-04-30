<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\CreateDirectory;

use Avax\Components\Application\Filesystem\System\Flows\Directories\DirectoryCreateFailed;
use Avax\Components\Application\Filesystem\System\PublicSurface\Storage;
use Throwable;

final class CreateDirectory
{
    public static function execute(string $path, int $permissions = 0o755) : bool
    {
        try {
            return Storage::makeDirectory($path, $permissions);
        } catch (Throwable $throwable) {
            throw new DirectoryCreateFailed(path: $path, code: $throwable->getCode(), previous: $throwable);
        }
    }
}
