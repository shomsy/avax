<?php

declare(strict_types=1);

namespace Avax\DataHandling\ArrayHandling\Traits;

use InvalidArgumentException;
use OutOfBoundsException;

/**
 * Trait ArrayAccessTrait
 *
 * Provides array-like access to a collection of items, enabling operations such as
 * checking existence, retrieving, setting, unsetting, and manipulating multiple items.
 *
 * This trait is intended to be used within classes that manage collections of data,
 * such as arrays of associative arrays or objects. It leverages the `AbstractDependenciesTrait`
 * for dependency management, ensuring that the underlying data collection is properly handled.
 *
 * @package Avax\DataHandling\ArrayHandling\Traits
 */
trait ArrayAccessTrait
{
    use AbstractDependenciesTrait;

    /**
     * Check if an offset exists in the collection items.
     *
     * Determines whether a specific key or index exists within the collection.
     *
     * @param mixed $offset The offset to check.
     *
     * @return bool True if the offset exists, false otherwise.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $exists = $arrh->offsetExists(1); // Returns true
     * $exists = $arrh->offsetExists(5); // Returns false
     * ```
     */
    public function offsetExists(mixed $offset) : bool
    {
        return isset($this->getItems()[$offset]);
    }

    /**
     * Get the value at a specific offset in the collection.
     *
     * Retrieves the value associated with a given key or index. Returns null if the offset does not exist.
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed|null The value at the specified offset, or null if it doesn't exist.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $fruit = $arrh->offsetGet(1); // Returns 'banana'
     * $fruit = $arrh->offsetGet(5); // Returns null
     * ```
     */
    public function offsetGet(mixed $offset) : mixed
    {
        return $this->getItems()[$offset] ?? null;
    }

    /**
     * Set a value at a specific offset in the collection.
     *
     * Assigns a value to a specified key or index. If the offset is null, the value is appended to the collection.
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     *
     *
     * @throws InvalidArgumentException If the key is invalid or cannot be set.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana']);
     * $arrh->offsetSet(1, 'blueberry'); // Collection becomes ['apple', 'blueberry']
     * $arrh->offsetSet(null, 'cherry'); // Collection becomes ['apple', 'blueberry', 'cherry']
     * ```
     */
    public function offsetSet(mixed $offset, mixed $value) : void
    {
        $items = $this->getItems();
        if (is_null(value: $offset)) {
            $items[] = $value;
        } else {
            $items[$offset] = $value;
        }

        $this->setItems(items: $items);
    }

    /**
     * Unset the value at a specific offset in the collection.
     *
     * Removes the value associated with a given key or index from the collection.
     *
     * @param mixed $offset The offset to unset.
     *
     *
     * @throws InvalidArgumentException If the offset cannot be unset.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $arrh->offsetUnset(1); // Collection becomes ['apple', 'cherry']
     * ```
     */
    public function offsetUnset(mixed $offset) : void
    {
        $items = $this->getItems();
        unset($items[$offset]);
        $this->setItems(items: $items);
    }

    /**
     * Retrieve multiple values by an array of offsets.
     *
     * Fetches values corresponding to the provided array of keys or indexes.
     *
     * @param array $keys The offsets to retrieve.
     *
     * @return array An array of values corresponding to the given offsets.
     *
     * @throws InvalidArgumentException If any of the keys are invalid.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry', 'date']);
     * $fruits = $arrh->getMultiple([0, 2]); // Returns ['apple', 'cherry']
     * ```
     */
    public function getMultiple(array $keys) : array
    {
        $items = $this->getItems();

        return array_intersect_key($items, array_flip(array: $keys));
    }

    /**
     * Set multiple values at once.
     *
     * Assigns multiple values to the collection based on an associative array of offsets and values.
     *
     * @param array $values An associative array of offsets and their corresponding values.
     *
     *
     * @throws InvalidArgumentException If any of the keys are invalid.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana']);
     * $arrh->setMultiple([1 => 'blueberry', 2 => 'cherry']); // Collection becomes ['apple', 'blueberry', 'cherry']
     * ```
     */
    public function setMultiple(array $values) : void
    {
        $items = $this->getItems();

        foreach ($values as $key => $value) {
            $items[$key] = $value;
        }

        $this->setItems(items: $items);
    }

    /**
     * Retrieve and remove an item by its offset.
     *
     * Fetches the value at the specified offset and removes it from the collection.
     *
     * @param mixed $offset The offset to retrieve and remove.
     *
     * @return mixed|null The value at the specified offset, or null if it doesn't exist.
     *
     * @throws InvalidArgumentException If the offset is invalid.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $fruit = $arrh->pull(1); // Returns 'banana' and collection becomes ['apple', 'cherry']
     * $fruit = $arrh->pull(5); // Returns null
     * ```
     */
    public function pull(mixed $offset) : mixed
    {
        $items = $this->getItems();
        $value = $items[$offset] ?? null;

        if (array_key_exists(key: $offset, array: $items)) {
            unset($items[$offset]);
            $this->setItems(items: $items);
        }

        return $value;
    }

    /**
     * Swap two items in the collection.
     *
     * Exchanges the values at the specified offsets within the collection.
     *
     * @param mixed $offset1 The first offset.
     * @param mixed $offset2 The second offset.
     *
     *
     * @throws OutOfBoundsException If either offset does not exist.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $arrh->swap(0, 2); // Collection becomes ['cherry', 'banana', 'apple']
     *
     * $arrh->swap(1, 3); // Throws OutOfBoundsException
     * ```
     */
    public function swap(mixed $offset1, mixed $offset2) : void
    {
        $items = $this->getItems();

        if (! isset($items[$offset1]) || ! isset($items[$offset2])) {
            throw new OutOfBoundsException(message: "One or both offsets do not exist.");
        }

        [$items[$offset1], $items[$offset2]] = [$items[$offset2], $items[$offset1]];

        $this->setItems(items: $items);
    }

    /**
     * Retrieve all keys of the collection.
     *
     * Provides an array of all keys or indexes present in the collection.
     *
     * @return array An array of keys.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $keys = $arrh->keys(); // Returns [0, 1, 2]
     * ```
     */
    public function keys() : array
    {
        return array_keys(array: $this->getItems());
    }

    /**
     * Retrieve all values of the collection.
     *
     * Provides an array of all values present in the collection.
     *
     * @return array An array of values.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $values = $arrh->values(); // Returns ['apple', 'banana', 'cherry']
     * ```
     */
    public function values() : array
    {
        return array_values(array: $this->getItems());
    }
}
