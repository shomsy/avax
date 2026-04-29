<?php
declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\PublicSurface;

use Avax\Components\Application\Filesystem\System\Capabilities\Disks\Disk;

final readonly class Filesystem implements FilesystemInterface
{
    public function __construct(
        private Disk $disk
    ) {}

    public function read(string $path): string { return $this->disk->read($path); }
    public function write(string $path, string $content, bool $append = false): bool { return $this->disk->write($path, $content, $append); }
    public function copy(string $source, string $destination): bool { return $this->disk->copy($source, $destination); }
    public function move(string $source, string $destination): bool { return $this->disk->move($source, $destination); }
    public function delete(string $path): bool { return $this->disk->delete($path); }
    public function exists(string $path): bool { return $this->disk->exists($path); }
    public function lastModified(string $path): ?int { return $this->disk->lastModified($path); }
    public function createDirectory(string $path, int $permissions = 0755): bool { return $this->disk->createDirectory($path, $permissions); }
    public function deleteDirectory(string $path): bool { return $this->disk->deleteDirectory($path); }
    public function clearDirectory(string $path): bool { return $this->disk->clear($path); }
    public function listFiles(string $path): array { return $this->disk->listFiles($path); }
    public function isWritable(string $path): bool { return $this->disk->isWritable($path); }
    public function setPermissions(string $path, int $permissions): bool { return $this->disk->setPermissions($path, $permissions); }
}