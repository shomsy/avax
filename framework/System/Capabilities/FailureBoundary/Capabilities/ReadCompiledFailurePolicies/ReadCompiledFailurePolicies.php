<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\ReadCompiledFailurePolicies;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledMethodPolicy;

/**
 * ReadCompiledFailurePolicies — Reads compiled policies from a file-based artifact.
 */
final readonly class ReadCompiledFailurePolicies
{
    public function __construct(
        private string $artifactDir,
        private Filesystem $filesystem,
    ) {
    }

    public function read(string $targetClass, string $targetMethod): CompiledMethodPolicy|null
    {
        $key = $targetClass . '::' . $targetMethod;
        $safeKey = str_replace(['\\', '::'], ['_', '__'], $key);
        $path = $this->artifactDir . '/' . $safeKey . '.json';

        if (!$this->filesystem->exists($path)) {
            return null;
        }

        $json = $this->filesystem->read($path);

        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return CompiledMethodPolicy::fromArray($data);
    }
}
