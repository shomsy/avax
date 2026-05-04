<?php

declare(strict_types=1);

namespace Avax\Labs\API\Contracts\System\Flows\ValidateApiContracts;

use Avax\Labs\API\Contracts\System\PublicSurface\ApiContract;
use Avax\Labs\API\Contracts\System\PublicSurface\ApiContractReport;

final class ValidateApiContracts
{
    public function validate(ApiContract $apiContract): ApiContractReport
    {
        $errors = [];
        $seenOperations = [];

        foreach ($apiContract->endpoints as $endpoint) {
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

        return new ApiContractReport(contract: $apiContract, errors: $errors);
    }
}
