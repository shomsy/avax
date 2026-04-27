<?php

declare(strict_types=1);

namespace Avax\Components\Request\System\Capabilities\RequestHeaders;

use components\HTTP\Request\ServerRequest\IncomingRequest\RequestHeaders\RequestHeaders as RealRequestHeaders;

/**
 * RequestHeaders — delegates to the real implementation.
 *
 * @see RealRequestHeaders
 */
class RequestHeaders extends RealRequestHeaders {}
