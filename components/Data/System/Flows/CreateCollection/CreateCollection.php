<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Flows\CreateCollection;

use Avax\Components\Data\System\Capabilities\Collections\Collection;

final class CreateCollection
{
    public function create(array $items = []): Collection
    {
        return new Collection($items);
    }
}