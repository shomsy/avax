<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\System\Capabilities\Uri;

use Stringable;

final readonly class Uri implements Stringable
{
    public function __construct(
        private string  $scheme = '',
        private string  $host = '',
        private string  $path = '/',
        private ?int    $port = null,
        private string  $query = '',
        private string  $fragment = '',
        private string  $user = '',
        private ?string $password = null,
    ) {}

    public function getScheme() : string
    {
        return $this->scheme;
    }

    public function getHost() : string
    {
        return $this->host;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function getPort() : ?int
    {
        return $this->port;
    }

    public function getQuery() : string
    {
        return $this->query;
    }

    public function getFragment() : string
    {
        return $this->fragment;
    }

    public function __toString() : string
    {
        $uri = '';
        if ($this->scheme !== '') {
            $uri .= $this->scheme . '://';
        }

        $uri .= $this->getAuthority();
        $uri .= $this->path;
        if ($this->query !== '') {
            $uri .= '?' . $this->query;
        }

        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }

    public function getAuthority() : string
    {
        $authority = $this->host;
        $userInfo  = $this->getUserInfo();
        if ($userInfo !== '') {
            $authority = $userInfo . '@' . $authority;
        }

        if ($this->port !== null && ! $this->isDefaultPort()) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    public function getUserInfo() : string
    {
        return $this->user . ($this->password !== null ? ':' . $this->password : '');
    }

    private function isDefaultPort() : bool
    {
        return ($this->scheme === 'http' && $this->port === 80) || ($this->scheme === 'https' && $this->port === 443);
    }
}
