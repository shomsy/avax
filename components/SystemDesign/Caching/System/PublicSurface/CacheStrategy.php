<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Caching\System\PublicSurface;

enum CacheStrategy: string
{
    case CACHE_ASIDE = 'cache-aside';
    case WRITE_THROUGH = 'write-through';
    case WRITE_BACK = 'write-back';
    case LOOK_ASIDE = 'look-aside';
}
