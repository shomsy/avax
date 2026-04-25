<?php

declare(strict_types=1);

namespace Avax\Cache\System\PublicSurface;

interface CacheReadTarget
{
    public function kind() : CacheReadKind;
}