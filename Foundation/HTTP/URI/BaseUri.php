<?php

declare(strict_types=1);

namespace Avax\HTTP\URI;

use SensitiveParameter;
use Stringable;

/**
 * Abstract class BaseUri
 * Represents a generic URI and provides mechanisms to construct and validate it.
 */
abstract class BaseUri implements Stringable
{
    protected string|null $password = null;
    protected string      $user     = '';
    protected string      $fragment = '';
    protected string      $query    = '';
    protected int|null    $port     = null;
    protected string      $path     = '/';
    protected string      $host     = '';
    protected string      $scheme   = '';

    /**
     * BaseUri constructor.
     * Initializes the URI components.
     */
    public function __construct(
        string                            $scheme = '',
        string                            $host = '',
        string                            $path = '/',
        int|null                          $port = null,
        string                            $query = '',
        string                            $fragment = '',
        string                            $user = '',
        #[SensitiveParameter] string|null $password = null
    )
    {
        $this->scheme   = $scheme;
        $this->host     = $host;
        $this->path     = $path;
        $this->port     = $port;
        $this->query    = $query;
        $this->fragment = $fragment;
        $this->user     = $user;
        $this->password = $password;
    }

    /**
     * Converts the URI to a string.
     */
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

    /**
     * Returns the authority part of the URI.
     */
    public function getAuthority() : string
    {
        $authority = $this->host;
        if ($this->user !== '') {
            $authority = $this->user . ($this->password !== null ? ':' . $this->password : '') . ('@' . $authority);
        }

        if ($this->port !== null && ! $this->isDefaultPort()) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * Checks if the port is the default for the scheme.
     */
    protected function isDefaultPort() : bool
    {
        return ($this->scheme === 'http' && $this->port === 80) || ($this->scheme === 'https' && $this->port === 443);
    }
}
