<?php

declare(strict_types=1);

namespace Avax\Components\Request\System\Capabilities;

use components\HTTP\Request\ServerRequest\IncomingRequest\ServerInit as RealServerInit;

/**
 * ServerInit — delegates to the real implementation.
 *
 * @see RealServerInit
 */
class ServerInit extends RealServerInit {}
