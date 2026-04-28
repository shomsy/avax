<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\URI\Parts;

use InvalidArgumentException;
use Stringable;

/**
 * Represents the authority part of a URI.
 */
final readonly class Authority implements Stringable
{
    private UserInfo|null $userInfo;
    private Host          $host;
    private Port|null     $port;

    public function __construct(Host $host, Port|null $port = null, UserInfo|null $userInfo = null)
    {
        $this->host     = $host;
        $this->port     = $port;
        $this->userInfo = $userInfo;
    }

    public static function fromString(string $authority, Scheme $scheme) : self
    {
        $parts = parse_url('https://' . $authority);
        if ($parts === false) {
            throw new InvalidArgumentException(message: 'Invalid authority: ' . $authority);
        }

        $host     = new Host(host: $parts['host'] ?? '');
        $port     = isset($parts['port']) ? new Port(port: $parts['port'], scheme: $scheme) : null;
        $userInfo = null;
        if (isset($parts['user'])) {
            $userInfo = new UserInfo(user: $parts['user'], password: $parts['pass'] ?? null);
        }

        return new self(host: $host, port: $port, userInfo: $userInfo);
    }

    public function host() : Host
    {
        return $this->host;
    }

    public function port() : Port|null
    {
        return $this->port;
    }

    public function userInfo() : UserInfo|null
    {
        return $this->userInfo;
    }

    public function __toString() : string
    {
        $authority = '';
        if ($this->userInfo !== null) {
            $authority .= $this->userInfo . '@';
        }
        $authority .= $this->host;
        if ($this->port !== null && $this->port->value() !== null) {
            $authority .= ':' . $this->port->value();
        }

        return $authority;
    }
}