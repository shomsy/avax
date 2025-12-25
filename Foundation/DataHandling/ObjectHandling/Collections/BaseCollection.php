<?php

/** @noinspection PhpMemberCanBePulledUpInspection */

declare(strict_types=1);

namespace Avax\DataHandling\ObjectHandling\Collections;

use ArrayIterator;
use Closure;
use Traversable;

/**
 * BaseCollection provides an abstract foundation for collections,
 * handling core functionalities and item storage while leaving specific collection behaviors to subclasses.
 *
 * This class is designed to be extended, providing common methods
 * that can be used by all types of collections.
 */
abstract class BaseCollection implements CollectionInterface
{
    /**
     * @var array Holds the items of the collection.
     * Using a protected array allows subclasses to access and manipulate the stored items directly.
     */
    protected array $items = [];

    /**
     * BaseCollection constructor.
     * Initializes the collection with the provided items.
     *
     * By accepting any iterable type, this constructor ensures flexibility in initializing the collection.
     *
     * @param iterable $items Initial items to populate the collection.
     */
    public function __construct(iterable $items = [])
    {
        $this->setItems(items: $this->convertToArray(items: $items));
    }

    /**
     * Convert various types of inputs to an array.
     *
     * Ensures compatibility regardless of the input type,
     * whether it's an instance of self, Traversable, or an array.
     *
     * @param mixed $items The items to convert.
     *
     * @return array The converted array.
     */
    public function convertToArray(mixed $items) : array
    {
        if ($items instanceof self) {
            return $items->all();
        }

        if ($items instanceof Traversable) {
            return iterator_to_array(iterator: $items);
        }

        return (array) $items;
    }

    /**
     * Retrieve all items in the collection.
     *
     * Provides a consistent way to access all the items stored in the collection.
     *
     * @return array The entire collection as an array.
     */
    public function all() : array
    {
        return $this->getItems();
    }

    /**
     * Abstract method to retrieve the internal items.
     * To be implemented by subclasses.
     *
     * Forces each subclass to define how items should be retrieved,
     * allowing for flexibility in different types of collections.
     *
     * @return array The items in the collection.
     */
    abstract public function getItems() : array;

    /**
     * Abstract method to set the internal items.
     * To be implemented by subclasses.
     *
     * Ensures that subclasses handle the specific logic for setting the items,
     * which can vary based on the type of collection.
     *
     * @param array $items The items to set.
     *
     * @return static This collection instance.
     */
    abstract public function setItems(array $items) : static;

    /**
     * Get an iterator for the collection.
     *
     * Supports iteration over the collection using foreach.
     *
     * @return Traversable An iterator over the items.
     */
    public function getIterator() : Traversable
    {
        return new ArrayIterator(array: $this->getItems());
    }

    /**
     * Get the count of items in the collection.
     *
     * Provides a quick way to determine how many items are currently in the collection.
     *
     * @return int The number of items.
     */
    public function count() : int
    {
        return count(value: $this->getItems());
    }

    /**
     * Convert the collection to an array for JSON serialization.
     *
     * Ensures that when the collection is JSON-encoded, it gets correctly represented as an array.
     *
     * @return array Data ready for JSON serialization.
     */
    public function jsonSerialize() : array
    {
        return $this->toArray();
    }

    /**
     * Recursively converts nested collections to arrays.
     *
     * Handles nested collections to ensure they are also converted to arrays,
     * preserving the structure when serialized or manipulated.
     *
     * @return array The collection items as an array.
     */
    public function toArray() : array
    {
        return array_map(callback: fn($item) => $item instanceof self ? $item->toArray() : $item, array: $this->getItems());
    }

    // Abstract methods to be implemented by subclasses for specific functionalities

    /**
     * Append an item to the collection.
     *
     * @param mixed $value The value to append.
     *
     * @return static This collection instance, enabling method chaining.
     */
    abstract public function append(mixed $value) : static;

    /**
     * Prepend an item to the collection.
     *
     * @param mixed $value The value to prepend.
     *
     * @return static This collection instance, enabling method chaining.
     */
    abstract public function prepend(mixed $value) : static;

    /**
     * Merge another collection or an array of items into this collection.
     *
     * @param array|CollectionInterface $items The items to merge.
     *
     * @return static This collection instance, enabling method chaining.
     */
    abstract public function merge(array|CollectionInterface $items) : static;

    /**
     * Invoke the provided callback with the collection instance.
     * This allows operations to be performed on the collection within the callback.
     *
     * @param Closure $callback The callback to invoke.
     *
     * @return static This collection instance, enabling method chaining.
     */
    abstract public function tap(Closure $callback) : static;
}
