<?php

declare(strict_types=1);

namespace Avax\DataHandling\ArrayHandling\Traits;

use InvalidArgumentException;

/**
 * Trait StringManipulationTrait
 *
 * Provides advanced string manipulation capabilities to the Arrhae class, including
 * concatenation, case transformation, trimming, replacing, and more.
 */
trait StringManipulationTrait
{
    use AbstractDependenciesTrait;

    /**
     * Defines the default characters to be trimmed.
     */
    private const string DEFAULT_TRIM_CHARACTERS = " \t\n\r\0\x0B";

    /**
     * Concatenates the items of the collection into a string using a glue string.
     *
     * @param string $glue The string to use between items. Defaults to an empty string.
     *
     * @return string The concatenated string.
     *
     * @throws InvalidArgumentException If the collection contains non-string items.
     *
     * @example
     * $arrh = Arrhae::make(['apple', 'banana', 'cherry']);
     * echo $arrh->implode(', '); // Output: 'apple, banana, cherry'
     */
    public function implode(string $glue = '') : string
    {
        // Ensure all items are strings
        foreach ($this->getItems() as $item) {
            if (! is_string(value: $item)) {
                throw new InvalidArgumentException(message: 'All items must be strings to perform implode.');
            }
        }

        return implode(separator: $glue, array: $this->getItems());
    }

    /**
     * Converts all string items in the collection to uppercase.
     *
     * @param string|null $key The key to target within associative arrays. If null, apply to all string items.
     *
     * @return static A new Arrhae instance with items converted to uppercase.
     *
     * @example
     * $arrh = Arrhae::make(['apple', 'banana']);
     * $uppercased = $arrh->uppercase();
     * // ['APPLE', 'BANANA']
     */
    public function uppercase(string|null $key = null) : static
    {
        return $this->processItems(callback: fn(string $value) : string => strtoupper(string: $value), key: $key);
    }

    /**
     * A helper method to process items within the collection and apply string transformations.
     *
     * @param callable    $callback The transformation to apply to string items.
     * @param string|null $key      The key to target within associative arrays if applicable.
     *
     * @return static A new Arrhae instance with processed items.
     */
    private function processItems(callable $callback, string|null $key = null) : static
    {
        return $this->map(callback: function ($item) use ($callback, $key) {
            if ($key !== null && is_array(value: $item) && isset($item[$key]) && is_string(value: $item[$key])) {
                $item[$key] = $callback($item[$key]);

                return $item;
            }

            if (is_string(value: $item)) {
                return $callback($item);
            }

            return $item;
        });
    }

    /**
     * Converts all string items in the collection to lowercase.
     *
     * @param string|null $key The key to target within associative arrays. If null, apply to all string items.
     *
     * @return static A new Arrhae instance with items converted to lowercase.
     *
     * @example
     * $arrh = Arrhae::make(['APPLE', 'BANANA']);
     * $lowercased = $arrh->lowercase();
     * // ['apple', 'banana']
     */
    public function lowercase(string|null $key = null) : static
    {
        return $this->processItems(callback: fn(string $value) : string => strtolower(string: $value), key: $key);
    }

    /**
     * Converts the first character of each word in the string items to uppercase.
     *
     * @param string|null $key The key to target within associative arrays. If null, apply to all string items.
     *
     * @return static A new Arrhae instance with items converted to the title case.
     *
     * @example
     * $arrh = Arrhae::make(['hello world', 'php is great']);
     * $titlecased = $arrh->title();
     * // ['Hello World', 'Php Is Great']
     */
    public function title(string|null $key = null) : static
    {
        return $this->processItems(callback: fn(string $value) : string => ucwords(string: strtolower(string: $value)), key: $key);
    }

    /**
     * Removes whitespace or other predefined characters from the beginning and end of string items.
     *
     * @param string      $characters The characters to trim. Defaults to trimming common whitespace characters.
     * @param string|null $key        The key to target within associative arrays. If null, apply to all string items.
     *
     * @return static A new Arrhae instance with items trimmed.
     *
     * @example
     * $arrh = Arrhae::make([' apple  ', "\tbanana\n", ' cherry ']);
     * $trimmed = $arrh->trim();
     * // ['apple', 'banana', 'cherry']
     */
    public function trim(string $characters = self::DEFAULT_TRIM_CHARACTERS, string|null $key = null) : static
    {
        return $this->processItems(callback: fn(string $value) : string => trim(string: $value, characters: $characters), key: $key);
    }

    /**
     * Converts string items in the collection to camelCase.
     *
     * @param string|null $key The key to target within associative arrays. If null, apply to all string items.
     *
     * @return static A new Arrhae instance with items converted to camelCase.
     *
     * @example
     * $arrh = Arrhae::make(['hello_world', 'php-is-great', 'convert this']);
     * $camelCased = $arrh->camelCase();
     * // ['helloWorld', 'phpIsGreat', 'convertThis']
     */
    public function camelCase(string|null $key = null) : static
    {
        return $this->processItems(
            callback: fn(string $value) : string => lcfirst(
                string: str_replace(search: ' ', replace: '', subject: ucwords(string: str_replace(search: ['-', '_'], replace: ' ', subject: $value)))
            ),
            key     : $key
        );
    }
}