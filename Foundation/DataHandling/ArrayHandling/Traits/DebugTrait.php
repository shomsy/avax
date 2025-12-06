<?php

declare(strict_types=1);

namespace Avax\DataHandling\ArrayHandling\Traits;

use InvalidArgumentException;

/**
 * Trait DebugTrait
 *
 * Provides debugging capabilities for classes that implement the required methods.
 * This trait enforces the presence of `toArray` and `count` methods in the using class.
 *
 * @package Avax\DataHandling\ArrayHandling\Traits
 */
trait DebugTrait
{
    /**
     * Dumps the array representation of the class and returns the instance.
     *
     * This method outputs the array representation using `var_dump` and returns
     * the current instance to allow method chaining.
     *
     * @return static The current instance for method chaining.
     *
     * @throws InvalidArgumentException If `toArray` does not return an array.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $arrh->dump();
     * // Outputs:
     * // array(3) {
     * //   [0]=>
     * //   string(5) "apple"
     * //   [1]=>
     * //   string(6) "banana"
     * //   [2]=>
     * //   string(6) "cherry"
     * // }
     * ```
     */
    public function dump() : static
    {
        $arrayRepresentation = $this->toArray();

        if (! is_array($arrayRepresentation)) {
            throw new InvalidArgumentException(message: 'toArray method must return an array.');
        }

        var_dump($arrayRepresentation);

        return $this;
    }

    /**
     * Enforce the implementation of toArray method.
     *
     * Classes using this trait must implement this method.
     *
     * @return array The array representation of the collection.
     */
    abstract public function toArray() : array;

    /**
     * Dumps the array representation of the class and terminates execution.
     *
     * This method outputs the array representation using `var_dump` and then
     * terminates the script execution.
     *
     *
     * @throws InvalidArgumentException If `toArray` does not return an array.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $arrh->dd();
     * // Outputs:
     * // array(3) {
     * //   [0]=>
     * //   string(5) "apple"
     * //   [1]=>
     * //   string(6) "banana"
     * //   [2]=>
     * //   string(6) "cherry"
     * // }
     * // Script execution terminated.
     * ```
     */
    public function dd() : void
    {
        $arrayRepresentation = $this->toArray();

        if (! is_array($arrayRepresentation)) {
            throw new InvalidArgumentException(message: 'toArray method must return an array.');
        }

        var_dump($arrayRepresentation);
        die();
    }

    /**
     * Overrides the __debugInfo magic method to provide custom debugging information.
     *
     * This method is automatically called by `var_dump` and similar functions to retrieve
     * debugging information about the object.
     *
     * @return array The debugging information.
     */
    public function __debugInfo() : array
    {
        return $this->debugInfo();
    }

    /**
     * Provides debugging information including count and items.
     *
     * This method returns an associative array containing the count of items
     * and their array representation. It can be used to log or inspect the collection's state.
     *
     * @return array The debugging information for the current instance.
     *
     * @throws InvalidArgumentException If `toArray` does not return an array.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $debugInfo = $arrh->debugInfo();
     * // Returns:
     * // [
     * //     'count' => 3,
     * //     'items' => ['apple', 'banana', 'cherry']
     * // ]
     * ```
     */
    public function debugInfo() : array
    {
        $items = $this->toArray();

        if (! is_array($items)) {
            throw new InvalidArgumentException(message: 'toArray method must return an array.');
        }

        return [
            'count' => $this->count(),
            'items' => $items,
        ];
    }

    /**
     * Enforce the implementation of count method.
     *
     * Classes using this trait must implement this method.
     *
     * @return int The number of items in the collection.
     */
    abstract public function count() : int;
}
