<?php

declare(strict_types=1);

namespace Avax\Components\Response\System\Capabilities\Caching;

use Avax\HTTP\Response\Capabilities\Caching\Etag as RealEtag;

/**
 * Etag — delegates to the real implementation.
 */
class Etag extends RealEtag {}
