<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Create;

use Avax\Components\Data\System\Capabilities\Collections\Internal\Iteration\NormalizedIterable;

final readonly class MakeCollection
{
    public function from(iterable $items = []) : array
    {
        return NormalizedIterable::toArrayPreserveKeys(iterable: $items);
    }
}
