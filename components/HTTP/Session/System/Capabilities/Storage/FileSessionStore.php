<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Storage;

final readonly class FileSessionStore implements SessionStoreInterface
{
    private string $path;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->path = $config['path'] ?? sys_get_temp_dir() . '/avax-sessions';

        if (! is_dir(filename: $this->path)) {
            mkdir(directory: $this->path, permissions: 0o755, recursive: true);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function read(string $id): array
    {
        $file = $this->filePath(sessionId: $id);

        if (! is_file(filename: $file)) {
            return [];
        }

        $payload = json_decode(json: (string) file_get_contents(filename: $file), associative: true);

        return is_array(value: $payload['data'] ?? null) ? $payload['data'] : [];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function write(string $id, array $data): bool
    {
        $file      = $this->filePath(sessionId: $id);
        $directory = dirname(path: $file);

        if (! is_dir(filename: $directory)) {
            mkdir(directory: $directory, permissions: 0o755, recursive: true);
        }

        return file_put_contents(
            filename: $file,
            data    : json_encode(value: ['data' => $data, 'updated_at' => time()], flags: JSON_THROW_ON_ERROR),
        ) !== false;
    }

    public function destroy(string $id): bool
    {
        $file = $this->filePath(sessionId: $id);

        return ! is_file(filename: $file) || unlink(filename: $file);
    }

    public function exists(string $sessionId): bool
    {
        return is_file(filename: $this->filePath(sessionId: $sessionId));
    }

    public function gc(int $maxLifetime): int
    {
        $removed = 0;

        foreach (glob(pattern: $this->path . '/*/*.json') ?: [] as $file) {
            if ((filemtime(filename: $file) ?: 0) < time() - $maxLifetime && unlink(filename: $file)) {
                $removed++;
            }
        }

        return $removed;
    }

    private function filePath(string $sessionId): string
    {
        $prefix = substr(string: $sessionId, offset: 0, length: 2);

        return $this->path . '/' . $prefix . '/' . $sessionId . '.json';
    }
}
