<?php

declare(strict_types=1);

namespace Avax\Components\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

interface InvalidateByPattern
{
    /**
     * @param string $pattern Unix shell-style wildcards: * matches everything, ? matches single char
     */
    public function invalidateByPattern(string $pattern) : int;

    public function matchesPattern(string $key, string $pattern) : bool;
}