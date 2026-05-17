<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\ORM\DataLoader;

interface DataLoaderInterface
{
    public function load(array $keys): array;

    public function for(string $relation): self;
}
