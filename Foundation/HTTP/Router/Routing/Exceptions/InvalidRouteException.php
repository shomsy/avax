<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing\Exceptions;

use InvalidArgumentException;
use Throwable;

/**
 * Thrown when a route definition is malformed or unsafe.
 *
 * Used for path errors, constraint syntax issues, or unsupported parameter configurations.
 */
final class InvalidRouteException extends InvalidArgumentException
{
    /**
     * Default constructor for direct string messages.
     *
     * @param string          $message  Error message.
     * @param int             $code     Error code (optional).
     * @param \Throwable|null $previous Chained exception (optional).
     */
    public function __construct(string $message = '', int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct(message: $message, code: $code, previous: $previous);
    }

    /**
     * Creates an exception for a malformed route path.
     *
     * @param string $path The invalid route path.
     *
     * @return self
     */
    public static function forPath(string $path) : self
    {
        return new self(
            message: sprintf(
                'Invalid route path provided: "%s". Path must begin with "/" and contain valid segments.',
                $path
            )
        );
    }

    /**
     * Creates an exception for a constraint regex that failed to compile.
     *
     * @param string $parameter Parameter name.
     * @param string $pattern   Invalid regex pattern.
     *
     * @return self
     */
    public static function forConstraint(string $parameter, string $pattern) : self
    {
        return new self(
            message: sprintf('Invalid regex constraint for parameter "%s": "%s"', $parameter, $pattern)
        );
    }

    /**
     * Creates an exception for wildcard misuse in route patterns.
     *
     * @param string $path The full route path.
     *
     * @return self
     */
    public static function forInvalidWildcardUsage(string $path) : self
    {
        return new self(
            message: sprintf(
                'Invalid wildcard usage: wildcards must appear only once and at the end. Path: "%s"',
                $path
            )
        );
    }
}
