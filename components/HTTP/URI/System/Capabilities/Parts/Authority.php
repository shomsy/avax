<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\URI\System\Capabilities\Parts;

use Stringable;

final readonly class Authority implements Stringable
{
    public function __construct(
        private Host      $host,
        private Port|null     $port = null,
        private UserInfo|null $userInfo = null,
    ) {}

    public static function fromString(string $authority, Scheme $scheme) : self
    {
        $userInfo = null;
        if (str_contains($authority, '@')) {
            [$userPart, $authority] = explode('@', $authority, 2);
            if (str_contains($userPart, ':')) {
                [$user, $pass] = explode(':', $userPart, 2);
                $userInfo = new UserInfo($user, $pass);
            } else {
                $userInfo = new UserInfo($userPart);
            }
        }

        $port = null;
        if (str_contains($authority, ':')) {
            [$hostPart, $portPart] = explode(':', $authority, 2);
            $host = new Host($hostPart);
            $port = new Port((int) $portPart, $scheme);
        } else {
            $host = new Host($authority);
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
        $authority = (string) $this->host;

        if ($this->userInfo !== null && (string) $this->userInfo !== '') {
            $authority = (string) $this->userInfo . '@' . $authority;
        }

        if ($this->port !== null && $this->port->value() !== null) {
            $authority .= ':' . $this->port->value();
        }

        return $authority;
    }
}
