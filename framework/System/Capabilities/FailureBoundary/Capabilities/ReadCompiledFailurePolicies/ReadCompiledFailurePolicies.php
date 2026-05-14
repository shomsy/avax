<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\ReadCompiledFailurePolicies;

use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledMethodPolicy;

/**
 * ReadCompiledFailurePolicies — Reads compiled policies from a file-based artifact.
 */
final readonly class ReadCompiledFailurePolicies
{
    public function __construct(
        private string $artifactDir,
    ) {
    }

    public function read(string $targetClass, string $targetMethod): CompiledMethodPolicy|null
    {
        $key = $targetClass . '::' . $targetMethod;
        $safeKey = str_replace(['\\', '::'], ['_', '__'], $key);
        $path = $this->artifactDir . '/' . $safeKey . '.json';

        if (!file_exists($path)) {
            return null;
        }

        $json = file_get_contents($path);
        if ($json === false) {
            return null;
        }

        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return CompiledMethodPolicy::fromArray($data);
    }
}
