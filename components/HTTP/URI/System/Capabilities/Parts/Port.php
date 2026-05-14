<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\URI\System\Capabilities\Parts;

use InvalidArgumentException;

final readonly class Port
{
    private int|null $port;

    public function __construct(int|null $port, Scheme|null $scheme = null)
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

    public function value() : int|null
    {
        return $this->port;
    }

    public function __toString() : string
    {
        return $this->port !== null ? (string) $this->port : '';
    }
}
