<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheTag;

interface InvalidateByTag
{
    public function invalidateByTag(CacheTag $tag) : int;

    public function isTagInvalidated(CacheTag $tag) : bool;
}