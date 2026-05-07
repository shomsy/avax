<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\URI\System\System\Capabilities\Parts;

use InvalidArgumentException;

final readonly class Port
{
    private ?int $port;

    public function __construct(?int $port, ?Scheme $scheme = null)
    {
        if ($port !== null && ($port < 1 || $port > 65535)) {
            throw new InvalidArgumentException("Invalid port number: {$port}");
        }

        if ($port !== null && $scheme !== null && $scheme->isDefaultPort($port)) {
            $this->port = null;
        } else {
            $this->port = $port;
        }
    }

    public function value() : ?int
    {
        return $this->port;
    }

    public function __toString() : string
    {
        return $this->port !== null ? (string) $this->port : '';
    }
}
