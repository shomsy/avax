<?php

declare(strict_types=1);

namespace Avax\DataHandling\ArrayHandling\Traits;

use Closure;
use InvalidArgumentException;

/**
 * Trait PartitioningTrait
 *
 * Provides methods to partition and group items within a collection.
 * This trait allows splitting collections based on conditions, grouping by keys or callbacks,
 * and dividing collections into specified numbers of groups or chunks.
 *
 * @package Avax\DataHandling\ArrayHandling\Traits
 */
trait PartitioningTrait
{
    use AbstractDependenciesTrait;

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
     * Splits the collection into two groups based on a callback.
     *
     * This method partitions the collection into two separate collections:
     * one where items satisfy the provided callback condition,
     * and another where items do not.
     *
     * @param Closure $callback The callback to determine the split condition.
     *
     * @return array Two collections: one matching the condition, one not.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry', 'date']);
     * [$fruitsWithA, $fruitsWithoutA] = $arrh->partition(fn($item) => strpos($item, 'a') !== false);
     * // $fruitsWithA contains ['apple', 'banana', 'date']
     * // $fruitsWithoutA contains ['cherry']
     * ```
     */
    public function partition(Closure $callback) : array
    {
        $matches    = [];
        $nonMatches = [];

        foreach ($this->getItems() as $item) {
            if ($callback($item)) {
                $matches[] = $item;
            } else {
                $nonMatches[] = $item;
            }
        }

        return [new static(items: $matches), new static(items: $nonMatches)];
    }

    /** ***Partitioning Methods*** */

    /**
     * Enforce the implementation of the getItems method.
     *
     * Classes using this trait must implement this method.
     *
     * @return array The current collection of items.
     */
    abstract public function getItems() : array;

    /**
     * Groups the collection items by a specific key or callback.
     *
     * This method organizes the collection into groups based on a specified key or a callback function.
     * Each group is represented as a sub-collection within the main collection.
     *
     * @param Closure|string $key The key to group by, or a callback function to determine the group key.
     *
     * @return static A collection containing grouped items.
     *
     * @throws InvalidArgumentException If a string key is provided but does not exist in one or more items.
     *
     * ```
     * // Grouping by a string key
     * $arrh = new Arrhae([
     *     ['type' => 'fruit', 'name' => 'apple'],
     *     ['type' => 'fruit', 'name' => 'banana'],
     *     ['type' => 'vegetable', 'name' => 'carrot'],
     * ]);
     * $grouped = $arrh->groupBy('type');
     * // $grouped contains:
     * // [
     * //     'fruit' => new Arrhae([
     * //         ['type' => 'fruit', 'name' => 'apple'],
     * //         ['type' => 'fruit', 'name' => 'banana'],
     * //     ]),
     * //     'vegetable' => new Arrhae([
     * //         ['type' => 'vegetable', 'name' => 'carrot'],
     * //     ]),
     * // ]
     *
     * // Grouping by a callback
     * $groupedByLength = $arrh->groupBy(fn($item) => strlen($item['name']));
     * // $groupedByLength contains:
     * // [
     * //     5 => new Arrhae([['type' => 'fruit', 'name' => 'apple']]),
     * //     6 => new Arrhae([['type' => 'fruit', 'name' => 'banana'], ['type' => 'vegetable', 'name' => 'carrot']]),
     * // ]
     * ```
     */
    public function groupBy(Closure|string $key) : static
    {
        $grouped = [];

        foreach ($this->getItems() as $item) {
            if (is_callable($key)) {
                $groupKey = $key($item);
            } elseif (is_string($key)) {
                if (! is_array($item) || ! array_key_exists($key, $item)) {
                    throw new InvalidArgumentException(
                        message: sprintf("Each item must be an array containing the key '%s'.", $key)
                    );
                }

                $groupKey = $item[$key];
            } else {
                throw new InvalidArgumentException(message: 'The key must be either a string or a callable.');
            }

            $grouped[$groupKey][] = $item;
        }

        return new static(items: array_map(fn($group) : static => new static(items: $group), $grouped));
    }

    /**
     * Splits the collection into a specified number of groups.
     *
     * This method divides the collection into the desired number of groups as evenly as possible.
     * Each group is represented as a sub-collection within the main collection.
     *
     * @param int $numberOfGroups The number of groups to split into.
     *
     * @return static A collection containing the specified number of groups.
     *
     * @throws InvalidArgumentException If the number of groups is less than 1.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry', 'date', 'elderberry']);
     * $groups = $arrh->split(2);
     * // $groups contains:
     * // [
     * //     new Arrhae(['apple', 'banana', 'cherry']),
     * //     new Arrhae(['date', 'elderberry']),
     * // ]
     * ```
     */
    public function split(int $numberOfGroups) : static
    {
        if ($numberOfGroups < 1) {
            throw new InvalidArgumentException(message: 'Number of groups must be at least 1.');
        }

        $totalItems = count($this->getItems());
        $groupSize  = (int) ceil($totalItems / $numberOfGroups);
        $groups     = array_chunk($this->getItems(), $groupSize);

        return new static(items: array_map(static fn($group) : static => new static(items: $group), $groups));
    }

    /**
     * Splits the collection into chunks of a given size.
     *
     * This method divides the collection into chunks, each containing a specified number of items.
     * Each chunk is represented as a sub-collection within the main collection.
     *
     * @param int $size The size of each chunk.
     *
     * @return static A collection containing the chunks.
     *
     * @throws InvalidArgumentException If the chunk size is less than 1.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry', 'date', 'elderberry']);
     * $chunks = $arrh->chunk(2);
     * // $chunks contains:
     * // [
     * //     new Arrhae(['apple', 'banana']),
     * //     new Arrhae(['cherry', 'date']),
     * //     new Arrhae(['elderberry']),
     * // ]
     * ```
     */
    public function chunk(int $size) : static
    {
        if ($size < 1) {
            throw new InvalidArgumentException(message: 'Chunk size must be at least 1.');
        }

        $chunks = array_chunk($this->getItems(), $size);

        return new static(items: array_map(static fn($chunk) : static => new static(items: $chunk), $chunks));
    }
}
