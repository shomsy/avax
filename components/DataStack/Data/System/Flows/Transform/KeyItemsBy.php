<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\Transform;

use Avax\Components\DataStack\Data\System\Capabilities\Forms\CollectionForm\Collection;

final class KeyItemsBy
{
    public function __invoke(Collection $collection, callable|string $callback): Collection
    {
        $result = [];
        foreach ($collection->all() as $key => $item) {
            $newKey = is_callable($callback) ? $callback($item, $key) : ($item[$callback] ?? $key);
            $result[$newKey] = $item;
        }

        return Collection::make($result);
    }
}
