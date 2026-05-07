<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\System\Capabilities\Resolution;

use Avax\Components\HTTP\Request\System\System\PublicSurface\RequestInterface;

final readonly class DomainResolver
{
    public static function resolve(RequestInterface $request) : ?string
    {
        $host  = $request->getUri()->getHost();
        $parts = explode('.', $host);

        if (count($parts) > 2) {
            return $parts[0];
        }

        return null;
    }
}
