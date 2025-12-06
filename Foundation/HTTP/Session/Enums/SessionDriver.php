<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Enums;

/**
 * Enum `SessionDriver`
 *
 * Represents the available session storage drivers in the application's session handling system.
 * Enumerations here define clearly the specific storage mechanisms supported.
 * This adheres to the Single Responsibility Principle by isolating session-driver-related constants
 * in a self-contained construct. This type-safety ensures scalability and reduces potential usage errors.
 *
 * @package Avax\HTTP\Session\Enums
 */
enum SessionDriver: string
{
    /**
     * Native Session Driver
     *
     * Represents the use of PHP's default session handling mechanism.
     * Suited for applications where the native PHP session engine suffices,
     * such as basic file-based storage without external adapters.
     *
     * @var string
     */
    case Native = 'native';

    /**
     * Array-Based Session Driver
     *
     * Represents a memory-only session storage mechanism where session data
     * is stored in arrays. This is ideal for unit testing or scenarios
     * where persistent state is not required.
     *
     * @var string
     */
    case Array = 'array';
}