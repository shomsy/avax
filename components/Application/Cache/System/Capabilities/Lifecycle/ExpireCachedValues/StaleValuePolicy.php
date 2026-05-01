<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ExpireCachedValues;

enum StaleValuePolicy: string
{
    case DO_NOT_SERVE_STALE  = 'do_not_serve_stale';
    case SERVE_STALE_WHILE_REVALIDATING = 'serve_stale_while_revalidating';
    case SERVE_STALE_FOREVER = 'serve_stale_forever';
}
