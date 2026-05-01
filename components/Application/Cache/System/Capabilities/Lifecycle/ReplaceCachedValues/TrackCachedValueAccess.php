<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues;

interface TrackCachedValueAccess
{
    public function recordAccess(string $key) : void;

    public function removeKey(string $key) : void;

    public function reset() : void;
}
