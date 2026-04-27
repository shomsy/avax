<?php

declare(strict_types=1);

namespace Avax\Components\Response\System\Capabilities\Caching;

use Avax\HTTP\Response\Capabilities\Caching\CacheControl as RealCacheControl;

/**
 * CacheControl — delegates to the real implementation.
 */
class CacheControl extends RealCacheControl {}
