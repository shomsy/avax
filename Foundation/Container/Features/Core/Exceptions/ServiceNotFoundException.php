<?php

declare(strict_types=1);
namespace Avax\Container\Features\Core\Exceptions;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Exception thrown when a requested service cannot be found.
 */
class ServiceNotFoundException extends Exception implements NotFoundExceptionInterface {}