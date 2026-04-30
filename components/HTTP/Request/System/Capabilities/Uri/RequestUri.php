<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Capabilities\Uri;

use Psr\Http\Message\UriInterface;

final class RequestUri implements UriInterface
{
    public function __construct(private string $s = '', private string $u = '', private string $h = '', private ?int $p = null, private string $pa = '', private string $q = '', private string $f = '') {}

    public function getScheme() : string
    {
        return $this->s;
    }

    public function getAuthority() : string
    {
        return $this->h;
    }

    public function getUserInfo() : string
    {
        return $this->u;
    }

    public function getHost() : string
    {
        return $this->h;
    }

    public function getPort() : ?int
    {
        return $this->p;
    }

    public function getPath() : string
    {
        return $this->pa;
    }

    public function getQuery() : string
    {
        return $this->q;
    }

    public function getFragment() : string
    {
        return $this->f;
    }

    public function withScheme($s) : self
    {
        $c    = clone $this;
        $c->s = $s;

        return $c;
    }

    public function withUserInfo($u, $p = null) : self
    {
        $c    = clone $this;
        $c->u = $u;

        return $c;
    }

    public function withHost($h) : self
    {
        $c    = clone $this;
        $c->h = $h;

        return $c;
    }

    public function withPort($p) : self
    {
        $c    = clone $this;
        $c->p = $p;

        return $c;
    }

    public function withPath($pa) : self
    {
        $c     = clone $this;
        $c->pa = $pa;

        return $c;
    }

    public function withQuery($q) : self
    {
        $c    = clone $this;
        $c->q = $q;

        return $c;
    }

    public function withFragment($f) : self
    {
        $c    = clone $this;
        $c->f = $f;

        return $c;
    }

    public function __toString() : string
    {
        return ($this->s ? $this->s . '://' : '') . $this->h . $this->pa . ($this->q ? '?' . $this->q : '');
    }
}
