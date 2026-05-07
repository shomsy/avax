<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\System\Capabilities\Status;

final class ResolveStatusReason
{
    private const array PHRASES
        = [
            200 => 'OK',
            404 => 'Not Found',
            500 => 'Internal Server Error',
        ];

    public function resolve(int $code) : string
    {
        return self::PHRASES[$code] ?? 'Unknown Status';
    }
}
