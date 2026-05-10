<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Storage;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;

final readonly class FileSessionStore implements SessionStoreInterface
{
    private Filesystem $filesystem;

    private string $path;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [], ?Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ?? new Filesystem();
        $this->path = $config['path'] ?? sys_get_temp_dir() . '/avax-sessions';

        if (! $this->filesystem->exists($this->path)) {
            $this->filesystem->createDirectory($this->path, 0o755);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function read(string $id) : array
    {
        $file = $this->filePath(sessionId: $id);

        if (! $this->filesystem->isReadable($file)) {
            return [];
        }

        $content = $this->filesystem->read($file);
        $payload = json_decode(json: $content, associative: true);

        return is_array(value: $payload['data'] ?? null) ? $payload['data'] : [];
    }

    private function filePath(string $sessionId) : string
    {
        $prefix = substr(string: $sessionId, offset: 0, length: 2);

        return $this->path . '/' . $prefix . '/' . $sessionId . '.json';
    }

    /**
     * @param array<string, mixed> $data
     */
    public function write(string $id, array $data) : bool
    {
        $file      = $this->filePath(sessionId: $id);
        $directory = dirname(path: $file);

        if (! $this->filesystem->exists($directory)) {
            $this->filesystem->createDirectory($directory, 0o755);
        }

        return $this->filesystem->write(
            $file,
            json_encode(value: ['data' => $data, 'updated_at' => time()], flags: JSON_THROW_ON_ERROR),
        );
    }

    public function destroy(string $id) : bool
    {
        $file = $this->filePath(sessionId: $id);

        return ! $this->filesystem->isReadable($file) || $this->filesystem->delete($file);
    }

    public function exists(string $sessionId) : bool
    {
        return $this->filesystem->isReadable($this->filePath(sessionId: $sessionId));
    }

    public function gc(int $maxLifetime) : int
    {
        $removed = 0;
        $now = time();

        foreach ($this->filesystem->listDirectory($this->path) as $subDir) {
            $fullSubDir = $this->path . '/' . $subDir;
            if (! $this->filesystem->isDirectory($fullSubDir)) {
                continue;
            }

            foreach ($this->filesystem->listDirectory($fullSubDir) as $entry) {
                $file = $fullSubDir . '/' . $entry;
                if (! str_ends_with($entry, '.json')) {
                    continue;
                }

                $mtime = filemtime($file);
                if ($mtime !== false && $mtime < $now - $maxLifetime) {
                    if ($this->filesystem->delete($file)) {
                        $removed++;
                    }
                }
            }
        }

        return $removed;
    }
}
