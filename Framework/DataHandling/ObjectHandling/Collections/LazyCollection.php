<?php

declare(strict_types=1);

namespace Gemini\DataHandling\ObjectHandling\Collections;

use Gemini\DataHandling\ArrayHandling\Traits\LazyEvaluationTrait;
use Closure;
use Traversable;

/**
 * Class LazyCollection
 *
 * This class represents a collection with lazy evaluation, where items are generated on the fly.
 * The primary benefit is memory efficiency, especially for large datasets.
 */
class LazyCollection extends Collection
{
    use LazyEvaluationTrait;

    /**
     * LazyCollection constructor.
     *
     * @param Closure $generator A generator function to build the collection lazily.
     */
    public function __construct(protected Closure $generator)
    {
        parent::__construct();
    }

    /**
     * Retrieve all items in the collection as an array.
     *
     * Converts the lazy-loaded items into a straightforward array.
     *
     * @return array The entire collection as an array.
     */
    public function all() : array
    {
        return iterator_to_array($this->getIterator());
    }

    /**
     * Get an iterator for the collection.
     *
     * This method facilitates the lazy evaluation by returning the generator.
     *
     * @return Traversable The generator yielding items of the collection.
     */
    public function getIterator() : Traversable
    {
        return ($this->generator)();
    }

    /**
     * Get every nth item in the collection.
     *
     * This method is useful for scenarios where sampling at regular intervals is required.
     *
     * @param int $step The interval at which items are retrieved.
     *
     * @return static A new lazy collection containing every nth item.
     */
    public function nth(int $step) : static
    {
        return new static(generator: function () use ($step) {
            $index = 0;
            foreach ($this->getIterator() as $item) {
                if ($index++ % $step === 0) {
                    yield $item;
                }
            }
        });
    }

    /**
     * Take items from the collection while the callback returns true.
     *
     * This allows conditional data processing where items are taken as long as a condition holds.
     *
     * @param Closure $callback The condition used to continue taking items.
     *
     * @return static A new lazy collection containing items while the callback returns true.
     */
    public function takeWhile(Closure $callback) : static
    {
        return new static(generator: function () use ($callback) {
            foreach ($this->getIterator() as $item) {
                if (! $callback($item)) {
                    break;
                }

                yield $item;
            }
        });
    }
}
