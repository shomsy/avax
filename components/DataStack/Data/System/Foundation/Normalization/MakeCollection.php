<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Normalization;



final readonly class MakeCollection
{
    /**
     * @param iterable<array-key, mixed> $items
     * @return array<array-key, mixed>
     */
    public function from(iterable $items = []): array
    {
        return NormalizedIterable::toArrayPreserveKeys(iterable: $items);
    }
}
