<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\PublicSurface;

use Avax\Components\API\ApiBlueprint\System\Capabilities\RestApi\FilterHandler;
use Avax\Components\API\ApiBlueprint\System\Capabilities\RestApi\PaginationHandler;
use Avax\Components\API\ApiBlueprint\System\Capabilities\RestApi\ResourceTransformer;
use Avax\Components\API\ApiBlueprint\System\Capabilities\RestApi\RouteRegistrar;
use Avax\Components\API\ApiBlueprint\System\Capabilities\RestApi\SortHandler;
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

    public static function pagination(?int $page = 1, ?int $perPage = 15) : PaginationHandler
    {
        return new PaginationHandler(page: $page ?? 1, perPage: $perPage ?? 15);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public static function filter(array $filters) : FilterHandler
    {
        return new FilterHandler($filters);
    }

    /**
     * @param array<string, string> $sorts
     */
    public static function sort(array $sorts) : SortHandler
    {
        return new SortHandler($sorts);
    }

    /**
     * @param array<string, mixed> $request
     */
    public static function handleRequest(array $request) : RestResponse
    {
        return new HandleRestRequest()->handle($request);
    }

    public static function buildResponse(mixed $data, ?RestResponseMeta $meta = null) : RestResponse
    {
        return new BuildRestResponse()->build(data: $data, meta: $meta);
    }
}
