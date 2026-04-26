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

        $originalContent = null;
        if (file_exists($finalPath->toString())) {
            $originalContent = file_get_contents($finalPath->toString());
        }

        $written = file_put_contents($temporaryPath, $payload, LOCK_EX);

        if ($written === false) {
            @unlink($temporaryPath);
            throw new CompiledCacheCouldNotBeWritten($name->toString());
        }

        $syntaxValid = $this->validatePhpSyntax($temporaryPath);

        if (! $syntaxValid) {
            @unlink($temporaryPath);
            if ($originalContent !== false && $originalContent !== null) {
                file_put_contents($finalPath->toString(), $originalContent, LOCK_EX);
            }
            throw new CompiledCacheCouldNotBeWritten($name->toString());
        }

        if (! rename($temporaryPath, $finalPath->toString())) {
            @unlink($temporaryPath);
            if ($originalContent !== false && $originalContent !== null) {
                file_put_contents($finalPath->toString(), $originalContent, LOCK_EX);
            }
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

    private function validatePhpSyntax(string $path) : bool
    {
        $output = shell_exec('php -l ' . escapeshellarg($path));
        if ($output === false || $output === null) {
            return false;
        }

        return str_contains($output, 'No syntax errors');
    }

    private function ensureDirectoryExists(string $directory) : void
    {
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }
}