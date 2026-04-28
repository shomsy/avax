<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\RefreshCachedValues;

enum RefreshPolicy: string
{
    case DO_NOT_REFRESH      = 'do_not_refresh';
    case REFRESH_ON_READ     = 'refresh_on_read';
    case REFRESH_AHEAD       = 'refresh_ahead';
    case REFRESH_AFTER_WRITE = 'refresh_after_write';
    case REFRESH_WHEN_STALE  = 'refresh_when_stale';
}