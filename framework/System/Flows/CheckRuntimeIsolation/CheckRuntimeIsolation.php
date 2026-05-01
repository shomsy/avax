<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\CheckRuntimeIsolation;

use Avax\Framework\System\Capabilities\RuntimeIsolation\RuntimeIsolationGuard;

final readonly class CheckRuntimeIsolation
{
    public function __construct(
        private RuntimeIsolationGuard $guard = new RuntimeIsolationGuard,
    ) {}

    /**
     * @param array<string, string> $files
     *
     * @return array<string, list<string>>
     */
    public function check(array $files): array
    {
        $violations = [];

        foreach ($files as $path => $source) {
            $fileViolations = $this->guard->detectLeaks(source: $source, path: $path);

            if ($fileViolations !== []) {
                $violations[$path] = $fileViolations;
            }
        }

        return $violations;
    }
}
