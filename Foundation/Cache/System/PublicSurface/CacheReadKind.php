<?php

declare(strict_types=1);

namespace Avax\Cache\System\PublicSurface;

enum CacheReadKind: string
{
    case RUNTIME  = 'runtime';
    case COMPILED = 'compiled';
}