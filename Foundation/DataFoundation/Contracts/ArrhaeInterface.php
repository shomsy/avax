<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Contracts;

use ArrayAccess;
use Avax\DataFoundation\Collection;
use Avax\DataFoundation\Composites\Pair\Pair;
use Countable;
use IteratorAggregate;

/**
 * Public contract for the raw array facade.
 */
interface ArrhaeInterface extends ArrayAccess, IteratorAggregate, Countable
{
    public function __construct(array $items = []);

    public static function from(iterable $items) : static;

    public static function make(iterable $items = []) : static;

    public static function wrap(mixed $value) : static;

    public function all() : array;

    public function get(string $key, mixed $default = null) : mixed;

    public function has(string $key) : bool;

    public function set(string $key, mixed $value) : static;

    public function forget(string $key) : static;

    public function add(mixed $value) : static;

    public function pull(string $key) : Pair;

    public function merge(array $items) : static;

    public function union(array $items) : static;

    public function diff(array $items) : static;

    public function intersect(array $items) : static;

    public function count() : int;

    public function isEmpty() : bool;

    public function isNotEmpty() : bool;

    public function first(mixed $default = null) : mixed;

    public function last(mixed $default = null) : mixed;

    public function map(callable $callback) : static;

    public function filter(callable $callback) : static;

    public function reduce(callable $callback, mixed $initial = null) : mixed;

    public function average(string|callable $key) : float;

    public function sum(string|callable $key) : int|float;

    public function min(string|callable $key) : mixed;

    public function max(string|callable $key) : mixed;

    public function contains(mixed $value) : bool;

    public function search(mixed $value) : int|false;

    public function whereIn(string $key, array $values) : static;

    public function whereBetween(string $key, array $range) : static;

    public function whereNull(string $key) : static;

    public function where(string $key, mixed $value) : static;

    public function whereNotNull(string $key) : static;

    public function sort(?callable $callback = null) : static;

    public function reverse() : static;

    public function shuffle() : static;

    public function unique() : static;

    public function chunk(int $size) : static;

    public function groupBy(string|callable $key) : array;

    public function aggregateGroupBy(string|callable $key) : array;

    public function countBy(string|callable $key) : array;

    public function keyBy(string|callable $key) : static;

    public function pluck(string|callable $key) : array;

    public function keys() : array;

    public function values() : static;

    public function flip() : static;

    public function toJson(int $flags = 0) : string;

    public function toArray() : array;

    public function toXml(string $rootElement = 'root') : string;

    public function only(array $keys) : static;

    public function except(array $keys) : static;

    public function tap(callable $callback) : static;

    public function when(bool $condition, callable $callback) : static;

    public function unless(bool $condition, callable $callback) : static;

    public function isLocked() : bool;

    public function lock() : static;

    public function toImmutable() : static;

    public function collect() : Collection;
}
