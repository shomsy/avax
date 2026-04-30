<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Foundation\Failure;

use RuntimeException;

/**
 * Exception thrown when request processing fails.
 */
final class RequestFailure extends RuntimeException {}
