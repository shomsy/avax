<?php

declare(strict_types=1);

use Avax\Components\DataStack\Data\System\Capabilities\Collections\Arrhae;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Collection;

if (! function_exists('arrhae')) {
    function arrhae(array $array) : Arrhae
    {
        return new Arrhae(items: $array);
    }
}

if (! function_exists('collect')) {
    function collect(iterable $items = []) : Collection
    {
        return new Collection(items: $items);
    }
}
