<?php

declare(strict_types=1);

use Avax\DataFoundation\Arrhae;
use Avax\DataFoundation\Collection;

if (! function_exists(function: 'arrhae')) {
    /**
     * Creates a new Arrhae instance for advanced array manipulation.
     *
     * @param array $array The source array.
     *
     * @return Arrhae
     */
    function arrhae(array $array) : Arrhae
    {
        return new Arrhae(items: $array);
    }
}

if (! function_exists(function: 'collect')) {
    /**
     * Creates a new Collection instance.
     *
     * @param iterable $items
     *
     * @return Collection
     */
    function collect(iterable $items = []) : Collection
    {
        return new Collection(items: $items);
    }
}
