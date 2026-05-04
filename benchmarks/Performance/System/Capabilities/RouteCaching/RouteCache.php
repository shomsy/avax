<?php

declare(strict_types=1);

namespace Avax\Benchmarks\Performance\System\Capabilities\RouteCaching;

final readonly class RouteCache
{
    public function __construct(private string $path)
    {
    }

    public function write(array $routes): void
    {
        $directory = dirname(path: $this->path);

        if (!is_dir(filename: $directory)) {
            mkdir(directory: $directory, permissions: 0o755, recursive: true);
        }

        file_put_contents(
            filename: $this->path,
            data: '<?php return ' . var_export(value: $routes, return: true) . ';' . PHP_EOL,
        );
    }

    public function read(): array
    {
        if (!is_file(filename: $this->path)) {
            return [];
        }

        $routes = require $this->path;

        return is_array(value: $routes) ? $routes : [];
    }

    public function clear(): void
    {
        if (is_file(filename: $this->path)) {
            unlink(filename: $this->path);
        }
    }
}
