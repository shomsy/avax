<?php

declare(strict_types=1);

namespace Avax\DataHandling\ArrayHandling\Traits;

use InvalidArgumentException;

/**
 * Trait SetOperationsTrait
 *
 * Provides set operations methods to manipulate collections, including intersection, union, difference, and merging.
 *
 * @package Avax\DataHandling\ArrayHandling\Traits
 */
trait SetOperationsTrait
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
     * Return the intersection of two collections.
     *
     * This method returns a new collection containing items that are present in both the current collection and the
     * provided collection.
     *
     * @param self $collection The collection to intersect with.
     *
     * @return static A new collection with intersected items.
     *
     * @throws InvalidArgumentException If the provided collection is empty.
     *
     * ```
     * $arrh1 = new Arrhae(['apple', 'banana', 'cherry']);
     * $arrh2 = new Arrhae(['banana', 'cherry', 'date']);
     * $intersection = $arrh1->intersect($arrh2);
     * // $intersection contains ['banana', 'cherry']
     * ```
     */
    public function intersect(self $collection) : static
    {
        $currentItems = $this->getItems();
        $otherItems   = $collection->toArray();

        if (empty($otherItems)) {
            throw new InvalidArgumentException(message: 'The provided collection for intersection is empty.');
        }

        $intersected = array_intersect($currentItems, $otherItems);

        return new static(items: $intersected);
    }

    /** ***Set Operations Methods*** */

    /**
     * Enforce the implementation of the getItems method.
     *
     * Classes using this trait must implement this method.
     *
     * @return array The current collection of items.
     */
    abstract public function getItems() : array;

    /**
     * Return the union of two collections.
     *
     * This method returns a new collection containing all unique items from both the current collection and the
     * provided collection.
     *
     * @param self $collection The collection to union with.
     *
     * @return static A new collection with unique combined items.
     *
     * ```
     * $arrh1 = new Arrhae(['apple', 'banana']);
     * $arrh2 = new Arrhae(['banana', 'cherry']);
     * $union = $arrh1->union($arrh2);
     * // $union contains ['apple', 'banana', 'cherry']
     * ```
     */
    public function union(self $collection) : static
    {
        $currentItems = $this->getItems();
        $otherItems   = $collection->toArray();

        $merged = array_merge($currentItems, $otherItems);
        $unique = array_unique(array: $merged, flags: SORT_REGULAR); // SORT_REGULAR ensures proper uniqueness for arrays

        return new static(items: $unique);
    }

    /**
     * Return the difference of two collections.
     *
     * This method returns a new collection containing items that are present in the current collection but not in the
     * provided collection.
     *
     * @param self $collection The collection to compare against.
     *
     * @return static A new collection with items in the original but not in the compared collection.
     *
     * ```
     * $arrh1 = new Arrhae(['apple', 'banana', 'cherry']);
     * $arrh2 = new Arrhae(['banana', 'date']);
     * $difference = $arrh1->diff($arrh2);
     * // $difference contains ['apple', 'cherry']
     * ```
     */
    public function diff(self $collection) : static
    {
        $currentItems = $this->getItems();
        $otherItems   = $collection->toArray();

        $diff = array_diff($currentItems, $otherItems);

        return new static(items: $diff);
    }

    /**
     * Merge two collections together.
     *
     * This method merges the current collection with the provided collection and returns a new collection containing
     * all items.
     *
     * @param self $collection The collection to merge with.
     *
     * @return static A new collection with merged items.
     *
     * ```
     * $arrh1 = new Arrhae(['apple', 'banana']);
     * $arrh2 = new Arrhae(['cherry', 'date']);
     * $merged = $arrh1->merge($arrh2);
     * // $merged contains ['apple', 'banana', 'cherry', 'date']
     * ```
     */
    public function merge(self $collection) : static
    {
        $currentItems = $this->getItems();
        $otherItems   = $collection->toArray();

        $merged = array_merge($currentItems, $otherItems);

        return new static(items: $merged);
    }

    /**
     * Returns the symmetric difference of two sets.
     *
     * This method returns a new collection containing elements that are present in either the current collection or
     * the
     * provided collection, but not in both. The symmetric difference is the combination of the differences in both
     * directions between the two sets.
     *
     * @param self $collection The collection to compare against.
     *
     * @return static A new collection with the symmetric difference of elements.
     *
     * @throws InvalidArgumentException If the provided collection is invalid or contains incompatible element types.
     *
     * ```
     * $arrh1 = new Arrhae(['apple', 'banana', 'cherry']);
     * $arrh2 = new Arrhae(['banana', 'date', 'fig']);
     * $symDifference = $arrh1->symmetricDifference($arrh2);
     * print_r($symDifference->toArray());
     * // Outputs:
     * // ['apple', 'cherry', 'date', 'fig']
     * ```
     */
    public function symmetricDifference(self $collection) : static
    {
        $diff1         = array_diff($this->getItems(), $collection->toArray());
        $diff2         = array_diff($collection->toArray(), $this->getItems());
        $symDifference = array_merge($diff1, $diff2);

        return new static(items: $symDifference);
    }
}
