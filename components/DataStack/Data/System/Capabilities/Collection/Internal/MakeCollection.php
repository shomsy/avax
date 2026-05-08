<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collection\Internal;

use Avax\Components\DataStack\Data\System\Capabilities\Collection\Internal\NormalizedIterable;

final readonly class MakeCollection
{
    public function from(iterable $items = []): array
    {
        return NormalizedIterable::toArrayPreserveKeys(iterable: $items);
    }
}
