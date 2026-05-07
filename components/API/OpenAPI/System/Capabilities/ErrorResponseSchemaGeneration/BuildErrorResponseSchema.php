<?php

declare(strict_types=1);

namespace Avax\Components\API\OpenAPI\System\Capabilities\ErrorResponseSchemaGeneration;

use Avax\Components\API\Surface\System\Capabilities\ResponseSchemas\ErrorResponseSchema;

final readonly class BuildErrorResponseSchema
{
    /**
     * @return array<string, mixed>
     */
    public function schemaFor(ErrorResponseSchema $errorResponse) : array
    {
        return [
            'type'        => 'object',
            'title'       => $errorResponse->code,
            'description' => $errorResponse->description ?? $errorResponse->message,
            'properties'  => [
                'code'    => ['type' => 'string', 'example' => $errorResponse->code],
                'message' => ['type' => 'string', 'example' => $errorResponse->message],
                'details' => ['type' => 'object'],
            ],
            'required'    => ['code', 'message'],
        ];
    }
}
