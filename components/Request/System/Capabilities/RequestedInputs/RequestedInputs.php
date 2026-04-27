<?php

declare(strict_types=1);

namespace Avax\Components\Request\System\Capabilities\RequestedInputs;

use components\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\RequestedInputs as RealRequestedInputs;

/**
 * RequestedInputs — delegates to the real implementation.
 *
 * The real implementation has: typed access (string, int, float, bool, array, enum),
 * source-aware values (fromQuery, fromBody), sanitization, DTO mapping via reflection.
 */
class RequestedInputs extends RealRequestedInputs {}
