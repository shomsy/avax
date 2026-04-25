<?php

declare(strict_types=1);

namespace Avax\HTTP\URI\Parts;

use InvalidArgumentException;

/**
 * Represents a URI port.
 */
final readonly class Port
{
    private int|null $port;

    public function __construct(int|null $port, Scheme $scheme)
    {
        $this->port = $this->validate(port: $port, scheme: $scheme);
    }

    private function validate(int|null $port, Scheme $scheme) : int|null
    {
        if ($port === null) {
            return null;
        }

        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException(message: 'Invalid port: ' . $port);
        }

        if ($scheme->isDefaultPort(port: $port)) {
            return null;
        }

        return $port;
    }

    public function value() : int|null
    {
        return $this->port;
    }
}