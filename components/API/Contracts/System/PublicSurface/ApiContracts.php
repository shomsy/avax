<?php

declare(strict_types=1);

namespace Avax\API\Contracts\System\PublicSurface;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;

final readonly class ApiContracts
{
    public static function describe(string $path, string $method = 'GET'): ApiContract
    {
        return ApiContract::describe(path: $path, method: $method);
    }

    public static function validate(RequestInterface $request): ApiContractReport
    {
        return ValidateApiContracts::validate(request: $request);
    }

    public static function hasBreakingChanges(ApiContract $old, ApiContract $new): bool
    {
        return DetectBreakingApiChanges::hasBreakingChanges(old: $old, new: $new);
    }

    public static function generateTests(ApiContract $contract): string
    {
        return GenerateApiContractTests::generate(contract: $contract);
    }
}