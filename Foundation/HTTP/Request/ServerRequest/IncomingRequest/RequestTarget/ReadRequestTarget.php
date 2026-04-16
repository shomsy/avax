<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestTarget;

use Psr\Http\Message\UriInterface;

/**
 * Action Owner: Resolves the request-target according to PSR-7 rules.
 *
 * If no explicit request-target is provided, it must return the
 * origin-form (path + query string) of the URI.
 */
final readonly class ReadRequestTarget
{
    private UriInterface $uri;
    private string|null  $explicitTarget;

    public function __construct(
        string|null  $explicitTarget,
        UriInterface $uri
    )
    {
        $this->explicitTarget = $explicitTarget;
        $this->uri            = $uri;
    }

    public function resolve() : string
    {
        if ($this->explicitTarget !== null) {
            return $this->explicitTarget;
        }

        $target = $this->uri->getPath();

        if ($target === '') {
            $target = '/';
        }

        $query = $this->uri->getQuery();
        if ($query !== '') {
            $target .= '?' . $query;
        }

        return $target;
    }
}
