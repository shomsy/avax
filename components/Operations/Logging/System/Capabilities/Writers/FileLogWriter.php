<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Capabilities\Writers;

/**
 * Basic file log writer.
 */
final readonly class FileLogWriter
{
    public function __construct(
        private string $path,
    ) {
    }

    public function write(string $message): void
    {
        $dir = dirname($this->path);
        if (! is_dir($dir)) {
            mkdir($dir, 0o777, true);
        }

        file_put_contents($this->path, $message . PHP_EOL, FILE_APPEND);
    }
}
