<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Flows\GenerateApiCompatibilityChecks;

use Avax\Components\API\Surface\System\PublicSurface\ApiSurfaceDefinition;

final class GenerateApiCompatibilityChecks
{
    /**
     * @return list<string>
     */
    public function scenariosFor(ApiSurfaceDefinition $surface) : array
    {
        $scenarios = [];

        foreach ($surface->endpoints as $endpoint) {
            $scenarios[] = sprintf(
                'assert %s %s returns %d for version %s',
                $endpoint->method,
                $endpoint->path,
                $endpoint->successResponse->statusCode ?? 0,
                $endpoint->version->label(),
            );
        }

        return $scenarios;
    }
}
