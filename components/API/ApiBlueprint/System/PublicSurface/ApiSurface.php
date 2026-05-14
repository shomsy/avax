<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\PublicSurface;

use Avax\Components\API\ApiBlueprint\System\Capabilities\RestApi\ApplyFilterParameter;
use Avax\Components\API\ApiBlueprint\System\Capabilities\RestApi\ApplyPagination;
use Avax\Components\API\ApiBlueprint\System\Capabilities\RestApi\ApplySortParameter;
use Avax\Components\API\ApiBlueprint\System\Capabilities\RestApi\ResourceTransformer;
use Avax\Components\API\ApiBlueprint\System\Capabilities\RestApi\RouteRegistrar;
use Avax\Components\API\ApiBlueprint\System\Flows\BuildRestResponse\BuildRestResponse;
use Avax\Components\API\ApiBlueprint\System\Flows\HandleRestRequest\HandleRestRequest;

final readonly class ApiSurface
{
    public static function registrar() : RouteRegistrar
    {
        return new RouteRegistrar();
    }

    public static function transformer() : ResourceTransformer
    {
        return new ResourceTransformer();
    }

    public static function pagination(int|null $page = 1, int|null $perPage = 15) : ApplyPagination
    {
        return new ApplyPagination(page: $page ?? 1, perPage: $perPage ?? 15);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public static function filter(array $filters) : ApplyFilterParameter
    {
        return new ApplyFilterParameter($filters);
    }

    /**
     * @param array<string, string> $sorts
     */
    public static function sort(array $sorts) : ApplySortParameter
    {
        return new ApplySortParameter($sorts);
    }

    /**
     * @param array<string, mixed> $request
     */
    public static function handleRequest(array $request) : RestResponse
    {
        return new HandleRestRequest()->handle($request);
    }

    public static function buildResponse(mixed $data, RestResponseMeta|null $meta = null) : RestResponse
    {
        return new BuildRestResponse()->build(data: $data, meta: $meta);
    }
}
