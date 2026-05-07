<?php

declare(strict_types=1);

namespace Avax\Components\API\OpenAPI\System\Capabilities\SchemaGeneration;

use Avax\Components\API\OpenAPI\System\Capabilities\ErrorResponseSchemaGeneration\BuildErrorResponseSchema;
use Avax\Components\API\OpenAPI\System\Capabilities\PayloadSchemaGeneration\BuildPayloadSchema;
use Avax\Components\API\OpenAPI\System\Configuration\OpenApiConfiguration;
use Avax\Components\API\Surface\System\Capabilities\EndpointDefinitions\EndpointDefinition;
use Avax\Components\API\Surface\System\Capabilities\ResponseSchemas\ErrorResponseSchema;
use Avax\Components\API\Surface\System\PublicSurface\ApiSurfaceDefinition;

final readonly class BuildOpenApiDocument
{
    public function __construct(
        private OpenApiConfiguration     $configuration,
        private BuildPayloadSchema       $buildPayloadSchema = new BuildPayloadSchema(),
        private BuildErrorResponseSchema $buildErrorResponseSchema = new BuildErrorResponseSchema(),
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(ApiSurfaceDefinition $surface) : array
    {
        $paths = [];

        foreach ($surface->endpoints as $endpoint) {
            $paths[$endpoint->path][strtolower($endpoint->method)] = $this->operation(endpoint: $endpoint);
        }

        ksort($paths);

        return [
            'openapi' => '3.1.0',
            'info'    => [
                'title'   => $this->configuration->title,
                'version' => $this->configuration->version,
            ],
            'servers' => [
                ['url' => $this->configuration->baseUrl],
            ],
            'paths'   => $paths,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function operation(EndpointDefinition $endpoint) : array
    {
        $operation = [
            'summary'     => $endpoint->summary,
            'description' => $endpoint->description,
            'operationId' => $endpoint->operationId,
            'tags'        => [$endpoint->version->label()],
            'responses'   => $this->responses(endpoint: $endpoint),
        ];

        if ($endpoint->requestBody !== null) {
            $operation['requestBody'] = [
                'required' => true,
                'content'  => [
                    'application/json' => [
                        'schema' => $this->buildPayloadSchema->schemaForRequest(requestSchema: $endpoint->requestBody),
                    ],
                ],
            ];
        }

        if ($endpoint->authentication !== null) {
            $operation['security'] = [
                [
                    $endpoint->authentication->type => $endpoint->authentication->scopes,
                ],
            ];
        }

        return array_filter(
            $operation,
            static fn (mixed $value) : bool => $value !== null,
        );
    }

    /**
     * @return array<int|string, mixed>
     */
    private function responses(EndpointDefinition $endpoint) : array
    {
        /** @var array<int|string, mixed> $responses */
        $responses = [];

        if ($endpoint->successResponse !== null) {
            $responses[(string) $endpoint->successResponse->statusCode] = [
                'description' => $endpoint->successResponse->description,
                'content'     => [
                    'application/json' => [
                        'schema' => $this->buildPayloadSchema->schemaForResponse(
                            responseSchema: $endpoint->successResponse,
                        ),
                    ],
                ],
            ];
        }

        foreach ($endpoint->errorResponses as $errorResponse) {
            $responses[(string) $errorResponse->statusCode] = $this->errorResponse(errorResponse: $errorResponse);
        }

        return $responses;
    }

    /**
     * @return array<string, mixed>
     */
    private function errorResponse(ErrorResponseSchema $errorResponse) : array
    {
        return [
            'description' => $errorResponse->description ?? $errorResponse->message,
            'content'     => [
                'application/json' => [
                    'schema' => $this->buildErrorResponseSchema->schemaFor(errorResponse: $errorResponse),
                ],
            ],
        ];
    }
}
