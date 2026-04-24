<?php

declare(strict_types=1);

namespace Avax\DataModeling\Collections\Create;

use Avax\DataModeling\Collections\Internal\NormalizedIterable;

final readonly class MakeCollection
{
    public function from(iterable $items = []) : array
    {
        return NormalizedIterable::toArrayPreserveKeys(iterable: $items);
    }
}
