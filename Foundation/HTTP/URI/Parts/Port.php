<?php

declare(strict_types=1);

namespace Avax\HTTP\URI\Parts;

use InvalidArgumentException;

/**
 * Represents a URI port.
 */
final readonly class Port
{
    private ?int $port;

    public function __construct(?int $port, Scheme $scheme)
    {
        $this->port = $this->validate($port, $scheme);
    }

    private function validate(?int $port, Scheme $scheme) : ?int
    {
        if ($port === null) {
            return null;
        }

        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException('Invalid port: ' . $port);
        }

        if ($scheme->isDefaultPort($port)) {
            return null;
        }

        return $port;
    }

    public function value() : ?int
    {
        return $this->port;
    }
}