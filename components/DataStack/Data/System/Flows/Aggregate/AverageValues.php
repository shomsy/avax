<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\Aggregate;

/**
 * Flow to calculate the average of values in a collection.
 */
final readonly class AverageValues
{
    public function __construct(private SumValues $sumValues)
    {
    }

    public function execute(iterable $items, string|callable|null $key = null) : float
    {
        $count = count(is_array($items) ? $items : iterator_to_array($items));
        if ($count === 0) {
            return 0.0;
        }

        return (float) ($this->sumValues->execute($items, $key) / $count);
    }
}
