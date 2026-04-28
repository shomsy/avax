<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\Transform;

use Avax\Components\DataStack\Data\System\Capabilities\Collections\Collection;

final class GroupItemsBy
{
    public function __invoke(Collection $collection, callable|string $callback) : array
    {
        $result = [];
        foreach ($collection->all() as $key => $item) {
            $groupKey            = is_callable($callback) ? $callback($item, $key) : ($item[$callback] ?? $key);
            $result[$groupKey][] = $item;
        }

        return $result;
    }
}