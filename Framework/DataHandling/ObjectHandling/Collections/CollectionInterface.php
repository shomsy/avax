<?php

declare(strict_types=1);

namespace Gemini\DataHandling\ObjectHandling\Collections;

use ArrayAccess;
use Closure;
use Countable;
use IteratorAggregate;
use JsonSerializable;

/**
 * CollectionInterface serves as a contract for collection operations that
 * supports various interfaces such as Countable, ArrayAccess, IteratorAggregate,
 * and JsonSerializable. This ensures a standardized way to manipulate
 * collections across the application.
 */
interface CollectionInterface extends Countable, ArrayAccess, IteratorAggregate, JsonSerializable
{
    /**
     * Retrieve all items in the collection.
     *
     * @return array All items in the collection.
     */
    public function all() : array;

    /**
     * Append an item to the collection.
     *
     * @param mixed $value The value to append.
     *
     * @return static This collection instance, enabling method chaining.
     */
    public function append(mixed $value) : static;

    /**
     * Prepend an item to the collection.
     *
     * @param mixed $value The value to prepend.
     *
     * @return static This collection instance, enabling method chaining.
     */
    public function prepend(mixed $value) : static;

    /**
     * Merge another collection or an array of items into this collection.
     *
     * @param array|self $items The items to merge.
     *
     * @return static This collection instance, enabling method chaining.
     */
    public function merge(array|self $items) : static;

    /**
     * Invoke the provided callback with the collection instance.
     * This allows operations to be performed on the collection within the callback.
     *
     * @param Closure $callback The callback to invoke.
     *
     * @return static This collection instance, enabling method chaining.
     */
    public function tap(Closure $callback) : static;

    /**
     * Convert the collection to an array.
     *
     * @return array The collection items as an array.
     */
    public function toArray() : array;

    /**
     * Get the count of items in the collection.
     *
     * @return int The number of items.
     */
    public function count() : int;

    /**
     * Convert various types of inputs to an array.
     * This accommodates different forms of collection items.
     *
     * @param mixed $items The items to convert.
     *
     * @return array The converted array.
     */
    public function convertToArray(mixed $items) : array;

    /**
     * Find the first item in the collection where a given key has a specific value.
     *
     * @param string $key   The key to search for.
     * @param mixed  $value The value of the key.
     *
     * @return mixed The first item matching the criteria.
     */
    public function firstWhere(string $key, mixed $value) : mixed;

    /**
     * Find the maximum value of a given key in the collection.
     *
     * @param string|null $key The key to search by. Defaults to null for the whole item.
     *
     * @return mixed The maximum value.
     */
    public function max(string|null $key = null) : mixed;

    /**
     * Find the minimum value of a given key in the collection.
     *
     * @param string|null $key The key to search by. Defaults to null for the whole item.
     *
     * @return mixed The minimum value.
     */
    public function min(string|null $key = null) : mixed;

    /**
     * Calculate the mode (most frequent value) for a given key in the collection.
     *
     * @param string|null $key The key to search by. Defaults to null for determining mode of the whole item.
     *
     * @return string|int|null The mode value, or null if no mode is found.
     */
    public function mode(string|null $key = null) : string|int|null;

    /**
     * Count items in the collection based on the given closure.
     *
     * @param Closure $callback The closure to determine the count criteria.
     *
     * @return static The collection instance, enabling method chaining.
     */
    public function countBy(Closure $callback) : static;
}
