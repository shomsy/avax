<?php

declare(strict_types=1);

namespace Avax\Components\Request\System\Capabilities\RequestedInputs\Inputs;

use components\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs\Inputs as RealInputs;

/**
 * Inputs — delegates to the real implementation.
 *
 * The real implementation has: lazy merged view, source-aware values,
 * body-wins-over-query semantics, InputValue with provenance tracking.
 */
class Inputs extends RealInputs {}
