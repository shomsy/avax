<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Storage;

use Avax\Components\HTTP\Session\System\Foundation\SessionData;
use Avax\Components\HTTP\Session\System\Foundation\SessionStoreInterface;

final class FileSessionStore implements SessionStoreInterface
{
    private string $path;
    private int    $ttl;

    public function __construct(
        private array $config = [],
    )
    {
        $this->path = $config['path'] ?? sys_get_temp_dir() . '/avax-sessions';
        $this->ttl  = $config['ttl'] ?? 1200;

        if (! is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    public function read(string $sessionId) : SessionData|null
    {
        $file = $this->getFilePath($sessionId);

        if (! file_exists($file)) {
            return null;
        }

        $content = file_get_contents($file);
        $decoded = json_decode($content, true);

        if (! $decoded) {
            return null;
        }

        return new SessionData(
            id       : $sessionId,
            data     : $decoded['data'] ?? [],
            createdAt: $decoded['created_at'] ?? time(),
            updatedAt: $decoded['updated_at'] ?? time(),
        );
    }

    private function getFilePath(string $sessionId) : string
    {
        $prefix    = substr($sessionId, 0, 2);
        $directory = $this->path . '/' . $prefix;

        return $directory . '/' . $sessionId . '.json';
    }

    public function write(string $sessionId, SessionData $data) : bool
    {
        $file      = $this->getFilePath($sessionId);
        $directory = dirname($file);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $payload = json_encode([
                                   'data'       => $data->data,
                                   'created_at' => $data->createdAt,
                                   'updated_at' => time(),
                               ]);

        return file_put_contents($file, $payload) !== false;
    }

    public function destroy(string $sessionId) : bool
    {
        $file = $this->getFilePath($sessionId);

        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    public function exists(string $sessionId) : bool
    {
        return file_exists($this->getFilePath($sessionId));
    }

    public function gc(int $maxLifetime) : int
    {
        $count = 0;
        $files = glob($this->path . '/*.json') ?: [];

        foreach ($files as $file) {
            if (filemtime($file) < time() - $maxLifetime) {
                if (unlink($file)) {
                    $count++;
                }
            }
        }

        return $count;
    }
}