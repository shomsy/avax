<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestTarget;

use Psr\Http\Message\UriInterface;

/**
 * ReadRequestTarget - Action owner for resolving the request-target.
 */
final readonly class ReadRequestTarget
{
    public function __construct(
        private string|null  $explicitTarget,
        private UriInterface $uri
    ) {}

    public function resolve() : string
    {
        if ($this->explicitTarget !== null && $this->explicitTarget !== '') {
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
