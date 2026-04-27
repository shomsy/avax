<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Flows\CreateCollection;

final class CreateCollection
{
    public function create(array $items = []): \Avax\Components\Data\System\Capabilities\Collections\Collection
    {
        return new \Avax\Components\Data\System\Capabilities\Collections\Collection($items);
    }
}