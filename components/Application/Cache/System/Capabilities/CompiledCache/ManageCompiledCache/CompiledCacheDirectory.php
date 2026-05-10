<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use InvalidArgumentException;

final readonly class CompiledCacheDirectory
{
    public function __construct(
        public string $path,
    ) {
        $filesystem = new Filesystem();
        $this->validate($filesystem);
        $this->ensureExists($filesystem);
    }

    private function validate(Filesystem $filesystem) : void
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

    private function ensureExists(Filesystem $filesystem) : void
    {
        if (! $filesystem->exists($this->path)) {
            $filesystem->createDirectory($this->path, 0o755);
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
