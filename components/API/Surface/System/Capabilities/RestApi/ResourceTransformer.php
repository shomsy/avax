<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Capabilities\RestApi;

use Closure;

final class ResourceTransformer
{
    /**
     * @var array<string, Closure>
     */
    private array $transformers = [];

    public function define(string $type, Closure $transformer) : self
    {
        $this->transformers[$type] = $transformer;

        return $this;
    }

    /**
     * @param list<mixed> $items
     *
     * @return list<array<string, mixed>>
     */
    public function transformCollection(string $type, array $items) : array
    {
        return array_map(
            fn (mixed $item) : array => $this->transform($type, $item),
            $items,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function transform(string $type, mixed $data) : array
    {
        $transformer = $this->transformers[$type] ?? null;

        if ($transformer === null) {
            return is_array($data) ? $data : (array) $data;
        }

        return $transformer($data);
    }
}
