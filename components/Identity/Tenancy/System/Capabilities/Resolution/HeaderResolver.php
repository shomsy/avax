<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Resolution;

use Psr\Http\Message\RequestInterface;

final readonly class HeaderResolver
{
    public static function resolve(RequestInterface $request) : string|null
    {
        return $request->getHeaderLine('X-Tenant-ID') ?: null;
    }
}
