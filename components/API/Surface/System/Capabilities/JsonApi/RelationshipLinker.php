<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Capabilities\JsonApi;

final readonly class RelationshipLinker
{
    public function __construct(
        private string $baseUrl = '',
    ) {}

    /**
     * @return array{self: string, related: string}
     */
    public function links(string $resourceType, string|int $resourceId, string $relationName) : array
    {
        return [
            'self'    => "{$this->baseUrl}/{$resourceType}/{$resourceId}/relationships/{$relationName}",
            'related' => "{$this->baseUrl}/{$resourceType}/{$resourceId}/{$relationName}",
        ];
    }

    /**
     * @param list<array{type: string, id: string|int}> $references
     *
     * @return list<array{type: string, id: string}>
     */
    public function references(array $references) : array
    {
        return array_map(
            fn (array $ref) : array => $this->reference($ref['type'], $ref['id']),
            $references,
        );
    }

    /**
     * @return array{type: string, id: string}
     */
    public function reference(string $type, string|int $id) : array
    {
        return [
            'type' => $type,
            'id'   => (string) $id,
        ];
    }
}
