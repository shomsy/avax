<?php

declare(strict_types=1);

namespace Avax\Cache;

use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheContract;
use Avax\Facade\BaseFacade;

final class CompiledCache extends BaseFacade
{
    protected static string $accessor = CompiledCacheContract::class;
}