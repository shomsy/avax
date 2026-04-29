<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * PSR-15 style request handler interface.
 *
 * Used by flow classes that handle HTTP requests and produce responses.
 */
interface RequestHandlerInterface
{
    public function handle(RequestInterface $request) : ResponseInterface;
}
