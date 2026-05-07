<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Flows\VerifyApiBlueprint;

use Avax\Components\API\ApiBlueprint\System\PublicSurface\ApiBlueprintDefinition;
use Avax\Components\API\ApiBlueprint\System\PublicSurface\ApiBlueprintReport;

final class VerifyApiBlueprint
{
    public function validate(ApiBlueprintDefinition $surface) : ApiBlueprintReport
    {
        $errors         = [];
        $seenOperations = [];

        foreach ($surface->endpoints as $endpoint) {
            if ($endpoint->path === '') {
                $errors[] = 'Endpoint path must not be empty.';
            }

            if ($endpoint->method === '') {
                $errors[] = sprintf('Endpoint %s must declare an HTTP method.', $endpoint->path);
            }

            if ($endpoint->successResponse === null) {
                $errors[] = sprintf('Endpoint %s %s must declare a success response.', $endpoint->method, $endpoint->path);
            }

            if ($endpoint->operationId !== null) {
                if (isset($seenOperations[$endpoint->operationId])) {
                    $errors[] = sprintf('Operation id %s is duplicated.', $endpoint->operationId);
                }

                $seenOperations[$endpoint->operationId] = true;
            }
        }

        return new ApiBlueprintReport(surface: $surface, errors: $errors);
    }
}
