<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\MoveFile;

use Avax\Components\Application\Filesystem\System\PublicSurface\Storage;
use Avax\Components\Application\Filesystem\System\Flows\Files\FileMoveFailed;

final class MoveFile
{
    public static function execute(string $source, string $destination): bool
    {
        try {
            return Storage::move($source, $destination);
        } catch (\Throwable $e) {
            throw new FileMoveFailed(path: $destination, previous: $e);
        }
    }
}