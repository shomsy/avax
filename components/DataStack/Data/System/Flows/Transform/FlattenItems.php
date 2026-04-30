<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\Transform;

use Avax\Components\DataStack\Data\System\Capabilities\Collections\Collection;

final class FlattenItems
{
    public function __invoke(Collection $collection, int $depth = -1) : Collection
    {
        $result = [];
        $this->flattenRecursive($collection->all(), $result, $depth);

        return Collection::from($result);
    }

    private function flattenRecursive(array $items, array &$result, int $depth) : void
    {
        foreach ($items as $item) {
            if (is_array($item) && $depth !== 0) {
                $this->flattenRecursive($item, $result, $depth - 1);
            } else {
                $result[] = $item;
            }
        }
    }
}
