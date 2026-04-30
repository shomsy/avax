<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;





enum CompiledCacheFreshness: string
{
    case FRESH   = 'fresh';
    case STALE   = 'stale';
    case MISSING = 'missing';
}