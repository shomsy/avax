<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Flows\BuildJsonApiResponse;

use Avax\Components\API\Surface\System\Capabilities\JsonApi\CompoundDocumentBuilder;
use Avax\Components\API\Surface\System\Capabilities\JsonApi\ResourceObjectBuilder;

final readonly class BuildJsonApiResponse
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function single(string $type, string|int $id, array $attributes = []) : array
    {
        $resource = (new ResourceObjectBuilder($type, $id))
            ->attributes($attributes)
            ->build();

        return (new CompoundDocumentBuilder())
            ->primary($resource)
            ->build();
    }

    /**
     * @param list<array{id: string|int, attributes: array<string, mixed>}> $items
     */
    public function collection(string $type, array $items = []) : array
    {
        $builder = new CompoundDocumentBuilder();

        foreach ($items as $item) {
            $resource = (new ResourceObjectBuilder($type, $item['id']))
                ->attributes($item['attributes'])
                ->build();

            $builder->primary($resource);
        }

        return $builder->build();
    }
}
