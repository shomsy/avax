<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Collections\Create;

use Avax\DataFoundation\Internal\Iteration\NormalizedIterable;

final readonly class MakeCollection
{
    public function from(iterable $items = []) : array
    {
        return NormalizedIterable::toArrayPreserveKeys(iterable: $items);
    }
}
