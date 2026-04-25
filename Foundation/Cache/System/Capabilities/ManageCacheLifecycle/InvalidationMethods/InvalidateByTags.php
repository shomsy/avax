<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\InvalidationMethods;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheTag;

interface InvalidateByTags
{
    /**
     * @param iterable<CacheTag> $tags
     */
    public function invalidateByTags(iterable $tags) : int;

    /**
     * @param iterable<CacheTag> $tags
     */
    public function areAllTagsInvalidated(iterable $tags) : bool;
}