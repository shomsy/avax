<?php

declare(strict_types=1);

namespace Avax\API\System\Flows\DescribeApiContracts;

use Avax\API\Contracts\System\PublicSurface\ApiContract;

final readonly class DescribeApiContracts
{
    public static function describe(string $path, string $method = 'GET'): ApiContract
    {
        return new ApiContract(
            path: $path,
            method: $method,
            summary: '',
            version: '1.0.0',
            isDeprecated: false,
            requiredAuthentication: false,
            requiredPermissions: [],
        );
    }
}