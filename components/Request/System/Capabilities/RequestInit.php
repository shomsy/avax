<?php

declare(strict_types=1);

namespace Avax\Components\Request\System\Capabilities;

use components\HTTP\Request\ServerRequest\IncomingRequest\RequestInit as RealRequestInit;

/**
 * RequestInit — delegates to the real implementation.
 *
 * @see RealRequestInit
 */
class RequestInit extends RealRequestInit {}
