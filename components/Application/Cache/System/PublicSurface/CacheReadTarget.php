<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\PublicSurface;

use Avax\Components\Application\Cache\System\PublicSurface\Read\CacheReadKind;

interface CacheReadTarget
{
    public function kind(): CacheReadKind;
}
