<?php

declare(strict_types=1);

namespace Avax\DataHandling\ArrayHandling\Traits;

use Closure;
use InvalidArgumentException;

/**
 * Trait SortOperationsTrait
 *
 * Provides methods to sort and manipulate the order of items within a collection.
 * This trait allows sorting in ascending or descending order based on a key or callback,
 * reversing the order of items, and sorting by keys.
 */
trait SortOperationsTrait
{
    use AbstractDependenciesTrait;

    /**
     * Enforce the implementation of the setItems method.
     *
     * Classes using this trait must implement this method.
     *
     * @param  array  $items  The new collection of items.
     * @return static A new instance with the updated collection.
     */
    abstract public function setItems(array $items): static;

    /**
     * Sort the collection in descending order based on a given key or callback.
     *
     * This method sorts the collection in descending order either by a specified key or using a custom comparison
     * function. It returns a new instance with the sorted items, ensuring immutability.
     *
     * @param  Closure|string  $key  The key to sort by, or a callable function to compare items.
     * @return static A new sorted collection instance.
     *
     * @throws InvalidArgumentException If a string key is provided but does not exist in one or more items.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $sortedDesc = $arrh->sortDesc('name');
     * // $sortedDesc contains ['cherry', 'banana', 'apple']
     * ```
     */
    public function sortDesc(Closure|string $key): static
    {
        return $this->sortBy(key: $key)->reverse();
    }

    /** ***Sorting Methods*** */

    /**
     * Reverse the order of the items in the collection.
     *
     * This method returns a new instance with the items in reverse order, ensuring immutability.
     *
     * @return static A new instance with the items in reverse order.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $reversed = $arrh->reverse();
     * // $reversed contains ['cherry', 'banana', 'apple']
     * ```
     */
    public function reverse(): static
    {
        return new static(items: array_reverse(array: $this->getItems(), preserve_keys: true));
    }

    /**
     * Enforce the implementation of the getItems method.
     *
     * Classes using this trait must implement this method.
     *
     * @return array The current collection of items.
     */
    abstract public function getItems(): array;

    /**
     * Sort the collection based on a given key or callback.
     *
     * This method sorts the collection in ascending order either by a specified key or using a custom comparison
     * function. It returns a new instance with the sorted items, ensuring immutability.
     *
     * @param  Closure|string  $key  The key to sort by, or a callable function to compare items.
     * @return static A new sorted collection instance.
     *
     * @throws InvalidArgumentException If a string key is provided but does not exist in one or more items.
     *
     * ```
     * $arrh = new Arrhae([
     *     ['name' => 'banana', 'price' => 1.2],
     *     ['name' => 'apple', 'price' => 0.8],
     *     ['name' => 'cherry', 'price' => 2.5],
     * ]);
     * $sorted = $arrh->sortBy('name');
     * // $sorted contains:
     * // [
     * //     ['name' => 'apple', 'price' => 0.8],
     * //     ['name' => 'banana', 'price' => 1.2],
     * //     ['name' => 'cherry', 'price' => 2.5],
     * // ]
     * ```
     */
    public function sortBy(Closure|string $key): static
    {
        $sortedItems = $this->getItems();

        if (is_string(value: $key)) {
            foreach ($sortedItems as $sortedItem) {
                if (! is_array(value: $sortedItem) || ! array_key_exists(key: $key, array: $sortedItem)) {
                    throw new InvalidArgumentException(
                        message: sprintf("Each item must be an array containing the key '%s'.", $key)
                    );
                }
            }

            uasort(array: $sortedItems, callback: static fn ($a, $b): int => $a[$key] <=> $b[$key]);
        } elseif (is_callable(value: $key)) {
            uasort(array: $sortedItems, callback: $key);
        } else {
            throw new InvalidArgumentException(message: 'The key must be either a string or a callable.');
        }

        return new static(items: $sortedItems);
    }

    /**
     * Sort the collection by its keys in ascending order.
     *
     * This method sorts the collection by its keys in ascending order and returns a new instance,
     * ensuring immutability.
     *
     * @return static A new key-sorted collection instance.
     *
     * ```
     * $arrh = new Arrhae(['b' => 'banana', 'a' => 'apple', 'c' => 'cherry']);
     * $sortedKeys = $arrh->sortKeys();
     * // $sortedKeys contains ['a' => 'apple', 'b' => 'banana', 'c' => 'cherry']
     * ```
     */
    public function sortKeys(): static
    {
        $sorted = $this->getItems();
        ksort(array: $sorted);

        return new static(items: $sorted);
    }

    /**
     * Sort the collection by its keys in descending order.
     *
     * This method sorts the collection by its keys in descending order and returns a new instance,
     * ensuring immutability.
     *
     * @return static A new key-sorted collection instance.
     *
     * ```
     * $arrh = new Arrhae(['b' => 'banana', 'a' => 'apple', 'c' => 'cherry']);
     * $sortedKeysDesc = $arrh->sortKeysDesc();
     * // $sortedKeysDesc contains ['c' => 'cherry', 'b' => 'banana', 'a' => 'apple']
     * ```
     */
    public function sortKeysDesc(): static
    {
        $sorted = $this->getItems();
        krsort(array: $sorted);

        return new static(items: $sorted);
    }

    /**
     * Sorts the collection in ascending order based on a given key or callback.
     *
     * This method sorts the collection in ascending order using the specified key or a callback function for comparing
     * elements. If the key is a string, it is expected that the collection's elements are associative arrays
     * containing the specified key. If a callback function is provided, it is used to dynamically compare elements.
     *
     * @param  Closure|string  $key  The key to sort by or a callable function for comparison.
     * @return static A new sorted collection instance.
     *
     * @throws InvalidArgumentException If a string key is provided but does not exist in one or more elements,
     *                                  or if the provided parameter is neither a Closure nor a string.
     *
     * ```
     * // Sorting by the 'price' key in ascending order
     * $arrh = new Arrhae([
     *     ['name' => 'banana', 'price' => 1.2],
     *     ['name' => 'apple', 'price' => 0.8],
     *     ['name' => 'cherry', 'price' => 2.5],
     * ]);
     * $sortedAsc = $arrh->sortAsc('price');
     * print_r($sortedAsc->toArray());
     * // Outputs:
     * // [
     * //     ['name' => 'apple', 'price' => 0.8],
     * //     ['name' => 'banana', 'price' => 1.2],
     * //     ['name' => 'cherry', 'price' => 2.5],
     * // ]
     *
     * // Sorting using a callback function
     * $sortedAscCallback = $arrh->sortAsc(function($a, $b) {
     *     return strlen($a['name']) <=> strlen($b['name']);
     * });
     * print_r($sortedAscCallback->toArray());
     * // Outputs:
     * // [
     * //     ['name' => 'apple', 'price' => 0.8],
     * //     ['name' => 'banana', 'price' => 1.2],
     * //     ['name' => 'cherry', 'price' => 2.5],
     * // ]
     * ```
     */
    public function sortAsc(Closure|string $key): static
    {
        return $this->sortBy(key: $key);
    }

    /**
     * Sorts the collection by multiple criteria.
     *
     * This method allows sorting by multiple keys with specified orders (ascending or descending).
     * It accepts an associative array where keys represent the attributes to sort by,
     * and values specify the sorting order (`'asc'` for ascending, `'desc'` for descending).
     *
     * @param  array  $criteria  Associative array of sorting criteria.
     *                           Keys are the item attributes, and values are sorting orders.
     *                           Example: `['name' => 'asc', 'age' => 'desc']`.
     * @return static A new collection instance sorted by the given criteria.
     *
     * ```
     * $collection = new Arrhae([
     *     ['name' => 'Alice', 'age' => 30, 'score' => 85],
     *     ['name' => 'Bob', 'age' => 25, 'score' => 90],
     *     ['name' => 'Alice', 'age' => 25, 'score' => 80],
     *     ['name' => 'Charlie', 'age' => 35, 'score' => 70],
     * ]);
     *
     * // Sort by name (ascending), then by age (ascending), and then by score (descending).
     * $sortedCollection = $collection->sortByMultiple([
     *     'name' => 'asc',
     *     'age' => 'asc',
     *     'score' => 'desc',
     * ]);
     *
     * // Result:
     * // [
     * //     ['name' => 'Alice', 'age' => 25, 'score' => 80],
     * //     ['name' => 'Alice', 'age' => 30, 'score' => 85],
     * //     ['name' => 'Bob', 'age' => 25, 'score' => 90],
     * //     ['name' => 'Charlie', 'age' => 35, 'score' => 70],
     * // ]
     * ```
     */
    public function sortByMultiple(array $criteria): static
    {
        $items = $this->getItems();
        usort(array: $items, callback: static function (array $a, array $b) use ($criteria): int {
            foreach ($criteria as $key => $order) {
                $result = $a[$key] <=> $b[$key];
                if ($result !== 0) {
                    return $order === 'desc' ? -$result : $result;
                }
            }

            return 0;
        });

        return new static(items: $items);
    }
}
