<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Flows\ApplyFallback;

use Avax\Components\Operations\Resilience\System\Capabilities\Fallback\Fallback;

final readonly class ApplyFallback
{
    /**
     * @param list<callable(): mixed> $fallbacks
     */
    public function apply(array $fallbacks) : mixed
    {
        return Fallback::execute($fallbacks);
    }
}
