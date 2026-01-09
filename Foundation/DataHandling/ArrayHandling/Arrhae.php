<?php

declare(strict_types=1);

namespace Avax\DataHandling\ArrayHandling;

use ArrayAccess;
use ArrayIterator;
use Avax\DataHandling\ArrayHandling\Traits\AbstractDependenciesTrait;
use Avax\DataHandling\ArrayHandling\Traits\AdvancedStringSearchTrait;
use Avax\DataHandling\ArrayHandling\Traits\AggregationTrait;
use Avax\DataHandling\ArrayHandling\Traits\ArrayAccessTrait;
use Avax\DataHandling\ArrayHandling\Traits\ArrayConversionTrait;
use Avax\DataHandling\ArrayHandling\Traits\CollectionWalkthroughTrait;
use Avax\DataHandling\ArrayHandling\Traits\ConditionalsTrait;
use Avax\DataHandling\ArrayHandling\Traits\DebugTrait;
use Avax\DataHandling\ArrayHandling\Traits\LazyEvaluationTrait;
use Avax\DataHandling\ArrayHandling\Traits\LockableTrait;
use Avax\DataHandling\ArrayHandling\Traits\MacrosTrait;
use Avax\DataHandling\ArrayHandling\Traits\ManageItemsTrait;
use Avax\DataHandling\ArrayHandling\Traits\MetaInfoTrait;
use Avax\DataHandling\ArrayHandling\Traits\OrderManipulationTrait;
use Avax\DataHandling\ArrayHandling\Traits\PartitioningTrait;
use Avax\DataHandling\ArrayHandling\Traits\SetOperationsTrait;
use Avax\DataHandling\ArrayHandling\Traits\SortOperationsTrait;
use Avax\DataHandling\ArrayHandling\Traits\StringManipulationTrait;
use Avax\DataHandling\ArrayHandling\Traits\StructureConversionTrait;
use Avax\DataHandling\ArrayHandling\Traits\TransformationTrait;
use Closure;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;


/**
 * Class Arrhae
 *
 * The `Arrhae` class is a comprehensive utility for working with arrays. It offers a variety of methods
 * for transforming, querying, and manipulating complex arrays. It supports features like dot notation for nested
 * arrays, lazy evaluation, optimized array access, conditional operations, and more.
 *
 * This class implements the `ArrayAccess`, `IteratorAggregate`, and `Countable` interfaces, allowing it to work
 * seamlessly with array operations, iteration, and counting.
 *
 * Additionally, it leverages several traits to modularize functionality:
 * - `AbstractDependenciesTrait`: Manages dependencies required by the class.
 * - `AggregationTrait`: Provides methods for aggregating data (e.g., sum, average).
 * - `ArrayAccessTrait`: Implements array access methods.
 * - `ArrayConversionTrait`: Offers methods to convert arrays to different formats.
 * - `CollectionWalkthroughTrait`: Facilitates iteration and traversal of collections.
 * - `ConditionalsTrait`: Provides conditional operations on arrays.
 * - `DebugTrait`: Contains debugging utilities.
 * - `LazyEvaluationTrait`: Enables lazy evaluation of array operations.
 * - `MacrosTrait`: Allows adding custom macros to the class.
 * - `ManageItemsTrait`: Manages items within the array.
 * - `MetaInfoTrait`: Provides metadata information about the array.
 * - `OrderManipulationTrait`: Handles ordering and sorting of array items.
 * - `PartitioningTrait`: Facilitates partitioning of the array into subsets.
 * - `SetOperationsTrait`: Implements set operations like union and intersection.
 * - `SortOperationsTrait`: Offers advanced sorting capabilities.
 * - `StructureConversionTrait`: Converts the array structure as needed.
 * - `TransformationTrait`: Provides methods to transform array data.
 *
 * @method float|int sum(string|callable $key) Sums all numeric values in the array.
 * @method float|null average() Calculates the average of all numeric values in the array.
 * @method Arrhae fromJson(string $json) Creates an Arrhae instance from a JSON string.
 * @method string toJson() Converts the array to a JSON string.
 *
 * @package Avax\DataHandling\ArrayHandling
 */
class Arrhae implements ArrayAccess, IteratorAggregate, Countable
{
    use AbstractDependenciesTrait;
    use AggregationTrait;
    use ArrayAccessTrait;
    use ArrayConversionTrait;
    use CollectionWalkthroughTrait;
    use ConditionalsTrait;
    use DebugTrait;
    use LazyEvaluationTrait;
    use MacrosTrait;
    use ManageItemsTrait;
    use MetaInfoTrait;
    use OrderManipulationTrait;
    use PartitioningTrait;
    use SetOperationsTrait;
    use SortOperationsTrait;
    use StructureConversionTrait;
    use TransformationTrait;
    use AdvancedStringSearchTrait;
    use StringManipulationTrait;
    use LockableTrait;

    /**
     * @var array The underlying items of the collection.
     */
    protected array $items = [];

    /**
     * Arrhae constructor.
     *
     * Initializes the `Arrhae` instance with an optional array of items. This constructor is called when a new
     * instance of `Arrhae` is created, and it optionally accepts an array of items that will be stored internally.
     *
     * @param iterable $items Initial items for the collection. Can be an array or any Traversable object.
     *
     * @throws InvalidArgumentException If the provided items cannot be converted to an array.
     */
    public function __construct(iterable $items = [])
    {
        $this->setItems(items: $this->convertToArray(items: $items));
    }

    /**
     * Converts an iterable to an array.
     *
     * This helper method ensures that the provided items are converted to an array,
     * regardless of whether they are initially an array or a Traversable object.
     *
     * @param iterable $items The items to convert.
     *
     * @return array The converted array of items.
     *
     * @throws InvalidArgumentException If the provided items cannot be converted to an array.
     */
    protected function convertToArray(iterable $items) : array
    {
        if (is_array(value: $items)) {
            return $items;
        }

        return iterator_to_array(iterator: $items, preserve_keys: false);
    }

    /**
     * Creates a new locked collection instance from the given items.
     *
     * This named constructor enforces immutability by creating a locked collection,
     * preventing any modifications after instantiation - adhering to DDD value object principles.
     *
     * @template TKey of array-key
     * @template TValue
     * @param iterable<TKey, TValue> $items The source items to populate the collection
     *
     * @return static<TKey, TValue> A new locked collection instance
     * @throws \RuntimeException If the collection cannot be locked
     * @immutable
     */
    public static function lockedFrom(iterable $items) : self
    {
        // First create a new collection instance from the provided items
        // Then immediately lock it to ensure immutability
        return self::make(items: $items)->lock();
    }

    /**
     * Static factory method to create a new Arrhae instance.
     *
     * This method provides a convenient way to instantiate the Arrhae collection
     * with an initial set of items. It accepts any iterable data type, including
     * arrays and objects implementing the Traversable interface.
     *
     * @param iterable $items Initial items for the collection. Can be an array or any Traversable object.
     *
     * @return self A new instance of Arrhae initialized with the provided items.
     *
     * @throws InvalidArgumentException If the provided items cannot be converted to an array.
     *
     * @example
     * use Avax\DataHandling\Arrhae;
     *
     * // Creating a collection from an array
     * $collection = Arrhae::make(['apple', 'banana', 'cherry']);
     *
     * // Creating a collection from a Traversable object
     * $iterator = new ArrayIterator(['apple', 'banana', 'cherry']);
     * $collection = Arrhae::make($iterator);
     */
    public static function make(iterable $items = []) : self
    {
        return new self(items: $items);
    }

    /**
     * Creates an immutable copy of the current instance.
     *
     * This method ensures thread-safety and immutability by creating a deep clone
     * of the current instance and applying a lock mechanism to prevent further modifications.
     * Implement the Immutable Object Pattern for defensive programming.
     *
     * @return self Returns a new locked instance
     * @throws \RuntimeException If the locking mechanism fails
     * @since 8.3.0
     * @immutable
     * @final
     */
    public function toImmutable() : self
    {
        // Create a defensive copy and apply immutability lock
        return clone $this->lock();
    }

    /**
     * Retrieves all items in the `Arrhae` instance.
     *
     * This method returns all items currently stored in the instance as a plain array. It is helpful for getting
     * access to the full dataset.
     *
     * @return array All items in the instance as a standard array.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3]);
     * print_r($arrh->all()); // Output: [1, 2, 3]
     * ```
     */
    public function all() : array
    {
        return $this->items;
    }

    /**
     * Sets a value at the specified key using dot notation for nested arrays.
     *
     * This method allows setting a value at a specific key, even within nested arrays, by using dot notation.
     * If a nested array doesn't exist, it will be created automatically.
     *
     * @param string|int $key   The key to set, which may include dot notation for nested arrays.
     * @param mixed      $value The value to set at the specified key.
     *
     * @return $this Returns the current instance for method chaining.
     *
     * @throws InvalidArgumentException If the key is not a string or integer.
     *
     * ```
     * $arrh = new Arrhae();
     * $arrh->set('user.name', 'Alice');
     * print_r($arrh->all()); // Output: ['user' => ['name' => 'Alice']]
     * ```
     */
    public function set(string|int $key, mixed $value) : self
    {
        if (! is_string(value: $key) && ! is_int(value: $key)) {
            throw new InvalidArgumentException(message: "Key must be a string or an integer.");
        }

        $this->assertNotLocked();

        if (is_string(value: $key) && str_contains(haystack: $key, needle: '.')) {
            $array = &$this->items;
            foreach (explode(separator: '.', string: $key) as $segment) {
                if (! isset($array[$segment]) || ! is_array(value: $array[$segment])) {
                    $array[$segment] = [];
                }

                $array = &$array[$segment];
            }

            $array = $value;
        } else {
            $this->items[$key] = $value;
        }

        return $this;
    }

    /**
     * Checks if a specific key exists using dot notation for nested keys.
     *
     * This method checks if a key exists in the array. If the key contains dot notation, it checks recursively
     * through the nested arrays.
     *
     * @param string|int $key The key to check for existence.
     *
     * @return bool True if the key exists, false otherwise.
     *
     * ```
     * $arrh = new Arrhae(['user' => ['name' => 'Alice']]);
     * echo $arrh->has('user.name'); // Output: true
     * ```
     */
    public function has(string|int $key) : bool
    {
        return $this->get(key: $key) !== null;
    }

    /**
     * Gets a value by key using dot notation for nested arrays.
     *
     * This method retrieves a value for a specific key, supporting dot notation for nested keys.
     * If the key doesn't exist, it returns the provided default value.
     *
     * @param string|int $key     The key to retrieve, supports dot notation for nested arrays.
     * @param mixed      $default The default value to return if the key does not exist. Default is `null`.
     *
     * @return mixed The value associated with the key or the default if the key does not exist.
     *
     * ```
     * $arrh = new Arrhae(['user' => ['name' => 'Alice']]);
     * echo $arrh->get('user.name'); // Output: 'Alice'
     * ```
     */
    public function get(string|int $key, mixed $default = null) : mixed
    {
        if (array_key_exists(key: $key, array: $this->items)) {
            return $this->items[$key];
        }

        if (is_string(value: $key) && str_contains(haystack: $key, needle: '.')) {
            $array = $this->items;
            foreach (explode(separator: '.', string: $key) as $segment) {
                if (is_array(value: $array) && array_key_exists(key: $segment, array: $array)) {
                    $array = $array[$segment];
                } else {
                    return $default;
                }
            }

            return $array;
        }

        return $default;
    }

    /**
     * Removes an item by key, supporting dot notation for nested keys.
     *
     * This method removes a specific key and its associated value from the array. If the key is nested, dot notation
     * will be used to traverse through the levels and remove the item.
     *
     * @param string|int $key The key to remove.
     *
     * @return $this The current instance for method chaining.
     *
     * ```
     * $arrh = new Arrhae(['user' => ['name' => 'Alice']]);
     * $arrh->forget('user.name');
     * print_r($arrh->all()); // Output: ['user' => []]
     * ```
     */
    public function forget(string|int $key) : self
    {
        $this->assertNotLocked();

        if (array_key_exists(key: $key, array: $this->items)) {
            unset($this->items[$key]);
        } elseif (is_string(value: $key) && str_contains(haystack: $key, needle: '.')) {
            $array = &$this->items;
            $keys  = explode(separator: '.', string: $key);
            while (count(value: $keys) > 1) {
                $segment = array_shift(array: $keys);
                if (! isset($array[$segment]) || ! is_array(value: $array[$segment])) {
                    return $this;
                }

                $array = &$array[$segment];
            }

            unset($array[array_shift(array: $keys)]);
        }

        return $this;
    }

    /**
     * Appends a value to the end of the array.
     *
     * This method adds a new value to the end of the current array, allowing dynamic expansion of the items.
     *
     * @param mixed $value The value to append to the array.
     *
     * @return $this The current instance for method chaining.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3]);
     * $arrh->add(4);
     * print_r($arrh->all()); // Output: [1, 2, 3, 4]
     * ```
     */
    public function add(mixed $value) : self
    {
        $this->assertNotLocked();

        $this->items[] = $value;

        return $this;
    }

    /**
     * Returns an iterator for the array.
     *
     * This method returns an iterator that can be used to loop through the items in the array using a `foreach`
     * loop or other iteration methods.
     *
     * @return Traversable An iterator for the items in the collection.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3]);
     * foreach ($arrh as $item) {
     *     echo $item; // Outputs: 1 2 3
     * }
     * ```
     */
    public function getIterator() : Traversable
    {
        return new ArrayIterator(array: $this->items);
    }

    /**
     * Counts the number of items in the array.
     *
     * This method returns the total number of items in the array, equivalent to the result of the `count()` function.
     *
     * @return int The number of items in the array.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3]);
     * echo $arrh->count(); // Output: 3
     * ```
     */
    public function count() : int
    {
        return count(value: $this->items);
    }

    /**
     * Extracts a list of values from the array using the specified key or applies a Closure.
     *
     * This method maps through the items and extracts the values associated with the specified key from each item.
     * If a Closure is provided instead of a key, it applies the Closure to each item and returns the results.
     * If a value doesn't exist or isn't an array when using a key, `null` is returned for that item.
     *
     * @param string|\Closure $key The key to pluck from each item or a Closure to apply to each item.
     *
     * @return array An array containing the plucked values or the results of the Closure for each item.
     *
     * ```
     * // Using a key
     * $arrh = new Arrhae([
     *     ['id' => 1, 'name' => 'John'],
     *     ['id' => 2, 'name' => 'Jane'],
     *     ['id' => 3, 'name' => 'Bob']
     * ]);
     * $names = $arrh->pluck('name');
     * print_r($names); // Output: ['John', 'Jane', 'Bob']
     *
     * // Using a Closure
     * $ages = $arrh->pluck(function($item) {
     *     return $item['id'] * 10;
     * });
     * print_r($ages); // Output: [10, 20, 30]
     * ```
     */
    public function pluck(string|Closure $key) : array
    {
        return match (true) {
            $key instanceof Closure => array_map(callback: $key, array: $this->items),
            default                 => array_map(
                callback: static fn($item) => is_array(value: $item) && array_key_exists(key: $key, array: $item) ? $item[$key] : null,
                array   : $this->items
            ),
        };
    }

    /**
     * Retrieves a value from the items array using the specified key.
     *
     * This method retrieves the value associated with a specific key if it exists in the array, or returns
     * the provided default value if the key is absent.
     *
     * @param string $key     The key to retrieve from the items array.
     * @param mixed  $default The default value to return if the key doesn't exist. Default is `null`.
     *
     * @return mixed The value associated with the key, or the default value if the key doesn't exist.
     *
     * ```
     * $arrh = new Arrhae(['id' => 1, 'name' => 'Alice']);
     * echo $arrh->arrGet('name'); // Output: 'Alice'
     * echo $arrh->arrGet('age', 30); // Output: 30
     * ```
     */
    public function arrGet(string $key, mixed $default = null) : mixed
    {
        if (is_array(value: $this->items) && array_key_exists(key: $key, array: $this->items)) {
            return $this->items[$key];
        }

        return $default;
    }

    /**
     * Retrieves a nested value from an array using a dot-notated key.
     *
     * This method allows retrieving values from a nested array using a dot notation key. If the value is
     * not found at any level, it returns the provided default value.
     *
     * @param string $key     The dot-notated key to retrieve the value.
     * @param mixed  $default The default value to return if the key is not found.
     *
     * @return mixed The nested value associated with the key or the default value if not found.
     *
     * ```
     * $arrh = new Arrhae([
     *     ['user' => ['name' => 'Alice']],
     *     ['user' => ['name' => 'Bob']],
     * ]);
     * echo $arrh->getValue('user.name'); // Output: 'Alice'
     * ```
     */
    public function getValue(string $key, mixed $default = null) : mixed
    {
        $firstItem = $this->first();
        if (! $firstItem || ! is_array(value: $firstItem)) {
            return $default;
        }

        foreach (explode(separator: '.', string: $key) as $segment) {
            if (is_array(value: $firstItem) && array_key_exists(key: $segment, array: $firstItem)) {
                $firstItem = $firstItem[$segment];
            } else {
                return $default;
            }
        }

        return $firstItem;
    }

    /**
     * Retrieves the first item in the array, optionally extracting a specific key using dot notation or applying a
     * Closure.
     *
     * This method returns the first item in the array. If a `$key` is provided, it retrieves the value associated
     * with that key using dot notation, similar to the `get()` method. If `$key` is a Closure, it applies the Closure
     * to the first item and returns the result. If the array is empty or the key does not exist, it returns `null` or
     * the provided default value.
     *
     * @param string|int|Closure|null $key      Optional key to retrieve from the first item, supports dot notation or
     *                                          a Closure.
     * @param mixed                   $default  The default value to return if the key is not found. Default is `null`.
     *
     * @return mixed The first item, the value of the specified key in the first item, the result of the Closure, or
     *               the default value.
     *
     * ```
     * // Without key
     * $arrh = new Arrhae([1, 2, 3]);
     * echo $arrh->first(); // Output: 1
     *
     * // With key
     * $arrh = new Arrhae([
     *     ['user' => ['name' => 'Alice']],
     *     ['user' => ['name' => 'Bob']],
     * ]);
     * echo $arrh->first('user.name'); // Output: 'Alice'
     *
     * // With Closure
     * $arrh = new Arrhae([
     *     ['user' => ['name' => 'Alice', 'age' => 25]],
     *     ['user' => ['name' => 'Bob', 'age' => 30]],
     * ]);
     * $firstUserAge = $arrh->first(function($item) {
     *     return $item['user']['age'];
     * });
     * echo $firstUserAge; // Output: 25
     *
     * // With key that does not exist
     * echo $arrh->first('user.gender', 'unknown'); // Output: 'unknown'
     * ```
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function first(string|int|Closure|null $key = null, mixed $default = null) : mixed
    {
        if ($this->items === []) {
            return $default;
        }

        $firstItem = reset(array: $this->items);
        if ($key === null) {
            return $firstItem;
        }

        if ($key instanceof Closure) {
            return $key($firstItem);
        }

        return $this->getFromItem(item: $firstItem, key: $key, default: $default);
    }

    /**
     * Retrieves a value from a single array item using a key with dot notation or applies a Closure.
     *
     * This helper method is used internally to extract a value from a single array item using a dot-notated key
     * or apply a Closure to it.
     *
     * @param mixed               $item    The array item to extract the value from.
     * @param string|int|\Closure $key     The key to retrieve, supports dot notation or a Closure.
     * @param mixed               $default The default value to return if the key does not exist.
     *
     * @return mixed The value associated with the key, the result of the Closure, or the default value if the key does
     *               not exist.
     */
    protected function getFromItem(mixed $item, string|int|Closure $key, mixed $default = null) : mixed
    {
        if ($key instanceof Closure) {
            return $key($item);
        }

        if (is_array(value: $item)) {
            return $this->getValueFromArray(array: $item, key: $key, default: $default);
        }

        return $default;
    }

    /**
     * Retrieves a value from an array using a key with dot notation.
     *
     * This helper method is used internally to extract a value from an array using a dot-notated key.
     *
     * @param array      $array   The array to extract the value from.
     * @param string|int $key     The key to retrieve, supports dot notation.
     * @param mixed      $default The default value to return if the key does not exist.
     *
     * @return mixed The value associated with the key or the default value if the key does not exist.
     */
    protected function getValueFromArray(array $array, string|int $key, mixed $default = null) : mixed
    {
        if (array_key_exists(key: $key, array: $array)) {
            return $array[$key];
        }

        if (is_string(value: $key) && str_contains(haystack: $key, needle: '.')) {
            $segments = explode(separator: '.', string: $key);
            foreach ($segments as $segment) {
                if (is_array(value: $array) && array_key_exists(key: $segment, array: $array)) {
                    $array = $array[$segment];
                } else {
                    return $default;
                }
            }

            return $array;
        }

        return $default;
    }

    /**
     * Determines whether some items in the array match a given condition.
     *
     * This method iterates over the items and applies the callback function to each item. It returns `true`
     * if the callback returns `true` for any item, and `false` otherwise.
     *
     * @param callable $callback The callback to apply to each item.
     *
     * @return bool `true` if at least one item satisfies the condition, `false` otherwise.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3]);
     * $result = $arrh->some(fn($item) => $item > 2); // Output: true
     * ```
     */
    public function some(callable $callback) : bool
    {
        foreach ($this->items as $item) {
            if ($callback($item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines if the collection is operating in batch mode.
     *
     * Batch mode indicates that the collection is handling multiple items
     * as a single unit of work, which can be useful for bulk operations
     * and performance optimization in data processing scenarios.
     *
     * @return bool True if the collection is in batch mode, false otherwise
     *
     * @since 1.0.0
     * @api
     *
     * @see   \Avax\DataHandling\ArrayHandling\Arrhae::setItems() For setting batch mode
     */
    public function isBatch() : bool
    {
        // Verify the existence of 'batch' flag in the internal items collection
        return isset($this->items['batch']);
    }

    /**
     * Retrieves the items stored in the `Arrhae` instance.
     *
     * This is a protected method that returns the array of items, which is useful in internal operations.
     *
     * @return array The items in the collection.
     */
    protected function getItems() : array
    {
        return $this->items;
    }

    /**
     * Sets the array of items for the current instance.
     *
     * This method allows setting a new array of items for the `Arrhae` instance. It is useful when you want
     * to replace the current set of items with a different array.
     *
     * @param array|iterable $items The new items array to set.
     *
     * @return $this The current instance for method chaining.
     */
    protected function setItems(iterable $items) : static
    {
        $this->assertNotLocked();

        $this->items = $items;

        return $this;
    }
}
