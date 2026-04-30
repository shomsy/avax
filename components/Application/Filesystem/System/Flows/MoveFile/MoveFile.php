<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\MoveFile;

use Avax\Components\Application\Filesystem\System\Flows\Files\FileMoveFailed;
use Avax\Components\Application\Filesystem\System\PublicSurface\Storage;
use Throwable;

final class MoveFile
{
    public static function execute(string $source, string $destination) : bool
    {
        try {
            return Storage::move($source, $destination);
        } catch (Throwable $throwable) {
            throw new FileMoveFailed(path: $destination, code: $throwable->getCode(), previous: $throwable);
        }
    }
}
