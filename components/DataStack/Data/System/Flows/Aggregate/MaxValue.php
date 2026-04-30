<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\Aggregate;

use Avax\Components\DataStack\Data\System\Capabilities\Collections\Collection;

final class MaxValue
{
    public function __invoke(Collection $collection, string|null $key = null) : mixed
    {
        if ($key === null) {
            return $collection->isEmpty() ? null : max($collection->all());
        }

        $values = array_map(static fn ($item) => is_array($item) ? ($item[$key] ?? null) : ($item->{$key} ?? null), $collection->all());
        $values = array_filter($values, static fn ($v) : bool => $v !== null);

        return $values === [] ? null : max($values);
    }
}
