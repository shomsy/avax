<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\Aggregate;

use Avax\Components\DataStack\Data\System\Capabilities\Collections\Collection;

final class MinValue
{
    public function __invoke(Collection $collection, string|null $key = null) : mixed
    {
        if ($key === null) {
            return $collection->isEmpty() ? null : min($collection->all());
        }

        $values = array_map(function ($item) use ($key) {
            return is_array($item) ? ($item[$key] ?? null) : ($item->{$key} ?? null);
        }, $collection->all());
        $values = array_filter($values, fn ($v) => $v !== null);

        return empty($values) ? null : min($values);
    }
}