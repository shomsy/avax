<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Filesystem\System\Flows\DeleteFile;

use Avax\Components\Operations\Filesystem\System\Foundation\Failure\FilesystemException;

final readonly class DeleteFile
{
    public function delete(string $path) : bool
    {
        if (! file_exists($path)) {
            return false;
        }

        $result = @unlink($path);

        if (! $result) {
            throw new FilesystemException("Unable to delete file: {$path}");
        }

        return true;
    }
}
