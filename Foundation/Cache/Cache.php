<?php

declare(strict_types=1);

namespace Avax\Cache;

use Avax\Cache\System\PublicSurface\CacheFacade;
use Avax\Facade\BaseFacade;

final class Cache extends BaseFacade
{
    protected static string $accessor = CacheFacade::class;
}