<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Flows\HandleRestRequest;

use Avax\Components\API\ApiBlueprint\System\Capabilities\RestApi\ApplyFilterParameter;
use Avax\Components\API\ApiBlueprint\System\Capabilities\RestApi\ApplyPagination;
use Avax\Components\API\ApiBlueprint\System\Capabilities\RestApi\ApplySortParameter;
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

        $pagination = new ApplyPagination(
            page   : (int) ($query['page'] ?? 1),
            perPage: (int) ($query['per_page'] ?? 15),
        );

        $filters = new ApplyFilterParameter($query['filter'] ?? []);
        $sorts   = new ApplySortParameter($query['sort'] ?? []);

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
