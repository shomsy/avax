<?php

declare(strict_types=1);

namespace Gemini\DataHandling\ObjectHandling\Collections;

use ArrayIterator;
use Closure;
use Gemini\DataHandling\ArrayHandling\Traits\AbstractDependenciesTrait;
use Gemini\DataHandling\ArrayHandling\Traits\AggregationTrait;
use Gemini\DataHandling\ArrayHandling\Traits\ArrayAccessTrait;
use Gemini\DataHandling\ArrayHandling\Traits\ArrayConversionTrait;
use Gemini\DataHandling\ArrayHandling\Traits\CollectionWalkthroughTrait;
use Gemini\DataHandling\ArrayHandling\Traits\ConditionalsTrait;
use Gemini\DataHandling\ArrayHandling\Traits\DebugTrait;
use Gemini\DataHandling\ArrayHandling\Traits\LazyEvaluationTrait;
use Gemini\DataHandling\ArrayHandling\Traits\MacrosTrait;
use Gemini\DataHandling\ArrayHandling\Traits\ManageItemsTrait;
use Gemini\DataHandling\ArrayHandling\Traits\MetaInfoTrait;
use Gemini\DataHandling\ArrayHandling\Traits\OrderManipulationTrait;
use Gemini\DataHandling\ArrayHandling\Traits\PartitioningTrait;
use Gemini\DataHandling\ArrayHandling\Traits\SetOperationsTrait;
use Gemini\DataHandling\ArrayHandling\Traits\SortOperationsTrait;
use Gemini\DataHandling\ArrayHandling\Traits\StructureConversionTrait;
use Gemini\DataHandling\ArrayHandling\Traits\TransformationTrait;
use Traversable;

/**
 * Collection class providing utilities for array manipulations.
 * Combines traits to enable sorting, filtering, partitioning, and more.
 */
class Collection extends BaseCollection implements CollectionInterface
{
    use AbstractDependenciesTrait;
    use AggregationTrait;
    use ArrayAccessTrait;
    use ArrayConversionTrait;
    use CollectionWalkthroughTrait;
    use ConditionalsTrait;
    use DebugTrait;
    use LazyEvaluationTrait;
    use MacrosTrait;
    use ManageItemsTrait;
    use MetaInfoTrait;
    use OrderManipulationTrait;
    use PartitioningTrait;
    use SetOperationsTrait;
    use SortOperationsTrait;
    use StructureConversionTrait;
    use TransformationTrait;

    /**
     * Internal storage for collection items specific to Collection.
     */
    protected array $elements = [];

    /**
     * Collection constructor.
     *
     * Initializes the collection with an optional set of items.
     *
     * @param iterable $items Initial set of items to populate the collection.
     */
    public function __construct(iterable $items = [])
    {
        parent::__construct(items: $items);
        $this->setItems(items: $this->convertToArray(items: $items));
    }

    /**
     * Set the internal elements array.
     *
     * @param array $items The items to set.
     *
     * @return static This collection instance.
     */
    public function setItems(iterable $items) : static
    {
        $this->elements = $items;

        return $this;
    }

    /**
     * Invokes a callback on the collection and returns the collection itself.
     *
     * Useful for debugging and inspection without altering chainability.
     *
     * @param Closure $callback The callback to execute.
     *
     * @return static The current collection instance for method chaining.
     */
    public function tap(Closure $callback) : static
    {
        $callback($this);

        return $this;
    }

    /**
     * Retrieves the first item that matches a given key-value pair.
     *
     * @param string $key   The key to search for.
     * @param mixed  $value The value to compare against.
     *
     * @return mixed|null The first matching item or null if not found.
     */
    public function firstWhere(string $key, mixed $value) : mixed
    {
        foreach ($this->getItems() as $item) {
            if (($item[$key] ?? null) === $value) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Get the internal elements array.
     *
     * @return array The elements in the collection.
     */
    public function getItems() : array
    {
        return $this->elements;
    }

    /**
     * Determine the maximum value in the collection for a specific key.
     *
     * @param string|null $key The key to consider.
     *
     * @return mixed The maximum value or null if the collection is empty.
     */
    public function max(string|null $key = null) : mixed
    {
        return max(
            array_map(
                static fn($item) => $key !== null && $key !== '' && $key !== '0' ? ($item[$key] ?? null) : $item,
                $this->getItems(),
            ),
        );
    }

    /**
     * Determine the minimum value in the collection for a specific key.
     *
     * @param string|null $key The key to consider.
     *
     * @return mixed The minimum value or null if the collection is empty.
     */
    public function min(string|null $key = null) : mixed
    {
        return min(
            array_map(
                static fn($item) => $key !== null && $key !== '' && $key !== '0' ? ($item[$key] ?? null) : $item,
                $this->getItems(),
            ),
        );
    }

    /**
     * Determine the mode (most frequent value) in the collection for a specific key.
     *
     * @param string|null $key The key to consider.
     *
     * @return string|int|null The mode or null if the collection is empty.
     */
    public function mode(string|null $key = null) : string|int|null
    {
        $counts = array_count_values(
            array_map(
                static fn($item) => $key !== null && $key !== '' && $key !== '0' ? ($item[$key] ?? null) : $item,
                $this->getItems(),
            ),
        );
        arsort($counts);

        return array_key_first($counts);
    }

    /**
     * Count items in the collection based on a given callback.
     *
     * @param Closure $callback The callback to determine the key for counting.
     *
     * @return static A new collection containing count values.
     */
    public function countBy(Closure $callback) : static
    {
        $counts = [];
        foreach ($this->getItems() as $item) {
            $key          = $callback($item);
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        return new static($counts);
    }

    /**
     * Appends a value to the end of the collection.
     *
     * @param mixed $value The value to append.
     *
     * @return static The current collection instance for method chaining.
     */
    public function append(mixed $value) : static
    {
        $this->elements[] = $value;

        return $this;
    }

    /**
     * Prepends a value to the beginning of the collection.
     *
     * @param mixed $value The value to prepend.
     *
     * @return static The current collection instance for method chaining.
     */
    public function prepend(mixed $value) : static
    {
        array_unshift($this->elements, $value);

        return $this;
    }

    /**
     * Merges the collection with another collection or array.
     *
     * Supports merging arrays or other collections seamlessly.
     *
     * @param CollectionInterface|array $items The items to merge.
     *
     * @return static The current collection instance for method chaining.
     */
    public function merge(CollectionInterface|array $items) : static
    {
        $mergedItems = array_merge($this->getItems(), is_array($items) ? $items : $items->all());
        $this->setItems(items: $mergedItems);

        return $this;
    }

    /**
     * Returns the count of items in the collection.
     *
     * @return int The number of items in the collection.
     */
    public function count() : int
    {
        return count($this->getItems());
    }

    /**
     * Returns an iterator for the collection.
     *
     * @return Traversable An iterator for the items.
     */
    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->getItems());
    }
}
