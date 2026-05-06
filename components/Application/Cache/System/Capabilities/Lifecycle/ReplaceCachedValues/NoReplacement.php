<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues;

use Override;

final readonly class NoReplacement implements ChooseCachedValueForReplacement
{
    #[Override]
    public function choose(array $entries): ?string
    {
        return null;
    }
}
