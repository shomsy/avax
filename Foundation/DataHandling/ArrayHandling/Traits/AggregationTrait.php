<?php

declare(strict_types=1);

namespace Avax\DataHandling\ArrayHandling\Traits;

use InvalidArgumentException;
use LogicException;

/**
 * Trait AggregationTrait
 *
 * Provides robust aggregation methods for handling data collections.
 * Supports summation, averaging, min/max determination, and counting by keys or callbacks.
 *
 * This trait is intended to be used within classes that manage collections of data,
 * such as arrays of associative arrays or objects. It offers flexible methods
 * that can operate on specific keys or use callbacks for dynamic value extraction.
 */
trait AggregationTrait
{
    /**
     * Calculate the average of values for a specified key or callback.
     *
     * This method computes the average (arithmetic mean) of all numeric values obtained
     * from the specified key or by applying a callback to each item in the collection.
     *
     * @param  string|callable  $key  The key to extract values from each item or a callable that returns the value.
     * @return float The resulting average or 0.0 if the collection is empty.
     *
     * @throws InvalidArgumentException If non-numeric values are encountered during sum calculation.
     * @throws LogicException If the data structure is invalid.
     *
     * ```
     * $arrh = new Arrhae([
     *     ['id' => 1, 'score' => 80],
     *     ['id' => 2, 'score' => 90],
     *     ['id' => 3, 'score' => 70],
     * ]);
     * $averageScore = $arrh->average('score'); // Returns 80.0
     *
     * // Using a callback to calculate average age
     * $arrh = new Arrhae([
     *     ['name' => 'Alice', 'age' => 25],
     *     ['name' => 'Bob', 'age' => 30],
     *     ['name' => 'Charlie', 'age' => 35],
     * ]);
     * $averageAge = $arrh->average(fn($item) => $item['age']); // Returns 30.0
     * ```
     */
    public function average(string|callable $key): float
    {
        $count = count(value: $this->getItems());

        return $count !== 0 ? $this->sum(key: $key) / $count : 0.0;
    }

    /**
     * Sum the values of a specified key or computed by a callback.
     *
     * This method calculates the total sum of all numeric values obtained from the specified key
     * or by applying a callback to each item in the collection.
     *
     * @param  string|callable  $key  The key to extract values from each item or a callable that returns the value.
     * @return float|int The resulting sum of the values.
     *
     * @throws InvalidArgumentException If non-numeric values are encountered during sum calculation.
     * @throws LogicException If the data structure is invalid.
     *
     * ```
     * $arrh = new Arrhae([
     *     ['id' => 1, 'amount' => 100.50],
     *     ['id' => 2, 'amount' => 200.75],
     *     ['id' => 3, 'amount' => 150.25],
     * ]);
     * $totalAmount = $arrh->sum('amount'); // Returns 451.5
     *
     * // Using a callback to sum ages
     * $arrh = new Arrhae([
     *     ['name' => 'Alice', 'age' => 25],
     *     ['name' => 'Bob', 'age' => 30],
     *     ['name' => 'Charlie', 'age' => 35],
     * ]);
     * $totalAge = $arrh->sum(fn($item) => $item['age']); // Returns 90
     * ```
     */
    public function sum(string|callable $key): float|int
    {
        $this->validateData();

        return array_reduce(
            array   : $this->getItems(),
            callback: static function ($carry, $item) use ($key): int|float {
                $value = is_callable(value: $key) ? $key($item) : ($item[$key] ?? 0);

                if (! is_numeric(value: $value)) {
                    throw new InvalidArgumentException(message: 'Non-numeric value encountered in sum calculation.');
                }

                return $carry + $value;
            },
            initial : 0
        );
    }

    /**
     * Validate the data structure before applying aggregation.
     *
     * Ensures that the collection is a valid array. This prevents unexpected errors
     * during aggregation operations.
     *
     * @throws LogicException If `getItems` does not return a valid array.
     */
    private function validateData(): void
    {
        $items = $this->getItems();

        if (! is_array(value: $items)) {
            throw new LogicException(message: 'Expected data to be an array.');
        }
    }

    /**
     * Find the minimum value for a specified key or callback.
     *
     * This method identifies the smallest numeric value obtained from the specified key
     * or by applying a callback to each item in the collection.
     *
     * @param  string|callable  $key  The key to extract values from each item or a callable that returns the value.
     * @return mixed The minimum value.
     *
     * @throws LogicException If the collection is empty or contains non-numeric values.
     *
     * ```
     * $arrh = new Arrhae([
     *     ['id' => 1, 'score' => 80],
     *     ['id' => 2, 'score' => 90],
     *     ['id' => 3, 'score' => 70],
     * ]);
     * $minScore = $arrh->min('score'); // Returns 70
     *
     * // Using a callback to find minimum age
     * $arrh = new Arrhae([
     *     ['name' => 'Alice', 'age' => 25],
     *     ['name' => 'Bob', 'age' => 30],
     *     ['name' => 'Charlie', 'age' => 35],
     * ]);
     * $minAge = $arrh->min(fn($item) => $item['age']); // Returns 25
     * ```
     */
    public function min(string|callable $key): mixed
    {
        $values = $this->mapValues(key: $key);

        if (empty($values)) {
            throw new LogicException(message: 'Cannot determine minimum value of an empty collection.');
        }

        return min(value: $values);
    }

    /**
     * Map items to values based on a key or callback.
     *
     * Extracts values from each item in the collection based on the specified key or by applying a callback.
     *
     * @param  string|callable  $key  The key to extract values from each item or a callable that returns the value.
     * @return array The extracted values.
     *
     * @throws LogicException If the data structure is invalid.
     *
     * ```
     * // Mapping scores
     * $arrh = new Arrhae([
     *     ['id' => 1, 'score' => 80],
     *     ['id' => 2, 'score' => 90],
     *     ['id' => 3, 'score' => 70],
     * ]);
     * $scores = $arrh->mapValues('score'); // Returns [80, 90, 70]
     *
     * // Using a callback to extract names
     * $arrh = new Arrhae([
     *     ['name' => 'Alice', 'age' => 25],
     *     ['name' => 'Bob', 'age' => 30],
     *     ['name' => 'Charlie', 'age' => 35],
     * ]);
     * $names = $arrh->mapValues(fn($item) => $item['name']); // Returns ['Alice', 'Bob', 'Charlie']
     * ```
     */
    private function mapValues(string|callable $key): array
    {
        $this->validateData();

        return array_map(
            callback: static fn ($item) => is_callable(value: $key) ? $key($item) : ($item[$key] ?? null),
            array   : $this->getItems()
        );
    }

    /**
     * Find the maximum value for a specified key or callback.
     *
     * This method identifies the largest numeric value obtained from the specified key
     * or by applying a callback to each item in the collection.
     *
     * @param  string|callable  $key  The key to extract values from each item or a callable that returns the value.
     * @return mixed The maximum value.
     *
     * @throws LogicException If the collection is empty or contains non-numeric values.
     *
     * ```
     * $arrh = new Arrhae([
     *     ['id' => 1, 'score' => 80],
     *     ['id' => 2, 'score' => 90],
     *     ['id' => 3, 'score' => 70],
     * ]);
     * $maxScore = $arrh->max('score'); // Returns 90
     *
     * // Using a callback to find maximum age
     * $arrh = new Arrhae([
     *     ['name' => 'Alice', 'age' => 25],
     *     ['name' => 'Bob', 'age' => 30],
     *     ['name' => 'Charlie', 'age' => 35],
     * ]);
     * $maxAge = $arrh->max(fn($item) => $item['age']); // Returns 35
     * ```
     */
    public function max(string|callable $key): mixed
    {
        $values = $this->mapValues(key: $key);

        if (empty($values)) {
            throw new LogicException(message: 'Cannot determine maximum value of an empty collection.');
        }

        return max(value: $values);
    }

    /**
     * Count occurrences of unique values by a specified key or callback.
     *
     * This method tallies the number of times each unique value appears in the collection,
     * based on the specified key or by applying a callback to each item.
     *
     * @param  string|callable  $key  The key to extract values from each item or a callable that returns the value.
     * @return array Associative array with counts for each unique value.
     *
     * @throws LogicException If the data structure is invalid.
     *
     * ```
     * $arrh = new Arrhae([
     *     ['id' => 1, 'category' => 'A'],
     *     ['id' => 2, 'category' => 'B'],
     *     ['id' => 3, 'category' => 'A'],
     *     ['id' => 4, 'category' => 'C'],
     *     ['id' => 5, 'category' => 'B'],
     * ]);
     * $categoryCounts = $arrh->countBy('category');
     * // Returns ['A' => 2, 'B' => 2, 'C' => 1]
     *
     * // Using a callback to count based on a derived value
     * $arrh = new Arrhae([
     *     ['name' => 'Alice', 'age' => 25],
     *     ['name' => 'Bob', 'age' => 30],
     *     ['name' => 'Charlie', 'age' => 35],
     *     ['name' => 'David', 'age' => 30],
     * ]);
     * $ageCounts = $arrh->countBy(fn($item) => $item['age']);
     * // Returns [25 => 1, 30 => 2, 35 => 1]
     * ```
     */
    public function countBy(string|callable $key): array
    {
        $this->validateData();

        $result = [];
        foreach ($this->getItems() as $item) {
            $value = is_callable(value: $key) ? $key($item) : ($item[$key] ?? null);
            $result[$value] = ($result[$value] ?? 0) + 1;
        }

        return $result;
    }

    /**
     * Reduce the collection to a single value using a callback.
     *
     * This method applies a callback function cumulatively to the items of the collection,
     * from left to right, to reduce the collection to a single value.
     *
     * @param  callable  $callback  Callback to apply to each item. It should accept two parameters:
     *                              the carry (accumulator) and the current item.
     * @param  mixed|null  $initial  Initial value to start the reduction. If not provided, the first item of the
     *                               collection is used.
     * @return mixed The reduced value.
     *
     * @throws LogicException If the data structure is invalid.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3, 4]);
     * $product = $arrh->reduce(fn($carry, $item) => $carry * $item, 1); // Returns 24
     *
     * // Using reduce to concatenate names
     * $arrh = new Arrhae([
     *     ['name' => 'Alice'],
     *     ['name' => 'Bob'],
     *     ['name' => 'Charlie'],
     * ]);
     * $names = $arrh->reduce(fn($carry, $item) => $carry . ', ' . $item['name'], '');
     * // Returns ', Alice, Bob, Charlie'
     * ```
     */
    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        $this->validateData();

        return array_reduce(array: $this->getItems(), callback: $callback, initial: $initial);
    }

    /**
     * Group items by a specified key or callback.
     *
     * This method organizes the collection into groups based on the specified key or by applying a callback to each
     * item.
     *
     * @param  string|callable  $key  The key to extract values from each item or a callable that returns the grouping
     *                                value.
     * @return array The grouped items.
     *
     * @throws LogicException If the data structure is invalid.
     *
     * ```
     * // Grouping by category
     * $arrh = new Arrhae([
     *     ['id' => 1, 'category' => 'A'],
     *     ['id' => 2, 'category' => 'B'],
     *     ['id' => 3, 'category' => 'A'],
     *     ['id' => 4, 'category' => 'C'],
     *     ['id' => 5, 'category' => 'B'],
     * ]);
     * $grouped = $arrh->aggregateGroupBy('category');
     *
     * Returns:
     * [
     *     'A' => [
     *         ['id' => 1, 'category' => 'A'],
     *         ['id' => 3, 'category' => 'A'],
     *     ],
     *     'B' => [
     *         ['id' => 2, 'category' => 'B'],
     *         ['id' => 5, 'category' => 'B'],
     *     ],
     *     'C' => [
     *         ['id' => 4, 'category' => 'C'],
     *     ],
     * ]
     *
     *
     * // Grouping by age range using a callback
     * $arrh = new Arrhae([
     *     ['name' => 'Alice', 'age' => 25],
     *     ['name' => 'Bob', 'age' => 30],
     *     ['name' => 'Charlie', 'age' => 35],
     *     ['name' => 'David', 'age' => 40],
     * ]);
     * $groupedByAgeRange = $arrh->aggregateGroupBy(function($item) {
     *     if ($item['age'] < 30) {
     *         return 'Under 30';
     *     } elseif ($item['age'] < 40) {
     *         return '30-39';
     *     } else {
     *         return '40 and above';
     *     }
     * });
     *
     * Returns:
     * [
     *     'Under 30' => [
     *         ['name' => 'Alice', 'age' => 25],
     *     ],
     *     '30-39' => [
     *         ['name' => 'Bob', 'age' => 30],
     *         ['name' => 'Charlie', 'age' => 35],
     *     ],
     *     '40 and above' => [
     *         ['name' => 'David', 'age' => 40],
     *     ],
     * ]
     */
    public function aggregateGroupBy(string|callable $key): array
    {
        $this->validateData();

        $grouped = [];
        foreach ($this->getItems() as $item) {
            $groupKey = is_callable(value: $key) ? $key($item) : ($item[$key] ?? null);
            $grouped[$groupKey][] = $item;
        }

        return $grouped;
    }
}
