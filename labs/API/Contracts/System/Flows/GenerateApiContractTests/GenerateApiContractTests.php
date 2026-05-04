<?php

declare(strict_types=1);

namespace Avax\Labs\API\Contracts\System\Flows\GenerateApiContractTests;

use Avax\Labs\API\Contracts\System\PublicSurface\ApiContract;

final class GenerateApiContractTests
{
    /**
     * @return list<string>
     */
    public function scenariosFor(ApiContract $apiContract): array
    {
        $scenarios = [];

        foreach ($apiContract->endpoints as $endpoint) {
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
