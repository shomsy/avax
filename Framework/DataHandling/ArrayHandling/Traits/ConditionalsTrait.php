<?php

declare(strict_types=1);

namespace Gemini\DataHandling\ArrayHandling\Traits;

use Closure;
use InvalidArgumentException;

/**
 * Trait ConditionalsTrait
 *
 * Adds conditional behaviors to collections, enabling the application of callbacks
 * based on specific conditions. This promotes more expressive and readable code
 * when dealing with collections.
 *
 * This trait is intended to be used within classes that manage collections of data,
 * such as arrays of associative arrays or objects. It leverages the `AbstractDependenciesTrait`
 * for dependency management, ensuring that the underlying data collection is properly handled.
 *
 * @package Gemini\DataHandling\ArrayHandling\Traits
 */
trait ConditionalsTrait
{
    use AbstractDependenciesTrait;

    /**
     * Apply a callback if the given condition is true.
     *
     * This method allows you to conditionally apply transformations or operations
     * to the collection based on a boolean condition. If the condition is true,
     * the callback is executed with the current instance as its parameter.
     *
     * @param bool    $condition The condition to evaluate.
     * @param Closure $callback  The callback to execute if the condition is true. It should accept the instance and
     *                           return the modified instance.
     *
     * @return static The current instance for method chaining.
     *
     * @throws InvalidArgumentException If the callback does not return the instance.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3, 4]);
     * $result = $arrh->when(true, fn($collection) => $collection->filter(fn($item) => $item > 2));
     * // $result contains [3, 4]
     * ```
     */
    public function when(bool $condition, Closure $callback) : static
    {
        if ($condition) {
            $result = $callback($this);
            if (! $result instanceof self) {
                throw new InvalidArgumentException(message: 'Callback must return the instance.');
            }

            return $result;
        }

        return $this;
    }

    /**
     * Apply a callback unless the given condition is true.
     *
     * This method allows you to conditionally apply transformations or operations
     * to the collection based on the inverse of a boolean condition. If the condition is false,
     * the callback is executed with the current instance as its parameter.
     *
     * @param bool    $condition The condition to evaluate.
     * @param Closure $callback  The callback to execute if the condition is false. It should accept the instance and
     *                           return the modified instance.
     *
     * @return static The current instance for method chaining.
     *
     * @throws InvalidArgumentException If the callback does not return the instance.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3, 4]);
     * $result = $arrh->unless(false, fn($collection) => $collection->map(fn($item) => $item * 2));
     * // $result contains [2, 4, 6, 8]
     * ```
     */
    public function unless(bool $condition, Closure $callback) : static
    {
        if (! $condition) {
            $result = $callback($this);
            if (! $result instanceof self) {
                throw new InvalidArgumentException(message: 'Callback must return the instance.');
            }

            return $result;
        }

        return $this;
    }

    /**
     * Apply a callback unless the collection is empty.
     *
     * This method provides an inverse conditional application. It checks if the collection
     * is not empty, and if so, executes the provided callback with the current instance as its parameter.
     *
     * @param Closure $callback The callback to execute if the collection is not empty. It should accept the instance
     *                          and return the modified instance.
     *
     * @return static The current instance for method chaining.
     *
     * @throws InvalidArgumentException If the callback does not return the instance.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3]);
     * $result = $arrh->unlessEmpty(fn($collection) => $collection->remove(2));
     * // $result contains [1, 3]
     * ```
     */
    public function unlessEmpty(Closure $callback) : static
    {
        return $this->whenNotEmpty(callback: $callback);
    }

    /**
     * Apply a callback if the collection is not empty.
     *
     * This method checks if the collection has items. If it is not empty,
     * the provided callback is executed with the current instance as its parameter.
     *
     * @param Closure $callback The callback to execute if the collection is not empty. It should accept the instance
     *                          and return the modified instance.
     *
     * @return static The current instance for method chaining.
     *
     * @throws InvalidArgumentException If the callback does not return the instance.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3]);
     * $result = $arrh->whenNotEmpty(fn($collection) => $collection->map(fn($item) => $item + 1));
     * // $result contains [2, 3, 4]
     * ```
     */
    public function whenNotEmpty(Closure $callback) : static
    {
        if (! $this->isEmpty()) {
            $result = $callback($this);
            if (! $result instanceof self) {
                throw new InvalidArgumentException(message: 'Callback must return the instance.');
            }

            return $result;
        }

        return $this;
    }

    /**
     * Check if the collection is empty.
     *
     * This method determines whether the collection contains any items.
     *
     * @return bool True if the collection has no items, false otherwise.
     *
     * ```
     * $arrh = new Arrhae([]);
     * $isEmpty = $arrh->isEmpty(); // Returns true
     *
     * $arrh = new Arrhae([1, 2, 3]);
     * $isEmpty = $arrh->isEmpty(); // Returns false
     * ```
     */
    public function isEmpty() : bool
    {
        return empty($this->getItems());
    }

    /**
     * Apply a callback unless the collection is not empty.
     *
     * This method checks if the collection is empty, and if so, executes the provided callback
     * with the current instance as its parameter. It serves as a semantic alternative to `whenEmpty`.
     *
     * @param Closure $callback The callback to execute if the collection is empty. It should accept the instance and
     *                          return the modified instance.
     *
     * @return static The current instance for method chaining.
     *
     * @throws InvalidArgumentException If the callback does not return the instance.
     *
     * ```
     * $arrh = new Arrhae([]);
     * $result = $arrh->unlessNotEmpty(fn($collection) => $collection->add('default'));
     * // $result contains ['default']
     * ```
     */
    public function unlessNotEmpty(Closure $callback) : static
    {
        return $this->whenEmpty(callback: $callback);
    }

    /**
     * Apply a callback if the collection is empty.
     *
     * This method checks if the collection has no items. If it is empty,
     * the provided callback is executed with the current instance as its parameter.
     *
     * @param Closure $callback The callback to execute if the collection is empty. It should accept the instance and
     *                          return the modified instance.
     *
     * @return static The current instance for method chaining.
     *
     * @throws InvalidArgumentException If the callback does not return the instance.
     *
     * ```
     * $arrh = new Arrhae([]);
     * $result = $arrh->whenEmpty(fn($collection) => $collection->setItems(['default']));
     * // $result contains ['default']
     * ```
     */
    public function whenEmpty(Closure $callback) : static
    {
        if ($this->isEmpty()) {
            $result = $callback($this);
            if (! $result instanceof self) {
                throw new InvalidArgumentException(message: 'Callback must return the instance.');
            }

            return $result;
        }

        return $this;
    }
}
