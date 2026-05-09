<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Flows\HandleJsonApiRequest;

use Avax\Components\API\ApiBlueprint\System\Capabilities\JsonApi\BuildCompoundDocument;
use Avax\Components\API\ApiBlueprint\System\Capabilities\JsonApi\BuildResourceObject;

final readonly class HandleJsonApiRequest
{
    /**
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function handle(array $request) : array
    {
        $type       = $request['type'] ?? 'resource';
        $id         = $request['id'] ?? null;
        $attributes = $request['attributes'] ?? [];

        if ($id === null) {
            return $this->buildCollectionResponse($type, $attributes);
        }

        return $this->buildSingleResponse($type, $id, $attributes);
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    private function buildCollectionResponse(string $type, array $attributes) : array
    {
        return [
            'data' => [],
            'type' => $type,
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    private function buildSingleResponse(string $type, string|int $id, array $attributes) : array
    {
        $builder = new BuildResourceObject($type, $id);
        $builder->attributes($attributes);

        return (new BuildCompoundDocument())
            ->primary($builder->build())
            ->build();
    }
}
