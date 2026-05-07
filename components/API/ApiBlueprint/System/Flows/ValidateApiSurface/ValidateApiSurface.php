<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Flows\ValidateApiSurface;

use Avax\Components\API\Surface\System\PublicSurface\ApiSurfaceDefinition;
use Avax\Components\API\Surface\System\PublicSurface\ApiSurfaceReport;

final class ValidateApiSurface
{
    public function validate(ApiSurfaceDefinition $surface) : ApiSurfaceReport
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

        return new ApiSurfaceReport(surface: $surface, errors: $errors);
    }
}
