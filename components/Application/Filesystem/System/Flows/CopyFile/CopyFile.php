<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\CopyFile;

use Avax\Components\Application\Filesystem\System\PublicSurface\Storage;
use Avax\Components\Application\Filesystem\System\Flows\Files\FileCopyFailed;

final class CopyFile
{
    public static function execute(string $source, string $destination): bool
    {
        try {
            return Storage::copy($source, $destination);
        } catch (\Throwable $e) {
            throw new FileCopyFailed(path: $destination, previous: $e);
        }
    }
}