<?php

declare(strict_types=1);

namespace Avax\Components\Cache\System\PublicSurface\Read;

enum CacheReadKind: string
{
    case RUNTIME  = 'runtime';
    case COMPILED = 'compiled';
}