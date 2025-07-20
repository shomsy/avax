<?php

declare(strict_types=1);

namespace Gemini\DataHandling\ArrayHandling\Traits;

use Closure;
use InvalidArgumentException;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

/**
 * Trait TransformationTrait
 *
 * Provides methods to transform the structure of collections,
 * including flattening multidimensional arrays, applying callbacks,
 * and mapping with custom keys.
 *
 * @package Gemini\DataHandling\ArrayHandling\Traits
 */
trait TransformationTrait
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
     * Flatten a multi-dimensional array into a single-dimensional array.
     *
     * This method recursively flattens a multi-dimensional array into a single-dimensional
     * array, disregarding the original keys.
     *
     * @return static A new instance with the flattened array.
     *
     * @throws InvalidArgumentException If the collection is not an array.
     *
     * ```
     * $arrh = new Arrhae([
     *     'fruits' => ['apple', 'banana'],
     *     'vegetables' => ['carrot', 'lettuce'],
     *     'dairy' => 'milk'
     * ]);
     * $flattened = $arrh->flatten();
     * // $flattened contains:
     * // ['apple', 'banana', 'carrot', 'lettuce', 'milk']
     * ```
     */
    public function flatten() : static
    {
        $items = $this->getItems();
        if (! is_array($items)) {
            throw new InvalidArgumentException(message: 'The collection must be an array to perform flattening.');
        }

        $iterator  = new RecursiveIteratorIterator(iterator: new RecursiveArrayIterator(array: $items));
        $flattened = [];

        foreach ($iterator as $value) {
            $flattened[] = $value;
        }

        return new static(items: $flattened);
    }


    /** ***Transformation Methods*** */

    /**
     * Enforce the implementation of the getItems method.
     *
     * Classes using this trait must implement this method.
     *
     * @return array The current collection of items.
     */
    abstract public function getItems() : array;

    /**
     * Apply a callback to each item and flatten the results into a single array.
     *
     * This method applies the provided callback to each item in the collection. The callback
     * should return an array, and the results are merged into a single, flattened array.
     *
     * @param Closure $callback The callback to apply. It should return an array for each item.
     *
     * @return static A new instance with the mapped and flattened array.
     *
     * @throws InvalidArgumentException If the callback does not return an array.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $flatMapped = $arrh->flatMap(function($item) {
     *     return [$item, strtoupper($item)];
     * });
     * // $flatMapped contains ['apple', 'APPLE', 'banana', 'BANANA', 'cherry', 'CHERRY']
     * ```
     */
    public function flatMap(Closure $callback) : static
    {
        $mapped = [];
        foreach ($this->getItems() as $item) {
            $result = $callback($item);
            if (! is_array($result)) {
                throw new InvalidArgumentException(message: 'The callback for flatMap must return an array.');
            }

            $mapped = array_merge($mapped, $result);
        }

        return new static(items: $mapped);
    }

    /**
     * Apply a callback to each item, using returned keys as the new array keys.
     *
     * This method applies the provided callback to each item in the collection. The callback
     * should return an associative array with a single key-value pair, where the key becomes
     * the new key in the resulting collection.
     *
     * @param Closure $callback The callback to apply. It should return an associative array with one key-value pair.
     *
     * @return static A new instance with mapped keys and values.
     *
     * @throws InvalidArgumentException If the callback does not return an associative array with one key-value pair.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $mappedWithKeys = $arrh->mapWithKeys(function($item, $key) {
     *     return [$item => strlen($item)];
     * });
     * // $mappedWithKeys contains ['apple' => 5, 'banana' => 6, 'cherry' => 6]
     * ```
     */
    public function mapWithKeys(Closure $callback) : static
    {
        $mapped = [];
        foreach ($this->getItems() as $key => $item) {
            $result = $callback($item, $key);
            if (! is_array($result) || count($result) !== 1) {
                throw new InvalidArgumentException(
                    message: 'The callback for mapWithKeys must return an associative array with exactly one key-value pair.'
                );
            }

            $newKey   = key($result);
            $newValue = reset($result);
            if (array_key_exists($newKey, $mapped)) {
                throw new InvalidArgumentException(
                    message: sprintf("Duplicate key '%s' returned by mapWithKeys callback.", $newKey)
                );
            }

            $mapped[$newKey] = $newValue;
        }

        return new static(items: $mapped);
    }

    /**
     * Transform the current items using a callback.
     *
     * This method applies the provided callback to each item in the collection and returns
     * a new instance with the transformed items, ensuring immutability.
     *
     * @param Closure $callback The callback to apply.
     *
     * @return static A new instance with transformed items.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3]);
     * $transformed = $arrh->transform(function($item) {
     *     return $item * 2;
     * });
     * // $transformed contains [2, 4, 6]
     * ```
     */
    public function transform(Closure $callback) : static
    {
        $transformedItems = array_map($callback, $this->getItems());

        return new static(items: $transformedItems);
    }

    /**
     * Apply a complex transformation using SPL iterators.
     *
     * This method allows applying a callback to each element during iteration,
     * enabling complex transformations beyond simple mapping.
     *
     * @param Closure $callback The callback to apply to each element.
     *
     * @return static A new instance with the transformed collection.
     *
     * @throws InvalidArgumentException If the callback does not return a valid value.
     *
     * @example
     * ```
     * $arrh = new Arrhae([
     *     'user' => [
     *         'name' => 'John Doe',
     *         'age' => 30
     *     ],
     *     'status' => 'active'
     * ]);
     * $advancedTransformed = $arrh->advancedTransform(function($value, $key) {
     *     if ($key === 'age') {
     *         return $value + 1; // Increment age by 1
     *     }
     *     return $value;
     * });
     * print_r($advancedTransformed->toArray());
     * // Outputs:
     * // [
     * //     'user' => [
     * //         'name' => 'John Doe',
     * //         'age' => 31
     * //     ],
     * //     'status' => 'active'
     * // ]
     * ```
     */
    public function advancedTransform(Closure $callback) : static
    {
        $iterator = new RecursiveIteratorIterator(
            iterator: new RecursiveArrayIterator(array: $this->getItems()),
            mode    : RecursiveIteratorIterator::CHILD_FIRST
        );

        $transformed = $this->getItems();

        foreach ($iterator as $key => $value) {
            if (! is_array($value)) {
                $transformed[$key] = $callback($value, $key);
            }
        }

        return new static(items: $transformed);
    }
}
