<?php

declare(strict_types=1);

namespace Avax\Components\Application\FeatureFlags\System\Flows\EvaluateFlag;

final readonly class EvaluateFlag
{
    /**
     * @param array<string, mixed> $flags
     */
    public function evaluate(string $flag, array $flags, mixed $default = false) : mixed
    {
        return $flags[$flag] ?? $default;
    }
}
