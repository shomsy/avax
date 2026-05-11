<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Filesystem\System\Flows\WriteFile;

use Avax\Components\Operations\Filesystem\System\Foundation\Failure\FilesystemException;

final readonly class WriteFile
{
    public function write(string $path, string $content) : bool
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $result = $this->writeFileContents($path, $content);

        if ($result === false) {
            throw new FilesystemException("Unable to write file: {$path}");
        }

        return true;
    }

    private function writeFileContents(string $path, string $content) : int|false
    {
        $error = null;
        set_error_handler(static function (int $errno, string $errstr) use (&$error) : true {
            $error = $errstr;

            return true;
        });

        try {
            $result = file_put_contents($path, $content);
        } finally {
            restore_error_handler();
        }

        if ($result === false && $error !== null) {
            throw new FilesystemException("Unable to write file: {$path} ({$error})");
        }

        return $result;
    }
}
