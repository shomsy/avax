<?php

declare(strict_types=1);

namespace Avax\DataHandling\ArrayHandling\Traits;

use Closure;
use InvalidArgumentException;

/**
 * Trait CollectionWalkthroughTrait
 *
 * A comprehensive trait for traversing, querying, filtering, and searching collections.
 * This trait combines essential methods to enable structured and efficient collection handling.
 *
 * It is intended to be used within classes that manage collections of data,
 * such as arrays of associative arrays or objects. It leverages the `AbstractDependenciesTrait`
 * for dependency management, ensuring that the underlying data collection is properly handled.
 *
 * @package Avax\DataHandling\ArrayHandling\Traits
 */
trait CollectionWalkthroughTrait
{
    use AbstractDependenciesTrait;

    /** ***Traversal and Filtering Methods*** */

    /**
     * Applies a callback to each item in the collection, transforming the items
     * and returning a new collection with the modified values.
     *
     * This method allows for the transformation of each item in the collection by
     * applying the provided callback function. The resulting collection contains
     * the transformed items.
     *
     * @param Closure $callback The callback to apply to each item. It should accept the item as a parameter and return
     *                          the transformed value.
     *
     * @return static A new instance with the mapped items.
     *
     * @throws InvalidArgumentException If the callback does not return a valid value.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3]);
     * $squared = $arrh->map(fn($item) => $item * $item); // Returns [1, 4, 9]
     * ```
     */
    public function map(Closure $callback) : static
    {
        $mappedItems = array_map(callback: $callback, array: $this->getItems());

        return new static(items: $mappedItems);
    }

    /**
     * Applies a callback to each item in the collection without modifying it.
     *
     * This method iterates over each item in the collection and applies the provided callback.
     * It is useful for performing operations that do not require modifying the collection,
     * such as logging or side effects.
     *
     * @param Closure $callback The callback to apply. It should accept the item and its key as parameters.
     *
     *
     * @throws InvalidArgumentException If the callback is not callable.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $arrh->each(fn($item) => echo $item . "\n");
     * // Outputs:
     * // apple
     * // banana
     * // cherry
     * ```
     */
    public function each(Closure $callback) : void
    {
        foreach ($this->getItems() as $key => $item) {
            $callback($item, $key);
        }
    }

    /**
     * Gets the first item passing a given truth test.
     *
     * This method retrieves the first item in the collection that satisfies the provided callback.
     * If no callback is provided, it returns the first item in the collection.
     *
     * @param Closure|null $callback The callback for the truth test. It should accept the item as a parameter and
     *                               return a boolean.
     *
     * @return mixed The first item passing the test, or null if none found.
     *
     * @throws InvalidArgumentException If the callback is not callable.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3, 4, 5]);
     * $firstEven = $arrh->first(fn($item) => $item % 2 === 0); // Returns 2
     *
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $first = $arrh->first(); // Returns 'apple'
     * ```
     */
    public function first(Closure|null $callback = null) : mixed
    {
        foreach ($this->getItems() as $item) {
            if (! $callback instanceof Closure || $callback($item)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Zips items in the collection with additional arrays.
     *
     * This method combines the collection with one or more additional arrays. The resulting collection
     * contains arrays where each array contains elements from the corresponding positions of the input arrays.
     * If the input arrays have different lengths, the missing values are filled with `null`.
     *
     * @param array ...$items Arrays to zip with the collection.
     *
     * @return static A new collection with zipped items.
     *
     * @throws InvalidArgumentException If no additional arrays are provided.
     *
     * ```
     * $arrh1 = new Arrhae([1, 2, 3]);
     * $arrh2 = new Arrhae(['a', 'b', 'c']);
     * $zipped = $arrh1->zip($arrh2->getItems()); // Returns [[1, 'a'], [2, 'b'], [3, 'c']]
     *
     * $arrh3 = new Arrhae(['x', 'y']);
     * $zipped = $arrh1->zip($arrh2->getItems(), $arrh3->getItems());
     * // Returns [[1, 'a', 'x'], [2, 'b', 'y'], [3, 'c', null]]
     * ```
     */
    public function zip(array ...$items) : static
    {
        if ($items === []) {
            throw new InvalidArgumentException(message: 'At least one array must be provided to zip with.');
        }

        $zipped = array_map(null, $this->getItems(), ...$items);

        return new static(items: $zipped);
    }

    /**
     * Checks if the collection contains a specific value.
     *
     * This method determines whether a given value exists within the collection.
     * It uses strict comparison to ensure accurate matching.
     *
     * @param mixed $value The value to search for.
     *
     * @return bool True if found, false otherwise.
     *
     * @throws InvalidArgumentException If the value type is unsupported.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $hasBanana = $arrh->contains('banana'); // Returns true
     * $hasDate = $arrh->contains('date'); // Returns false
     * ```
     */
    public function contains(mixed $value) : bool
    {
        return in_array(needle: $value, haystack: $this->getItems(), strict: true);
    }

    /** ***Query and Search Methods*** */

    /**
     * Alias for the search method.
     *
     * This method provides an alternative name for the `search` method for better readability.
     *
     * @param mixed $value The value to search for.
     *
     * @return int|false The index or false if not found.
     *
     * @throws InvalidArgumentException If the value type is unsupported.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $index = $arrh->indexOf('cherry'); // Returns 2
     * $index = $arrh->indexOf('date'); // Returns false
     * ```
     */
    public function indexOf(mixed $value) : int|false
    {
        return $this->search(value: $value);
    }

    /**
     * Finds the index of the first occurrence of a value in the collection.
     *
     * This method searches for the specified value and returns the index of its first occurrence.
     * If the value is not found, it returns `false`.
     *
     * @param mixed $value  The value to search for.
     * @param bool  $strict Use strict comparison.
     *
     * @return int|false The index or false if not found.
     *
     * @throws InvalidArgumentException If the value type is unsupported.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry', 'banana']);
     * $index = $arrh->search('banana'); // Returns 1
     * $index = $arrh->search('date'); // Returns false
     * ```
     */
    public function search(mixed $value, bool $strict = false) : int|false
    {
        return array_search(needle: $value, haystack: $this->getItems(), strict: $strict);
    }

    /**
     * Finds the last occurrence of a value in the collection.
     *
     * This method searches for the specified value and returns the index of its last occurrence.
     * If the value is not found, it returns `false`.
     *
     * @param mixed $value The value to search for.
     *
     * @return int|false The index of the last occurrence, or false if not found.
     *
     * @throws InvalidArgumentException If the value type is unsupported.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry', 'banana']);
     * $lastIndex = $arrh->lastIndexOf('banana'); // Returns 3
     * $lastIndex = $arrh->lastIndexOf('date'); // Returns false
     * ```
     */
    public function lastIndexOf(mixed $value) : int|false
    {
        $reversedItems = array_reverse(array: $this->getItems(), preserve_keys: true);

        return array_search(needle: $value, haystack: $reversedItems, strict: true);
    }

    /**
     * Filters items where a specific key matches a given value.
     *
     * This method filters the collection to include only items where the specified key's value
     * is equal to the provided value.
     *
     * @param string $key   The key to filter by.
     * @param mixed  $value The value to match.
     *
     * @return static A new instance with the filtered items.
     *
     * @throws InvalidArgumentException If the key does not exist in any of the items.
     *
     * ```
     * $arrh = new Arrhae([
     *     ['name' => 'Alice', 'age' => 25],
     *     ['name' => 'Bob', 'age' => 30],
     *     ['name' => 'Charlie', 'age' => 25],
     * ]);
     * $filtered = $arrh->where('age', 25);
     * // Returns [
     * //     ['name' => 'Alice', 'age' => 25],
     * //     ['name' => 'Charlie', 'age' => 25],
     * // ]
     * ```
     */
    public function where(string $key, mixed $value) : static
    {
        $filtered = array_filter(
            array   : $this->getItems(),
            callback: static fn($item) : bool => ($item[$key] ?? null) === $value
        );

        return new static(items: $filtered);
    }

    /**
     * Filters items where a specific key's value is within a given range.
     *
     * This method filters the collection to include only items where the specified key's value
     * is between the provided minimum and maximum values, inclusive.
     *
     * @param string $key   The key to filter by.
     * @param array  $range An array containing exactly two elements: [min, max].
     *
     * @return static A new instance with the filtered items.
     *
     * @throws InvalidArgumentException If the range array does not contain exactly two elements.
     *
     * ```
     * $arrh = new Arrhae([
     *     ['name' => 'Alice', 'score' => 85],
     *     ['name' => 'Bob', 'score' => 90],
     *     ['name' => 'Charlie', 'score' => 75],
     * ]);
     * $filtered = $arrh->whereBetween('score', [80, 90]);
     * // Returns [
     * //     ['name' => 'Alice', 'score' => 85],
     * //     ['name' => 'Bob', 'score' => 90],
     * // ]
     * ```
     */
    public function whereBetween(string $key, array $range) : static
    {
        if (count(value: $range) !== 2) {
            throw new InvalidArgumentException(message: 'Range array must contain exactly two elements: [min, max].');
        }

        [$min, $max] = $range;

        $filtered = array_filter(
            array   : $this->getItems(),
            callback: static fn($item) : bool => ($item[$key] ?? null) >= $min &&
                ($item[$key] ?? null) <= $max
        );

        return new static(items: $filtered);
    }

    /**
     * Filters items by a specific key where values are in an array.
     *
     * This method filters the collection to include only items where the specified key's value
     * is present in the provided array of values.
     *
     * @param string $key    The key to filter by.
     * @param array  $values Array of acceptable values.
     *
     * @return static A new instance with the filtered items.
     *
     * @throws InvalidArgumentException If the values array is empty.
     *
     * ```
     * $arrh = new Arrhae([
     *     ['name' => 'Alice', 'role' => 'admin'],
     *     ['name' => 'Bob', 'role' => 'editor'],
     *     ['name' => 'Charlie', 'role' => 'subscriber'],
     * ]);
     * $filtered = $arrh->whereIn('role', ['admin', 'editor']);
     * // Returns [
     * //     ['name' => 'Alice', 'role' => 'admin'],
     * //     ['name' => 'Bob', 'role' => 'editor'],
     * // ]
     * ```
     */
    public function whereIn(string $key, array $values) : static
    {
        if ($values === []) {
            throw new InvalidArgumentException(message: 'Values array cannot be empty.');
        }

        $filtered = array_filter(
            array   : $this->getItems(),
            callback: static fn($item) : bool => in_array(needle: $item[$key] ?? null, haystack: $values, strict: true)
        );

        return new static(items: $filtered);
    }

    /**
     * Filters items where a specific key's value is not within a given range.
     *
     * This method filters the collection to include only items where the specified key's value
     * is outside the provided minimum and maximum values.
     *
     * @param string $key   The key to filter by.
     * @param array  $range An array containing exactly two elements: [min, max].
     *
     * @return static A new instance with the filtered items.
     *
     * @throws InvalidArgumentException If the range array does not contain exactly two elements.
     *
     * ```
     * $arrh = new Arrhae([
     *     ['name' => 'Alice', 'score' => 85],
     *     ['name' => 'Bob', 'score' => 90],
     *     ['name' => 'Charlie', 'score' => 75],
     * ]);
     * $filtered = $arrh->whereNotBetween('score', [80, 90]);
     * // Returns [
     * //     ['name' => 'Charlie', 'score' => 75],
     * // ]
     * ```
     */
    public function whereNotBetween(string $key, array $range) : static
    {
        if (count(value: $range) !== 2) {
            throw new InvalidArgumentException(message: 'Range array must contain exactly two elements: [min, max].');
        }

        [$min, $max] = $range;

        $filtered = array_filter(
            array   : $this->getItems(),
            callback: static fn($item) : bool => ($item[$key] ?? null) < $min ||
                ($item[$key] ?? null) > $max
        );

        return new static(items: $filtered);
    }

    /**
     * Filters items where a specific key is null.
     *
     * This method filters the collection to include only items where the specified key's value
     * is `null`.
     *
     * @param string $key The key to filter by.
     *
     * @return static A new instance with the filtered items.
     *
     * @throws InvalidArgumentException If the key does not exist in any of the items.
     *
     * ```
     * $arrh = new Arrhae([
     *     ['name' => 'Alice', 'age' => null],
     *     ['name' => 'Bob', 'age' => 30],
     *     ['name' => 'Charlie', 'age' => null],
     * ]);
     * $filtered = $arrh->whereNull('age');
     * // Returns [
     * //     ['name' => 'Alice', 'age' => null],
     * //     ['name' => 'Charlie', 'age' => null],
     * // ]
     * ```
     */
    public function whereNull(string $key) : static
    {
        $filtered = array_filter(
            array   : $this->getItems(),
            callback: static fn($item) : bool => ($item[$key] ?? null) === null
        );

        return new static(items: $filtered);
    }

    /**
     * Filters items where a specific key is not null.
     *
     * This method filters the collection to include only items where the specified key's value
     * is not `null`.
     *
     * @param string $key The key to filter by.
     *
     * @return static A new instance with the filtered items.
     *
     * @throws InvalidArgumentException If the key does not exist in any of the items.
     *
     * ```
     * $arrh = new Arrhae([
     *     ['name' => 'Alice', 'age' => null],
     *     ['name' => 'Bob', 'age' => 30],
     *     ['name' => 'Charlie', 'age' => null],
     * ]);
     * $filtered = $arrh->whereNotNull('age');
     * // Returns [
     * //     ['name' => 'Bob', 'age' => 30],
     * // ]
     * ```
     */
    public function whereNotNull(string $key) : static
    {
        $filtered = array_filter(
            array   : $this->getItems(),
            callback: static fn($item) : bool => ($item[$key] ?? null) !== null
        );

        return new static(items: $filtered);
    }

    /**
     * Filters items by a specific key where values belong to a group of acceptable values.
     *
     * This method filters the collection to include only items where the specified key's value
     * is present in the provided array of groups. It allows for grouping-based filtering of collections.
     *
     * @param string $key    The key to filter by.
     * @param array  $groups Array of acceptable values.
     *
     * @return static A new instance with the filtered items.
     *
     * @throws InvalidArgumentException If the key does not exist in one or more items.
     *
     * ```
     * $arrh = new Arrhae([
     *     ['name' => 'Alice', 'role' => 'admin'],
     *     ['name' => 'Bob', 'role' => 'editor'],
     *     ['name' => 'Charlie', 'role' => 'subscriber'],
     * ]);
     *
     * $filtered = $arrh->whereInGroup('role', ['admin', 'subscriber']);
     *
     * // $filtered contains:
     * // [
     * //     ['name' => 'Alice', 'role' => 'admin'],
     * //     ['name' => 'Charlie', 'role' => 'subscriber'],
     * // ]
     * ```
     */
    public function whereInGroup(string $key, array $groups) : static
    {
        return $this->filter(callback: static fn($item) : bool => in_array(needle: $item[$key] ?? null, haystack: $groups, strict: true));
    }

    /**
     * Filters items in the collection based on a callback.
     *
     * This method filters the collection by applying the provided callback to each item.
     * Only items for which the callback returns `true` are included in the resulting collection.
     *
     * @param Closure $callback The callback to filter items. It should accept the item and its key as parameters and
     *                          return a boolean.
     *
     * @return static A new instance containing only the filtered items.
     *
     * @throws InvalidArgumentException If the callback is not callable.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3, 4, 5]);
     * $evens = $arrh->filter(fn($item) => $item % 2 === 0); // Returns [2, 4]
     * ```
     */
    public function filter(Closure $callback) : static
    {
        $filteredItems = array_filter(
            array   : $this->getItems(),
            callback: $callback,
            mode    : ARRAY_FILTER_USE_BOTH
        );

        return new static(items: $filteredItems);
    }

    /**
     * Updates items in the collection based on a condition.
     *
     * This method applies the provided update callback to items that satisfy the specified condition.
     * It returns a new instance with the updated items, ensuring immutability.
     *
     * @param Closure $condition The condition to check for each item. It should accept an item as a parameter and
     *                           return a boolean indicating whether the item should be updated.
     * @param Closure $updater   The callback to apply to items that satisfy the condition. It should accept an item
     *                           as a parameter and return the updated item.
     *
     * @return static A new instance with the updated items.
     *
     * ```
     * $arrh = new Arrhae([
     *     ['id' => 1, 'name' => 'Alice', 'role' => 'admin'],
     *     ['id' => 2, 'name' => 'Bob', 'role' => 'editor'],
     *     ['id' => 3, 'name' => 'Charlie', 'role' => 'subscriber'],
     * ]);
     *
     * $updated = $arrh->updateWhere(
     *     fn($item) => $item['role'] === 'subscriber',
     *     fn($item) => array_merge($item, ['role' => 'member'])
     * );
     *
     * // $updated contains:
     * // [
     * //     ['id' => 1, 'name' => 'Alice', 'role' => 'admin'],
     * //     ['id' => 2, 'name' => 'Bob', 'role' => 'editor'],
     * //     ['id' => 3, 'name' => 'Charlie', 'role' => 'member'],
     * // ]
     * ```
     */
    public function updateWhere(Closure $condition, Closure $updater) : static
    {
        $updated = array_map(
            callback: static fn($item) => $condition($item) ? $updater($item) : $item,
            array   : $this->getItems()
        );

        return new static(items: $updated);
    }

    /**
     * Adds a whereIs clause to filter the array based on a condition.
     *
     * This method filters the items in the array based on the specified condition applied to a given column.
     * It supports a wide range of operators such as '=', '==', '===', '!=', '<>', '!==', '>', '<', '>=', '<=',
     * '<=>', 'contains', 'not contains', 'in', and 'not in'. The column can be specified using dot notation to access
     * nested values.
     *
     * @param string $column   The column name for the where clause.
     * @param string $operator The operator to be used in the where clause (e.g., '=', '==', '!=', '>', '<',
     *                         'contains', 'in', '<=>').
     * @param mixed  $value    The value to be compared with the column.
     */
    public function whereIs(string $column, string $operator, mixed $value) : self
    {
        // add more here if needed
        $supportedOperators = [
            '=',
            '==',
            '===',
            '!=',
            '<>',
            '!==',
            '>',
            '<',
            '>=',
            '<=',
            '<=>',
            'contains',
            'not contains',
            'in',
            'not in',
        ];

        if (! in_array(needle: $operator, haystack: $supportedOperators, strict: true)) {
            throw new InvalidArgumentException(message: 'Unsupported operator: ' . $operator);
        }

        $filteredItems = array_filter(
            array   : $this->items,
            callback: function ($item) use ($column, $operator, $value) : bool {
                $itemValue = $this->getFromItem(item: $item, key: $column);

                return match ($operator) {
                    '=', '=='      => $itemValue == $value,
                    '==='          => $itemValue === $value,
                    '!=', '<>'     => $itemValue != $value,
                    '!=='          => $itemValue !== $value,
                    '>'            => $itemValue > $value,
                    '<'            => $itemValue < $value,
                    '>='           => $itemValue >= $value,
                    '<='           => $itemValue <= $value,
                    '<=>'          => $itemValue <=> $value,
                    'contains'     => match (true) {
                        is_string(value: $itemValue) && is_string(value: $value) => str_contains(haystack: $itemValue, needle: $value),
                        is_array(value: $itemValue)                              => in_array(needle: $value, haystack: $itemValue, strict: true),
                        default                                                  => false,
                    },
                    'not contains' => match (true) {
                        is_string(value: $itemValue) && is_string(value: $value) => ! str_contains(haystack: $itemValue, needle: $value),
                        is_array(value: $itemValue)                              => ! in_array(needle: $value, haystack: $itemValue, strict: true),
                        default                                                  => true,
                    },
                    'in'           => in_array(needle: $itemValue, haystack: (array) $value, strict: true),
                    'not in'       => ! in_array(needle: $itemValue, haystack: (array) $value, strict: true),
                    default        => false,
                };
            }
        );

        $this->items = array_values(array: $filteredItems);

        return new static(items: $this->items);
    }

}
