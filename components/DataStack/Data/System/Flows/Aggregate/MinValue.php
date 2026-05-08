<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\Aggregate;

use Avax\Components\DataStack\Data\System\Capabilities\Forms\CollectionForm\Collection;

final class MinValue
{
    public function __invoke(Collection $collection, ?string $key = null): mixed
    {
        if ($collection->isEmpty()) {
            return null;
        }

        if ($key === null) {
            $all = $collection->all();
            assert($all !== []);
            return min($all);
        }

        $values = array_values(array_filter(array_map(
            static fn ($item) => is_array($item) ? ($item[$key] ?? null) : ($item->{$key} ?? null),
            $collection->all(),
        ), static fn ($v): bool => $v !== null));

        if ($values === []) {
            return null;
        }

        return min($values);
    }
}
