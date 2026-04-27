<?php

declare(strict_types=1);

namespace Avax\Components\Request\System\Capabilities\RequestTarget;

use components\HTTP\Request\ServerRequest\IncomingRequest\RequestTarget\ReadRequestTarget as RealReadRequestTarget;

/**
 * ReadRequestTarget — delegates to the real implementation.
 */
class ReadRequestTarget extends RealReadRequestTarget {}
