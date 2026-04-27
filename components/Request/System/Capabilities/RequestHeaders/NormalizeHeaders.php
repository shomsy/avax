<?php

declare(strict_types=1);

namespace Avax\Components\Request\System\Capabilities\RequestHeaders;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestHeaders\NormalizeHeaders as RealNormalizeHeaders;

/**
 * NormalizeHeaders — delegates to the real implementation.
 *
 * @see RealNormalizeHeaders
 */
class NormalizeHeaders extends RealNormalizeHeaders {}
