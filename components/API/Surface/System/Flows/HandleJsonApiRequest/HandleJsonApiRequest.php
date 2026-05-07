<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Flows\HandleJsonApiRequest;

use Avax\Components\API\Surface\System\Capabilities\JsonApi\CompoundDocumentBuilder;
use Avax\Components\API\Surface\System\Capabilities\JsonApi\ResourceObjectBuilder;

final readonly class HandleJsonApiRequest
{
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

    private function buildCollectionResponse(string $type, array $attributes) : array
    {
        return [
            'data' => [],
            'type' => $type,
        ];
    }

    private function buildSingleResponse(string $type, string|int $id, array $attributes) : array
    {
        $builder = new ResourceObjectBuilder($type, $id);
        $builder->attributes($attributes);

        return (new CompoundDocumentBuilder())
            ->primary($builder->build())
            ->build();
    }
}
