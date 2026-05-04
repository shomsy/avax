<?php

declare(strict_types=1);

namespace Avax\Components\Performance\System\Capabilities\ConfigCaching;

final readonly class ConfigCache
{
    public function __construct(private string $path) {}

    /**
     * @param array<string, mixed> $config
     */
    public function write(array $config): void
    {
        $directory = dirname(path: $this->path);

        if (! is_dir(filename: $directory)) {
            mkdir(directory: $directory, permissions: 0o755, recursive: true);
        }

        file_put_contents(
            filename: $this->path,
            data    : '<?php return ' . var_export(value: $config, return: true) . ';' . PHP_EOL,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function read(): array
    {
        if (! is_file(filename: $this->path)) {
            return [];
        }

        $config = require $this->path;

        return is_array(value: $config) ? $config : [];
    }

    public function clear(): void
    {
        if (is_file(filename: $this->path)) {
            unlink(filename: $this->path);
        }
    }
}
