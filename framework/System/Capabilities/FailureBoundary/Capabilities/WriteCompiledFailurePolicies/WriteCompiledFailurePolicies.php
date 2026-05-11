<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\WriteCompiledFailurePolicies;

use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledMethodPolicy;

/**
 * WriteCompiledFailurePolicies — Writes compiled policies to a file-based artifact.
 *
 * This provides persistence across process restarts, complementing the in-memory cache.
 */
final readonly class WriteCompiledFailurePolicies
{
    public function __construct(
        private string $artifactDir,
    ) {
    }

    public function write(CompiledMethodPolicy $policy): string
    {
        $key = $policy->targetClass . '::' . $policy->targetMethod;
        $safeKey = str_replace(['\\', '::'], ['_', '__'], $key);
        $path = $this->artifactDir . '/' . $safeKey . '.json';

        $data = $policy->toArray();
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        if (!is_dir($this->artifactDir)) {
            mkdir($this->artifactDir, 0755, true);
        }

        file_put_contents($path, $json, LOCK_EX);

        return $path;
    }
}
