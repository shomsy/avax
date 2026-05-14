<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\ReadFile;

use Avax\Components\Application\Filesystem\System\Foundation\Failure\FileNotFound;
use RuntimeException;

final readonly class ReadFile
{
    /**
 * @throws FileNotFound
 */
public function execute(string $path) : string
    {
        $cleanPath = $this->sanitizePath($path);

        if (! file_exists($cleanPath)) {
            throw new FileNotFound($path);
        }

        if (! is_readable($cleanPath)) {
            throw new RuntimeException("File not readable: {$path}");
        }

        $content = file_get_contents($cleanPath);

        if ($content === false) {
            throw new RuntimeException("Failed to read file: {$path}");
        }

        return $content;
    }

    private function sanitizePath(string $path) : string
    {
        return str_replace(["\0", "\n", "\r"], '', $path);
    }
}