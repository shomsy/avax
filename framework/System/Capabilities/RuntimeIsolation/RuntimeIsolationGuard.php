<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RuntimeIsolation;

final readonly class RuntimeIsolationGuard
{
    private const array RUNTIME_MARKERS
        = [
            'Swoole\\',
            'RoadRunner\\',
            'Spiral\\RoadRunner',
            'FrankenPHP',
        ];

    /**
     * @return list<string>
     */
    public function detectLeaks(string $source, string $path = ''): array
    {
        $violations = [];

        foreach (self::RUNTIME_MARKERS as $marker) {
            if (str_contains($source, $marker) && ! str_contains($path, '/Runtime/Adapters/')) {
                $violations[] = $marker;
            }
        }

        return $violations;
    }
}
