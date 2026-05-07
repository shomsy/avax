<?php

declare(strict_types=1);

namespace Avax\Components\Application\FeatureFlags\System\Flows\CheckFeatureEnabled;

final readonly class CheckFeatureEnabled
{
    /**
     * @param array<string, mixed> $flags
     */
    public function check(string $feature, array $flags) : bool
    {
        return (bool) ($flags[$feature] ?? false);
    }
}
