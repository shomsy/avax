<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\PublicSurface;

use Avax\Components\Application\Filesystem\System\Capabilities\Storage\LocalStorage;

/**
 * Storage Public Surface.
 *
 * Main entry point for filesystem operations.
 * Delegated to internal storage capabilities.
 */
final readonly class Storage
{
    public function __construct(
        private LocalStorage $storage
    ) {}

    public function get(string $path) : string
    {
        return $this->storage->get($path);
    }

    public function put(string $path, string $content) : void
    {
        $this->storage->put($path, $content);
    }

    public function exists(string $path) : bool
    {
        return $this->storage->exists($path);
    }

    public function delete(string $path) : void
    {
        $this->storage->delete($path);
    }

    public function makeDirectory(string $path, int $permissions = 0755) : void
    {
        $this->storage->createDirectory($path, $permissions);
    }
}
