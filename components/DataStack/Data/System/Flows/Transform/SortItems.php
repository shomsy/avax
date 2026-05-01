<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\Transform;

use Avax\Components\DataStack\Data\System\Capabilities\Collections\Collection;

final class SortItems
{
    public function __invoke(Collection $collection, ?callable $callback = null, bool $descending = false): Collection
    {
        $items = $collection->all();
        if ($callback !== null) {
            $descending ? uksort($items, static fn ($a, $b) => $callback($b, $a)) : uksort($items, $callback);
        } else {
            $descending ? rsort($items) : sort($items);
        }

        return Collection::from($items);
    }
}
