<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;

final readonly class AtomicCompiledCacheWrite
{
    private CompiledCacheDirectory $compiledCacheDirectory;

    public function __construct(
        CompiledCacheDirectory $directory,
        private Clock          $clock = new SystemClock(),
    )
    {
        $this->compiledCacheDirectory = $directory;
    }

    public function write(
        CompiledCacheName $name,
        string            $payload,
    ) : CompiledCacheArtifact
    {
        $resolveCompiledCachePath = new ResolveCompiledCachePath(directory: $this->compiledCacheDirectory);
        $compiledCachePath = $resolveCompiledCachePath->resolveArtifactPath(name: $name);

        $temporaryPath = $compiledCachePath->toTemporaryPath();

        $this->ensureDirectoryExists(directory: $compiledCachePath->directory());

        $written = file_put_contents($temporaryPath, $payload, LOCK_EX);

        if ($written === false) {
            @unlink($temporaryPath);

            throw new CompiledCacheCouldNotBeWritten(name: $name->toString());
        }

        $syntaxValid = $this->validatePhpSyntax(path: $temporaryPath);

        if (! $syntaxValid) {
            @unlink($temporaryPath);

            throw new CompiledCacheCouldNotBeWritten(name: $name->toString());
        }

        if (! rename($temporaryPath, $compiledCachePath->toString())) {
            @unlink($temporaryPath);

            throw new CompiledCacheCouldNotBeWritten(name: $name->toString());
        }

        $now = $this->clock->now();

        return CompiledCacheArtifact::create(
            name             : $name->toString(),
            path             : $compiledCachePath->toString(),
            createdAt        : $now->seconds,
            sourceFingerprint: '',
        );
    }

    private function ensureDirectoryExists(string $directory) : void
    {
        if (! is_dir($directory)) {
            mkdir($directory, 0o755, true);
        }
    }

    private function validatePhpSyntax(string $path) : bool
    {
        $output = shell_exec('php -l ' . escapeshellarg($path));
        if ($output === false || $output === null) {
            return false;
        }

        return str_contains($output, 'No syntax errors');
    }
}
