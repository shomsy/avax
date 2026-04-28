<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheTag;

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