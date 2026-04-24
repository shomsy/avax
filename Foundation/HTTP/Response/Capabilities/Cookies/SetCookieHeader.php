<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Cookies;

use Psr\Http\Message\ResponseInterface;

final class SetCookieHeader
{
    public function __invoke(ResponseInterface $response, ResponseCookie $cookie) : ResponseInterface
    {
        return $response->withAddedHeader(name: 'Set-Cookie', value: $cookie->toHeaderValue());
    }
}
