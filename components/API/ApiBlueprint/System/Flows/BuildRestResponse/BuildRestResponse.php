<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Flows\BuildRestResponse;

use Avax\Components\API\ApiBlueprint\System\PublicSurface\RestResponse;
use Avax\Components\API\ApiBlueprint\System\PublicSurface\RestResponseMeta;

final readonly class BuildRestResponse
{
    public function build(mixed $data, ?RestResponseMeta $meta = null) : RestResponse
    {
        $responseData = is_array($data) ? $data : (array) $data;

        return new RestResponse(
            data  : $responseData,
            status: 200,
            meta  : $meta,
        );
    }
}
