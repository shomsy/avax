<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Filesystem\System\Flows\ReadFile;

use Avax\Components\Operations\Filesystem\System\Foundation\Failure\FilesystemException;

final readonly class ReadFile
{
    public function read(string $path) : string
    {
        $content = $this->readFileContents($path);

        if ($content === false) {
            throw new FilesystemException("Unable to read file: {$path}");
        }

        return $content;
    }

    private function readFileContents(string $path) : string|false
    {
        $error = null;
        set_error_handler(static function (int $errno, string $errstr) use (&$error) : true {
            $error = $errstr;

            return true;
        });

        try {
            $content = file_get_contents($path);
        } finally {
            restore_error_handler();
        }

        if ($content === false && $error !== null) {
            throw new FilesystemException("Unable to read file: {$path} ({$error})");
        }

        return $content;
    }
}
