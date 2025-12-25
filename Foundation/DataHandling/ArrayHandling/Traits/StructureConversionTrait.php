<?php

declare(strict_types=1);

namespace Avax\DataHandling\ArrayHandling\Traits;

use InvalidArgumentException;

/**
 * Trait StructureConversionTrait
 *
 * Provides methods to convert the structure of collections,
 * including flattening multidimensional arrays into dot-notated arrays
 * and converting collections to indexed lists.
 *
 * @package Avax\DataHandling\ArrayHandling\Traits
 */
trait StructureConversionTrait
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
     * Flatten a multidimensional collection into a dot-notated array.
     *
     * Dot notation represents nested elements in a flat structure with keys such as "key.subkey".
     * This method returns a new instance with flattened keys and corresponding values.
     *
     * @return static A new instance with dot-notated keys and values.
     *
     * @throws InvalidArgumentException If the collection contains non-array nested items.
     *
     * ```
     * $arrh = new Arrhae([
     *     'user' => [
     *         'name' => 'John Doe',
     *         'address' => [
     *             'street' => '123 Main St',
     *             'city' => 'Anytown'
     *         ]
     *     ],
     *     'status' => 'active'
     * ]);
     * $flattened = $arrh->dot();
     * // $flattened contains:
     * // [
     * //     'user.name' => 'John Doe',
     * //     'user.address.street' => '123 Main St',
     * //     'user.address.city' => 'Anytown',
     * //     'status' => 'active'
     * // ]
     * ```
     */
    public function dot() : static
    {
        $results = [];
        $flatten = static function (array $items, string $prefix = '') use (&$flatten, &$results) : void {
            foreach ($items as $key => $value) {
                if (! is_scalar(value: $key) && ! is_null(value: $key)) {
                    throw new InvalidArgumentException(message: 'Keys must be scalar or null.');
                }

                $dotKey = $prefix . $key;
                if (is_array(value: $value)) {
                    $flatten($value, $dotKey . '.');
                } else {
                    $results[$dotKey] = $value;
                }
            }
        };

        $items = $this->getItems();
        if (! is_array(value: $items)) {
            throw new InvalidArgumentException(
                message: 'The collection must be an array to perform dot notation flattening.'
            );
        }

        $flatten(items: $items);

        return new static(items: $results);
    }

    /** ***Design Conversion Methods*** */

    /**
     * Enforce the implementation of the getItems method.
     *
     * Classes using this trait must implement this method.
     *
     * @return array The current collection of items.
     */
    abstract public function getItems() : array;

    /**
     * Converts the collection to a list (indexed array).
     *
     * This method returns only the values, disregarding the keys.
     * It returns a new instance containing an indexed list of the original values.
     *
     * @return static A new instance with an indexed list of array values.
     *
     * ```
     * $arrh = new Arrhae(['first' => 'apple', 'second' => 'banana', 'third' => 'cherry']);
     * $list = $arrh->toList();
     * // $list contains ['apple', 'banana', 'cherry']
     * ```
     */
    public function toList() : static
    {
        $list = array_values(array: $this->getItems());

        return new static(items: $list);
    }

    /**
     * Reconstruct a dot-notated array back into a multidimensional array.
     *
     * This method reverses the flattening process, restoring the original multidimensional structure.
     *
     * @return static A new instance with the original multidimensional array structure.
     *
     * ```
     * $flattened = new Arrhae([
     *     'user.name' => 'John Doe',
     *     'user.address.street' => '123 Main St',
     *     'user.address.city' => 'Anytown',
     *     'status' => 'active'
     * ]);
     * $original = $flattened->unDot();
     * // $original contains:
     * // [
     * //     'user' => [
     * //         'name' => 'John Doe',
     * //         'address' => [
     * //             'street' => '123 Main St',
     * //             'city' => 'Anytown'
     * //         ]
     * //     ],
     * //     'status' => 'active'
     * // ]
     * ```
     */
    public function unDot() : static
    {
        $results = [];
        foreach ($this->getItems() as $dotKey => $item) {
            $keys = explode(separator: '.', string: (string) $dotKey);
            $temp = &$results;
            foreach ($keys as $key) {
                if (! isset($temp[$key]) || ! is_array(value: $temp[$key])) {
                    $temp[$key] = [];
                }

                $temp = &$temp[$key];
            }

            $temp = $item;
            unset($temp);
        }

        return new static(items: $results);
    }
}
