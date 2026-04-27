<?php

declare(strict_types=1);

namespace Avax\Components\Response\System\Flows\BuildResponse;

use Avax\HTTP\Response\Flows\BuildResponse\BuildResponse as RealBuildResponse;

/**
 * BuildResponse — delegates to the real implementation.
 *
 * The real implementation has: status validation, reason phrase resolution,
 * protocol version normalization, body normalization, proper 204/304 handling.
 */
class BuildResponse extends RealBuildResponse {}
