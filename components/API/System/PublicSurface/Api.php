<?php

declare(strict_types=1);

namespace Avax\API\System\PublicSurface;

use Avax\API\OpenAPI\System\PublicSurface\OpenAPI;

final readonly class Api
{
    public static function contracts(): ApiContracts
    {
        return new ApiContracts();
    }

    public static function openapi(): OpenAPI
    {
        return new OpenAPI();
    }
}