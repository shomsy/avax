<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCompiledCache;

use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\SystemClock;

final class AtomicCompiledCacheWrite
{
    public function __construct(
        private CompiledCacheDirectory $directory,
        private Clock                  $clock = new SystemClock()
    ) {}

    public function write(
        CompiledCacheName $name,
        string            $payload
    ) : CompiledCacheArtifact
    {
        $pathResolver = new ResolveCompiledCachePath($this->directory);
        $finalPath    = $pathResolver->resolveArtifactPath($name);

        $temporaryPath = $finalPath->toTemporaryPath();

        $this->ensureDirectoryExists($finalPath->directory());

        $written = file_put_contents($temporaryPath, $payload, LOCK_EX);

        if ($written === false) {
            throw new CompiledCacheCouldNotBeWritten($name->toString());
        }

        if (! rename($temporaryPath, $finalPath->toString())) {
            @unlink($temporaryPath);
            throw new CompiledCacheCouldNotBeWritten($name->toString());
        }

        $now = $this->clock->now();

        return CompiledCacheArtifact::create(
            name             : $name->toString(),
            path             : $finalPath->toString(),
            createdAt        : $now->seconds,
            sourceFingerprint: ''
        );
    }

    private function ensureDirectoryExists(string $directory) : void
    {
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }
}