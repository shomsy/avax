<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\PublicSurface;

use Avax\Components\Data\System\Capabilities\Arrays\ArrayReader;
use Avax\Components\Data\System\Capabilities\Arrays\ArrayWriter;
use Avax\Components\Data\System\Capabilities\Collections\Collection;

/**
 * Data PublicSurface contract.
 *
 * Provides access to all data manipulation capabilities:
 * - Array reading with dot-notation and type-safe accessors
 * - Array writing with nested path support
 * - Collection creation for fluent data transformations
 */
interface DataInterface
{
    /**
     * Get the type-safe array reader.
     */
    public function array(): ArrayReader;

    /**
     * Get the array writer for mutation operations.
     */
    public function write(): ArrayWriter;

    /**
     * Create a new Collection from an array.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param array<TKey, TValue> $items
     *
     * @return Collection<TKey, TValue>
     */
    public function collect(array $items = []) : Collection;
}