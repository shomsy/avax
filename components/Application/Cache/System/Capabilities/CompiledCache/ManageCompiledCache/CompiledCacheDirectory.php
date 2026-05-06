<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use InvalidArgumentException;

final readonly class CompiledCacheDirectory
{
    public function __construct(
        public string $path,
    ) {
        $this->validate();
        $this->ensureExists();
    }

    private function validate(): void
    {
        if ($this->path === '') {
            throw new InvalidArgumentException(message: 'Compiled cache directory cannot be empty');
        }

        if (is_file($this->path)) {
            throw new InvalidArgumentException(message: sprintf(
                'Expected directory but found file: %s',
                $this->path,
            ));
        }
    }

    private function ensureExists(): void
    {
        if (! is_dir($this->path)) {
            mkdir($this->path, 0o755, true);
        }
    }

    public static function fromString(string $path): self
    {
        return new self(path: $path);
    }

    public function toString(): string
    {
        return $this->path;
    }

    public function resolve(string $filename): CompiledCachePath
    {
        return new CompiledCachePath(path: $this->path.DIRECTORY_SEPARATOR.$filename.'.php');
    }

    public function resolveManifestPath(): CompiledCachePath
    {
        return new CompiledCachePath(path: $this->path.DIRECTORY_SEPARATOR.'manifest.php');
    }
}
