<?php

declare(strict_types=1);

namespace Avax\Cache\System\PublicSurface\Read;

interface CacheReadTarget
{
    public function kind() : CacheReadKind;
}