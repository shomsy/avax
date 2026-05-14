<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;

final class AtomicCompiledCacheWrite
{
    public function __construct(
        private CompiledCacheDirectory $compiledCacheDirectory,
        private Filesystem $filesystem,
        private Clock                  $clock = new SystemClock(),
    )
    {
    }

    /**
 * @throws CompiledCacheCouldNotBeWritten
 */
public function write(
        CompiledCacheName $compiledCacheName,
        string $payload,
    ): CompiledCacheArtifact {
        $resolveCompiledCachePath = new ResolveCompiledCachePath(compiledCacheDirectory: $this->compiledCacheDirectory);
        $compiledCachePath = $resolveCompiledCachePath->resolveArtifactPath(compiledCacheName: $compiledCacheName);

        $temporaryPath = $compiledCachePath->toTemporaryPath();

        $this->ensureDirectoryExists(directory: $compiledCachePath->directory());

        $written = $this->filesystem->write($temporaryPath, $payload);

        if (! $written) {
            $this->filesystem->delete($temporaryPath);

            throw new CompiledCacheCouldNotBeWritten(name: $compiledCacheName->toString());
        }

        $syntaxValid = $this->validatePhpSyntax(path: $temporaryPath);

        if (! $syntaxValid) {
            $this->filesystem->delete($temporaryPath);

            throw new CompiledCacheCouldNotBeWritten(name: $compiledCacheName->toString());
        }

        if (! $this->filesystem->move($temporaryPath, $compiledCachePath->toString())) {
            $this->filesystem->delete($temporaryPath);

            throw new CompiledCacheCouldNotBeWritten(name: $compiledCacheName->toString());
        }

        $now = $this->clock->now();

        return CompiledCacheArtifact::create(
            name             : $compiledCacheName->toString(),
            path             : $compiledCachePath->toString(),
            createdAt        : $now->seconds,
            sourceFingerprint: '',
        );
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (! $this->filesystem->exists($directory)) {
            $this->filesystem->createDirectory($directory, 0o755);
        }
    }

    private function validatePhpSyntax(string $path): bool
    {
        $output = shell_exec('php -l '.escapeshellarg($path));
        if ($output === false || $output === null) {
            return false;
        }

        return str_contains($output, 'No syntax errors');
    }
}
