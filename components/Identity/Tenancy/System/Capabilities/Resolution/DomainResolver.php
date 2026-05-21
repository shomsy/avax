<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Resolution;

use Psr\Http\Message\RequestInterface;

final readonly class DomainResolver
{
    public static function resolve(RequestInterface $request) : string|null
    {
        $host  = $request->getUri()->getHost();
        $parts = explode('.', $host);

        if (count($parts) > 2) {
            return $parts[0];
        }

        return null;
    }
}
