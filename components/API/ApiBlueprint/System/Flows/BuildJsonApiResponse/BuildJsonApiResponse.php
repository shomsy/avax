<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Flows\BuildJsonApiResponse;

use Avax\Components\API\ApiBlueprint\System\Capabilities\JsonApi\BuildCompoundDocument;
use Avax\Components\API\ApiBlueprint\System\Capabilities\JsonApi\BuildResourceObject;

final readonly class BuildJsonApiResponse
{
    /**
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    public function single(string $type, string|int $id, array $attributes = []) : array
    {
        $resource = (new BuildResourceObject($type, $id))
            ->attributes($attributes)
            ->build();

        return (new BuildCompoundDocument())
            ->primary($resource)
            ->build();
    }

    /**
     * @param list<array{id: string|int, attributes: array<string, mixed>}> $items
     *
     * @return array<string, mixed>
     */
    public function collection(string $type, array $items = []) : array
    {
        $builder = new BuildCompoundDocument();

        foreach ($items as $item) {
            $resource = (new BuildResourceObject($type, $item['id']))
                ->attributes($item['attributes'])
                ->build();

            $builder->primary($resource);
        }

        return $builder->build();
    }
}
