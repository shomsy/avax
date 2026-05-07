<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Flows\VerifyApiCompatibility;

use Avax\Components\API\ApiBlueprint\System\PublicSurface\ApiBlueprintDefinition;

final class VerifyApiCompatibility
{
    /**
     * @return list<string>
     */
    public function scenariosFor(ApiBlueprintDefinition $surface) : array
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
