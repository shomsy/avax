<?php

declare(strict_types=1);

namespace Avax\Components\Response\System\Capabilities\Caching;

use Avax\HTTP\Response\Capabilities\Caching\LastModified as RealLastModified;

/**
 * LastModified — delegates to the real implementation.
 */
class LastModified extends RealLastModified {}
