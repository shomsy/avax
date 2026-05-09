<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Flows\HandleRestRequest;

use Avax\Components\API\ApiBlueprint\System\Capabilities\RestApi\FilterHandler;
use Avax\Components\API\ApiBlueprint\System\Capabilities\RestApi\PaginationHandler;
use Avax\Components\API\ApiBlueprint\System\Capabilities\RestApi\SortHandler;
use Avax\Components\API\ApiBlueprint\System\PublicSurface\RestResponse;

final readonly class HandleRestRequest
{
    /**
     * @param array<string, mixed> $request
     */
    public function handle(array $request) : RestResponse
    {
        $method = $request['method'] ?? 'GET';
        $uri    = $request['uri'] ?? '/';
        $query  = $request['query'] ?? [];

        $pagination = new PaginationHandler(
            page   : (int) ($query['page'] ?? 1),
            perPage: (int) ($query['per_page'] ?? 15),
        );

        $filters = new FilterHandler($query['filter'] ?? []);
        $sorts   = new SortHandler($query['sort'] ?? []);

        return new RestResponse(
            data: [
                      'method'     => $method,
                      'uri'        => $uri,
                      'pagination' => [
                          'page'     => $pagination->page,
                          'per_page' => $pagination->perPage,
                      ],
                      'filters'    => $filters->all(),
                      'sorts'      => $sorts->all(),
                  ],
        );
    }
}
