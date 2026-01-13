<?php

declare(strict_types=1);

namespace Avax\DataHandling\ArrayHandling\Traits;

use InvalidArgumentException;

/**
 * Trait OrderManipulationTrait
 *
 * Provides methods to manipulate the order of arrays.
 * This trait allows sorting arrays in ascending order
 * and shuffling array elements.
 */
trait OrderManipulationTrait
{
    use AbstractDependenciesTrait;

    /**
     * Sort items in ascending order based on a given key.
     *
     * This method sorts the collection in ascending order either by a specified key or using a custom comparison
     * function. It returns a new instance with the sorted items, ensuring immutability.
     *
     * @param  string|callable  $key  The key to sort by, or a callable function to compare items.
     * @return static A new instance with sorted items.
     *
     * @throws InvalidArgumentException If the key is a string and does not exist in one or more items.
     *
     * ```
     * $arrh = new Arrhae([
     *     ['name' => 'banana', 'price' => 1.2],
     *     ['name' => 'apple', 'price' => 0.8],
     *     ['name' => 'cherry', 'price' => 2.5],
     * ]);
     * $sorted = $arrh->sortAsc('name');
     * // $sorted contains:
     * // [
     * //     ['name' => 'apple', 'price' => 0.8],
     * //     ['name' => 'banana', 'price' => 1.2],
     * //     ['name' => 'cherry', 'price' => 2.5],
     * // ]
     * ```
     */
    public function sortAscending(string|callable $key): static
    {
        $items = $this->getItems();

        // If sorting by a string key, ensure all items are arrays and contain the key
        if (is_string(value: $key)) {
            foreach ($items as $item) {
                if (! is_array(value: $item) || ! array_key_exists(key: $key, array: $item)) {
                    throw new InvalidArgumentException(
                        message: sprintf("Each item must be an array containing the key '%s'.", $key)
                    );
                }
            }

            usort(array: $items, callback: static fn ($a, $b): int => $a[$key] <=> $b[$key]);
        } elseif (is_callable(value: $key)) {
            usort(array: $items, callback: $key);
        } else {
            throw new InvalidArgumentException(message: 'The key must be either a string or a callable.');
        }

        return new static(items: $items);
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
     * Shuffle the items.
     *
     * This method randomizes the order of items in the collection and returns a new instance
     * with the shuffled items, ensuring immutability.
     *
     * @return static A new instance with shuffled items.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $shuffled = $arrh->shuffle();
     * // $shuffled might contain ['cherry', 'apple', 'banana']
     * ```
     */
    public function shuffle(): static
    {
        $items = $this->getItems();

        // Shuffle items to randomize their order
        shuffle(array: $items);

        // Return a new instance to preserve immutability
        return new static(items: $items);
    }
}
