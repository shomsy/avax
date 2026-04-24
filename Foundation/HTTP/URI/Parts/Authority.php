<?php

declare(strict_types=1);

namespace Avax\HTTP\URI\Parts;

use SensitiveParameter;
use Stringable;

/**
 * Represents the authority part of a URI.
 */
final readonly class Authority implements Stringable
{
    private ?UserInfo $userInfo;
    private Host      $host;
    private ?Port     $port;

    public function __construct(Host $host, ?Port $port = null, ?UserInfo $userInfo = null)
    {
        $this->host     = $host;
        $this->port     = $port;
        $this->userInfo = $userInfo;
    }

    public static function fromString(string $authority, Scheme $scheme) : self
    {
        $parts = parse_url('https://' . $authority);
        if ($parts === false) {
            throw new \InvalidArgumentException('Invalid authority: ' . $authority);
        }

        $host     = new Host($parts['host'] ?? '');
        $port     = isset($parts['port']) ? new Port($parts['port'], $scheme) : null;
        $userInfo = null;
        if (isset($parts['user'])) {
            $userInfo = new UserInfo($parts['user'], $parts['pass'] ?? null);
        }

        return new self($host, $port, $userInfo);
    }

    public function host() : Host
    {
        return $this->host;
    }

    public function port() : ?Port
    {
        return $this->port;
    }

    public function userInfo() : ?UserInfo
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