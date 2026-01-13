<?php

declare(strict_types=1);

namespace Avax\DataHandling\ArrayHandling\Traits;

use InvalidArgumentException;
use OutOfBoundsException;

/**
 * Trait ManageItemsTrait
 *
 * Provides methods to manage items within a collection.
 * This trait offers functionalities to append, prepend, concatenate, remove,
 * replace, and slice items in a collection in an immutable and memory-efficient manner.
 *
 * The trait enforces the implementation of `getItems` and `setItems` methods
 * in the using class to manage the underlying data collection.
 */
trait ManageItemsTrait
{
    use AbstractDependenciesTrait;

    /**
     * Append a value to the end of the collection.
     *
     * This method adds a new item to the end of the collection and returns a new instance
     * with the appended item, ensuring immutability.
     *
     * @param mixed $value The value to append.
     *
     * @return static A new instance with the appended item.
     *
     * @example
     * ```
     * $arrh = new Arrhae(['apple', 'banana']);
     * $newArrh = $arrh->append('cherry');
     * // $newArrh contains ['apple', 'banana', 'cherry']
     * ```
     */
    public function append(mixed $value) : static
    {
        $items   = $this->getItems();
        $items[] = $value;

        return $this->setItems(items: $items);
    }

    /**
     * Enforce the implementation of the getItems method.
     *
     * Classes using this trait must implement this method.
     *
     * @return array The current collection of items.
     */
    abstract public function getItems() : array;

    /** ***Item Management Methods*** */

    /**
     * Enforce the implementation of the setItems method.
     *
     * Classes using this trait must implement this method.
     *
     * @param array $items The new collection of items.
     *
     * @return static A new instance with the updated collection.
     */
    abstract public function setItems(array $items) : static;

    /**
     * Prepend a value to the beginning of the collection.
     *
     * This method adds a new item to the beginning of the collection and returns a new instance
     * with the prepended item, ensuring immutability.
     *
     * @param mixed $value The value to prepend.
     *
     * @return static A new instance with the prepended item.
     *
     * @example
     * ```
     * $arrh = new Arrhae(['banana', 'cherry']);
     * $newArrh = $arrh->prepend('apple');
     * // $newArrh contains ['apple', 'banana', 'cherry']
     * ```
     */
    public function prepend(mixed $value) : static
    {
        $items = $this->getItems();
        array_unshift($items, $value);

        return $this->setItems(items: $items);
    }

    /**
     * Concatenate the given iterable items to the current collection.
     *
     * This method merges the current collection with another iterable (array or instance of the using class)
     * and returns a new instance with the concatenated items, ensuring immutability.
     *
     * @param iterable $items The items to concatenate.
     *
     * @return static A new instance with concatenated items.
     *
     * @throws InvalidArgumentException If the provided items are not iterable.
     *
     * ```
     * $arrh1 = new Arrhae(['apple', 'banana']);
     * $arrh2 = new Arrhae(['cherry', 'date']);
     * $concatenated = $arrh1->concat($arrh2);
     * // $concatenated contains ['apple', 'banana', 'cherry', 'date']
     * ```
     */
    public function concat(iterable $items) : static
    {
        $currentItems = $this->getItems();

        if ($items instanceof self) {
            $items = $items->getItems();
        } elseif (! is_array(value: $items)) {
            throw new InvalidArgumentException(
                message: 'Concat method expects an array or an instance of the using class.'
            );
        }

        $newItems = array_merge($currentItems, $items);

        return $this->setItems(items: $newItems);
    }

    /**
     * Remove and return the first item of the collection.
     *
     * This method removes the first item from the collection and returns a new instance
     * without that item. If the collection is empty, it returns null.
     *
     * @return static|null A new instance without the first item, or null if the collection is empty.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $newArrh = $arrh->shift();
     * // $newArrh contains ['banana', 'cherry']
     * ```
     */
    public function shift() : static|null
    {
        $items = $this->getItems();
        $value = array_shift(array: $items);

        if ($value === null && $items === []) {
            return null;
        }

        return $this->setItems(items: $items);
    }

    /**
     * Remove and return the last item of the collection.
     *
     * This method removes the last item from the collection and returns a new instance
     * without that item. If the collection is empty, it returns null.
     *
     * @return static|null A new instance without the last item, or null if the collection is empty.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $newArrh = $arrh->pop();
     * // $newArrh contains ['apple', 'banana']
     * ```
     */
    public function pop() : static|null
    {
        $items = $this->getItems();
        $value = array_pop(array: $items);

        if ($value === null && $items === []) {
            return null;
        }

        return $this->setItems(items: $items);
    }

    /**
     * Remove an item at a specific index.
     *
     * This method removes the item at the specified index and returns a new instance
     * without that item. It throws an exception if the index is invalid.
     *
     * @param int $index The index of the item to remove.
     *
     * @return static A new instance without the specified item.
     *
     * @throws OutOfBoundsException If the index does not exist in the collection.
     *
     * @example
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $newArrh = $arrh->removeAt(1);
     * // $newArrh contains ['apple', 'cherry']
     * ```
     */
    public function removeAt(int $index) : static
    {
        $items = $this->getItems();

        if (! array_key_exists(key: $index, array: $items)) {
            throw new OutOfBoundsException(message: 'Invalid index ' . $index . '.');
        }

        unset($items[$index]);

        // Reindex the array to maintain sequential keys
        $items = array_values(array: $items);

        return $this->setItems(items: $items);
    }

    /**
     * Replace an item at a specific index.
     *
     * This method replaces the item at the specified index with a new value and returns a new instance
     * with the updated item. It throws an exception if the index is invalid.
     *
     * @param int   $index The index to replace.
     * @param mixed $value The new value.
     *
     * @return static A new instance with the replaced item.
     *
     * @throws OutOfBoundsException If the index does not exist in the collection.
     *
     * @example
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $newArrh = $arrh->replaceAt(1, 'blueberry');
     * // $newArrh contains ['apple', 'blueberry', 'cherry']
     * ```
     */
    public function replaceAt(int $index, mixed $value) : static
    {
        $items = $this->getItems();

        if (! array_key_exists(key: $index, array: $items)) {
            throw new OutOfBoundsException(message: 'Invalid index ' . $index . '.');
        }

        $items[$index] = $value;

        return $this->setItems(items: $items);
    }

    /**
     * Returns a sliced portion of the collection.
     *
     * This method returns a new instance containing a subset of the collection based on the provided offset and
     * length.
     *
     * @param int      $offset    The starting index of the slice.
     * @param int|null $length    The number of items to include in the slice. If null, slices to the end of the
     *                            collection.
     *
     * @return static A new instance containing the sliced portion.
     *
     * @throws InvalidArgumentException If the offset or length is negative.
     *
     * @example
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry', 'date', 'elderberry']);
     * $sliced = $arrh->slice(1, 3);
     * // $sliced contains ['banana', 'cherry', 'date']
     * ```
     */
    public function slice(int $offset, int|null $length = null) : static
    {
        if ($offset < 0) {
            throw new InvalidArgumentException(message: 'Offset cannot be negative.');
        }

        if ($length !== null && $length < 0) {
            throw new InvalidArgumentException(message: 'Length cannot be negative.');
        }

        $slicedItems = array_slice(array: $this->getItems(), offset: $offset, length: $length, preserve_keys: true);

        // If slicing preserves keys and you want sequential keys, reindex
        // $slicedItems = array_values($slicedItems);

        return new static(items: $slicedItems);
    }

    /**
     * Get all items in the collection.
     *
     * This method provides a complete array of all items in the collection.
     *
     * @return array The array of all items.
     *
     * @example
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $allItems = $arrh->all();
     * // $allItems contains ['apple', 'banana', 'cherry']
     * ```
     */
    public function all() : array
    {
        return $this->getItems();
    }
}
