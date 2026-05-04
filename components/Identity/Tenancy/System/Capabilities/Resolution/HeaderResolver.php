<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Resolution;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;

final readonly class HeaderResolver
{
    public static function resolve(RequestInterface $request): ?string
    {
        return $request->getHeaderLine('X-Tenant-ID') ?: null;
    }
}