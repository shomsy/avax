<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Contracts;

use Psr\Log\LoggerInterface;

/**
 * Interface SessionLoggerInterface
 *
 * This interface defines a specialized logging contract that extends the PSR-3 LoggerInterface,
 * specifically for session-related logging functionalities. It enables the implementation of a unified
 * session logging system to maintain high observability and track session-related events.
 *
 * @see \Psr\Log\LoggerInterface For the standard logging contract, this interface extends.
 */
interface SessionLoggerInterface extends LoggerInterface
{
    // By extending LoggerInterface, this interface inherits the PSR-3 logging methods,
    // such as `emergency()`, `alert()`, `critical()`, `error()`, `warning()`, `notice()`, `info()`,
    // and `debug()`. This enables session-specific logging needs to seamlessly integrate with
    // any PSR-3-compliant logging system.
}