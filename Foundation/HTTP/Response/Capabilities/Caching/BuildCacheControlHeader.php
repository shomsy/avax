<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Caching;

final class BuildCacheControlHeader
{
    public function __invoke(CacheControl $cacheControl) : string
    {
        $parts = [];

        foreach ($cacheControl->directives as $directive => $value) {
            $parts[] = $value === true ? $directive : "{$directive}={$value}";
        }

        return implode(separator: ', ', array: $parts);
    }
}
