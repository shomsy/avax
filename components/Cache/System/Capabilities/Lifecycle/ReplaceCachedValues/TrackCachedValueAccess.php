<?php

declare(strict_types=1);

namespace components\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues;

interface TrackCachedValueAccess
{
    public function recordAccess(string $key) : void;
}