<?php

declare(strict_types=1);

namespace Avax\Components\API\OpenAPI\System\Capabilities\PayloadSchemaGeneration;

use Avax\Components\API\ApiBlueprint\System\Capabilities\RequestSchemas\RequestSchema;
use Avax\Components\API\ApiBlueprint\System\Capabilities\ResponseSchemas\ResponseSchema;

final readonly class BuildPayloadSchema
{
    /**
     * @return array<string, mixed>
     */
    public function schemaForRequest(RequestSchema $requestSchema) : array
    {
        return $this->schema(
            name       : $requestSchema->name,
            description: $requestSchema->description,
            properties : $requestSchema->properties,
            required   : $requestSchema->required,
        );
    }

    /**
     * @param array<string, mixed> $properties
     * @param list<string>         $required
     *
     * @return array<string, mixed>
     */
    private function schema(string $name, string $description, array $properties, array $required = []) : array
    {
        $schema = [
            'type'        => 'object',
            'title'       => $name,
            'description' => $description,
            'properties'  => $properties,
        ];

        if ($required !== []) {
            $schema['required'] = $required;
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    public function schemaForResponse(ResponseSchema $responseSchema) : array
    {
        return $this->schema(
            name       : $responseSchema->name,
            description: $responseSchema->description,
            properties : $responseSchema->properties,
        );
    }
}
