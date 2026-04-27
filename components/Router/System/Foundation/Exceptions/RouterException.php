<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\Foundation\Exceptions;

use RuntimeException;

/**
 * Base exception for all Router component errors.
 */
class RouterException extends RuntimeException implements RouterExceptionInterface {}
