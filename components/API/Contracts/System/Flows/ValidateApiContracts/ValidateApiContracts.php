<?php

declare(strict_types=1);

namespace Avax\API\Contracts\System\Flows\ValidateApiContracts;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;

final readonly class ValidateApiContracts
{
    public static function validate(RequestInterface $request): ApiContractReport
    {
        return new ApiContractReport(
            contractId: $request->path(),
            path: $request->path(),
            method: $request->method(),
            isValid: true,
            violations: [],
            warnings: [],
        );
    }
}