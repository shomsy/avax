<?php

declare(strict_types=1);

namespace Avax\DataHandling\ArrayHandling\Traits;

use Closure;
use InvalidArgumentException;

/**
 * Trait LazyEvaluationTrait
 *
 * Provides methods to enable lazy evaluation on collections.
 * It offers a set of functions to manipulate collections in a memory-efficient manner
 * using generator functions, enabling operations like taking or skipping elements
 * based on conditions, selecting every nth element, and sliding windows of elements.
 *
 * This trait is intended to be used within classes that manage collections of data,
 * such as arrays of associative arrays or objects. It leverages the `AbstractDependenciesTrait`
 * for dependency management, ensuring that the underlying data collection is properly handled.
 *
 * @package Avax\DataHandling\ArrayHandling\Traits
 */
trait LazyEvaluationTrait
{
    use AbstractDependenciesTrait;

    /**
     * Take items from the collection while the callback returns true.
     *
     * Creates a new collection taking elements while the specified condition is met.
     *
     * @param Closure $callback The condition used to continue taking items. It should accept the item and its key as
     *                          parameters and return a boolean.
     *
     * @return static A new lazy collection containing items while the callback returns true.
     *
     * @throws InvalidArgumentException If the callback does not return a boolean.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3, 4, 5]);
     * $result = $arrh->takeWhile(fn($item) => $item < 4);
     * // $result yields 1, 2, 3
     * ```
     */
    public function takeWhile(Closure $callback) : static
    {
        return new static(function () use ($callback) {
            foreach ($this->getItems() as $key => $item) {
                $result = $callback($item, $key);
                if (! is_bool($result)) {
                    throw new InvalidArgumentException(message: 'Callback must return a boolean.');
                }

                if (! $result) {
                    break;
                }

                yield $item;
            }
        });
    }

    /** ***Traversal and Filtering Methods*** */


    /**
     * Skip items in the collection while the callback returns true.
     *
     * Creates a new collection that skips initial elements while the specified condition is met.
     *
     * @param Closure $callback The condition used to skip items. It should accept the item and its key as parameters
     *                          and return a boolean.
     *
     * @return static A new lazy collection skipping items while the callback returns true.
     *
     * @throws InvalidArgumentException If the callback does not return a boolean.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3, 4, 5]);
     * $result = $arrh->skipWhile(fn($item) => $item < 3);
     * // $result yields 3, 4, 5
     * ```
     */
    public function skipWhile(Closure $callback) : static
    {
        return new static(function () use ($callback) {
            $yielding = false;
            foreach ($this->getItems() as $key => $item) {
                if (! $yielding) {
                    $result = $callback($item, $key);
                    if (! is_bool($result)) {
                        throw new InvalidArgumentException(message: 'Callback must return a boolean.');
                    }

                    if (! $result) {
                        $yielding = true;
                        yield $item;
                    }
                } else {
                    yield $item;
                }
            }
        });
    }

    /**
     * Get every nth item in the collection.
     *
     * Useful for scenarios where sampling at regular intervals is required.
     *
     * @param int $step The interval at which items are retrieved. Must be a positive integer.
     *
     * @return static A new lazy collection containing every nth item.
     *
     * @throws InvalidArgumentException If $step is not a positive integer.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3, 4, 5, 6]);
     * $result = $arrh->nth(2);
     * // $result yields 1, 3, 5
     * ```
     */
    public function nth(int $step) : static
    {
        if ($step <= 0) {
            throw new InvalidArgumentException(message: 'Step must be a positive integer.');
        }

        return new static(function () use ($step) {
            $index = 0;
            foreach ($this->getItems() as $item) {
                if ($index++ % $step === 0) {
                    yield $item;
                }
            }
        });
    }

    /**
     * Take items from the collection until the callback returns true.
     *
     * Creates a new collection taking elements until the specified condition is met.
     *
     * @param Closure $callback The condition that stops the taking of items. It should accept the item and its key as
     *                          parameters and return a boolean.
     *
     * @return static A new lazy collection containing items until the callback returns true.
     *
     * @throws InvalidArgumentException If the callback does not return a boolean.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3, 4, 5]);
     * $result = $arrh->takeUntil(fn($item) => $item === 4);
     * // $result yields 1, 2, 3
     * ```
     */
    public function takeUntil(Closure $callback) : static
    {
        return new static(function () use ($callback) {
            foreach ($this->getItems() as $key => $item) {
                $result = $callback($item, $key);
                if (! is_bool($result)) {
                    throw new InvalidArgumentException(message: 'Callback must return a boolean.');
                }

                if ($result) {
                    break;
                }

                yield $item;
            }
        });
    }

    /**
     * Skip items in the collection until the callback returns true.
     *
     * Creates a new collection that starts taking elements once the specified
     * condition is met.
     *
     * @param Closure $callback The condition that starts the taking of items. It should accept the item and its key as
     *                          parameters and return a boolean.
     *
     * @return static A new lazy collection skipping items until the callback returns true.
     *
     * @throws InvalidArgumentException If the callback does not return a boolean.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3, 4, 5]);
     * $result = $arrh->skipUntil(fn($item) => $item === 3);
     * // $result yields 3, 4, 5
     * ```
     */
    public function skipUntil(Closure $callback) : static
    {
        return new static(function () use ($callback) {
            $yielding = false;
            foreach ($this->getItems() as $key => $item) {
                if (! $yielding) {
                    $result = $callback($item, $key);
                    if (! is_bool($result)) {
                        throw new InvalidArgumentException(message: 'Callback must return a boolean.');
                    }

                    if ($result) {
                        $yielding = true;
                        yield $item;
                    }
                } else {
                    yield $item;
                }
            }
        });
    }

    /**
     * Creates a sliding window of items in the collection.
     *
     * Produces sub-arrays (chunks) of size specified, sliding by step count.
     * Useful for windowed computations or batch processing.
     *
     * @param int $size The size of each sliding window. Must be a positive integer.
     * @param int $step The step by which the window slides. Must be a positive integer. Defaults to 1.
     *
     * @return static A new lazy collection containing sliding windows of items.
     *
     * @throws InvalidArgumentException If $size or $step are not positive integers.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3, 4, 5]);
     * $result = $arrh->sliding(3, 1);
     * // $result yields [1, 2, 3], [2, 3, 4], [3, 4, 5]
     * ```
     */
    public function sliding(int $size = 2, int $step = 1) : static
    {
        if ($size <= 0 || $step <= 0) {
            throw new InvalidArgumentException(message: 'Size and step must be positive integers.');
        }

        return new static(function () use ($size, $step) {
            $buffer = [];
            foreach ($this->getItems() as $item) {
                $buffer[] = $item;
                if (count($buffer) === $size) {
                    yield $buffer;
                    array_splice($buffer, 0, $step);
                }
            }

            // Yield remaining items if needed (optional)
            // if (count($buffer) > 0) {
            //     yield $buffer;
            // }
        });
    }

    /**
     * Returns a new instance with the first $limit items of the current collection.
     *
     * This method uses generator functions to yield only the specified number of items,
     * promoting memory efficiency for large datasets.
     *
     * @param int $limit The number of items to take from the beginning of the collection. Must be a non-negative
     *                   integer.
     *
     * @return static A new lazy collection with the first $limit items.
     *
     * @throws InvalidArgumentException If $limit is negative.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3, 4, 5]);
     * $result = $arrh->take(3);
     * // $result yields 1, 2, 3
     * ```
     */
    public function take(int $limit) : static
    {
        if ($limit < 0) {
            throw new InvalidArgumentException(message: 'Limit must be a non-negative integer.');
        }

        return new static(
            iterator_to_array(
                (function () use ($limit) {
                    if ($limit === 0) {
                        return;
                    }

                    $count = 0;
                    foreach ($this->getItems() as $item) {
                        yield $item;
                        if (++$count >= $limit) {
                            break;
                        }
                    }
                })(),
                false
            )
        );
    }

    /**
     * Returns a new instance with the items starting from the $offset position of the current collection.
     *
     * This method utilizes generator functions to skip a certain number of items,
     * promoting memory efficiency for large datasets.
     *
     * @param int $offset The number of items to skip from the beginning of the collection. Must be a non-negative
     *                    integer.
     *
     * @return static A new lazy collection with items starting from the $offset position.
     *
     * @throws InvalidArgumentException If $offset is negative.
     *
     * ```
     * $arrh = new Arrhae([1, 2, 3, 4, 5]);
     * $result = $arrh->skip(2);
     * // $result yields 3, 4, 5
     * ```
     */
    public function skip(int $offset) : static
    {
        if ($offset < 0) {
            throw new InvalidArgumentException(message: 'Offset must be a non-negative integer.');
        }

        return new static(function () use ($offset) {
            if ($offset === 0) {
                foreach ($this->getItems() as $item) {
                    yield $item;
                }

                return;
            }

            $count = 0;
            foreach ($this->getItems() as $item) {
                if ($count++ < $offset) {
                    continue;
                }

                yield $item;
            }
        });
    }

    /**
     * Converts a lazy collection to an eagerly-loaded collection.
     *
     * This method resolves all deferred (lazy) items into an array, allowing for immediate
     * in-memory operations. It's useful for scenarios where further operations require
     * the collection to be fully loaded in memory.
     *
     * @return static A new instance containing the eagerly-loaded collection.
     *
     * ```
     * // Example: Processing a large dataset lazily, then converting to eager for final transformation.
     * $lazyCollection = new Arrhae((function () {
     *     // Simulate fetching a large dataset lazily.
     *     for ($i = 1; $i <= 10000; $i++) {
     *         yield [
     *             'id' => $i,
     *             'value' => $i * 2,
     *             'category' => $i % 2 === 0 ? 'even' : 'odd',
     *         ];
     *     }
     * })());
     *
     * // Step 1: Lazy filtering and mapping.
     * $filteredLazyCollection = $lazyCollection
     *     ->filter(fn($item) => $item['id'] > 5000)  // Keep items with IDs > 5000.
     *     ->map(fn($item) => [
     *         'id' => $item['id'],
     *         'value' => $item['value'] + 10,
     *         'is_even' => $item['category'] === 'even',
     *     ]);
     *
     * // Step 2: Convert to eager-loaded collection.
     * $eagerCollection = $filteredLazyCollection->toEager();
     *
     * // Step 3: Further eager operations.
     * $finalResult = $eagerCollection
     *     ->filter(fn($item) => $item['is_even'])     // Only keep even items.
     *     ->map(fn($item) => [
     *         'id' => $item['id'],
     *         'summary' => "Item ID: {$item['id']}, Value: {$item['value']}",
     *     ])
     *     ->toArray();
     *
     * // Output: $finalResult contains an eagerly-loaded array with transformed data.
     * [
     *     ['id' => 5002, 'summary' => 'Item ID: 5002, Value: 10014'],
     *     ['id' => 5004, 'summary' => 'Item ID: 5004, Value: 10018'],
     *     ...
     * ]
     * ```
     */
    public function toEager() : static
    {
        return new static(iterator_to_array($this->getItems()));
    }


}
