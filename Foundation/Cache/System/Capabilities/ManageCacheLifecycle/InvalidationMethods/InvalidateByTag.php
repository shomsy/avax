<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\InvalidationMethods;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheTag;

interface InvalidateByTag
{
    public function invalidateByTag(CacheTag $tag) : int;

    public function isTagInvalidated(CacheTag $tag) : bool;
}