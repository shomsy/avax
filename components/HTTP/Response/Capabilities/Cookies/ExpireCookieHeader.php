<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Capabilities\Cookies;

use Psr\Http\Message\ResponseInterface;

final class ExpireCookieHeader
{
    public function __invoke(ResponseInterface $response, string $name, string|null $path = null, string|null $domain = null) : ResponseInterface
    {
        $path ??= '/';

        return new SetCookieHeader()(
            response: $response,
            cookie  : ResponseCookie::expired(name: $name, path: $path, domain: $domain),
        );
    }
}
