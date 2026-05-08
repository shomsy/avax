<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\PublicSurface;

use Avax\Components\DataStack\Data\System\Capabilities\Arrays\ArrayReader;
use Avax\Components\DataStack\Data\System\Capabilities\Arrays\ArrayWriter;
use Avax\Components\DataStack\Data\System\Capabilities\Collection\Collection;

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
     * @param  array<array-key, mixed>  $items
     * @return Collection
     */
    public function collect(array $items = []): Collection;
}
