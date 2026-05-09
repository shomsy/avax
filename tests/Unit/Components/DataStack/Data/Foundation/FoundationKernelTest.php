<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Data\Foundation;

use Avax\Components\DataStack\Data\System\Foundation\Comparison\Comparator;
use Avax\Components\DataStack\Data\System\Foundation\Comparison\Equality;
use Avax\Components\DataStack\Data\System\Foundation\Comparison\Ordering;
use Avax\Components\DataStack\Data\System\Foundation\Hashing\HashFunction;
use Avax\Components\DataStack\Data\System\Foundation\Hashing\ObjectHash;
use Avax\Components\DataStack\Data\System\Foundation\Hashing\StableHash;
use Avax\Components\DataStack\Data\System\Foundation\Hashing\StringHash;
use Avax\Components\DataStack\Data\System\Foundation\Values\Coordinate;
use Avax\Components\DataStack\Data\System\Foundation\Values\Edge;
use Avax\Components\DataStack\Data\System\Foundation\Values\Entry;
use Avax\Components\DataStack\Data\System\Foundation\Values\Interval;
use Avax\Components\DataStack\Data\System\Foundation\Values\Pair;
use Avax\Components\DataStack\Data\System\Foundation\Values\Point;
use Avax\Components\DataStack\Data\System\Foundation\Values\Priority;
use Avax\Components\DataStack\Data\System\Foundation\Values\Range;
use Avax\Components\DataStack\Data\System\Foundation\Values\Tuple;
use Avax\Components\DataStack\Data\System\Foundation\Values\WeightedEdge;
use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

final class FoundationKernelTest extends TestCase
{
    // -- Comparison interfaces are interfaces --

    public function test_comparison_interfaces_are_interfaces() : void
    {
        foreach ([Comparator::class, Equality::class, Ordering::class] as $interface) {
            self::assertTrue((new ReflectionClass($interface))->isInterface());
        }
    }

    public function test_hash_function_interface_is_interface() : void
    {
        self::assertTrue((new ReflectionClass(HashFunction::class))->isInterface());
    }

    // -- StableHash --

    public function test_stable_hash_produces_deterministic_hashes() : void
    {
        $hasher = new StableHash();
        self::assertSame($hasher->hash(42), $hasher->hash(42));
        self::assertSame($hasher->hash('hello'), $hasher->hash('hello'));
    }

    public function test_stable_hash_type_prefixes_prevent_collisions() : void
    {
        $hasher = new StableHash();
        self::assertNotSame($hasher->hash(1), $hasher->hash('1'));
        self::assertNotSame($hasher->hash(true), $hasher->hash(1));
    }

    public function test_stable_hash_handles_null_bool_int_float_string() : void
    {
        $hasher = new StableHash();
        self::assertSame('null', $hasher->hash(null));
        self::assertSame('bool:1', $hasher->hash(true));
        self::assertSame('bool:0', $hasher->hash(false));
        self::assertStringStartsWith('int:', $hasher->hash(42));
        self::assertStringStartsWith('float:', $hasher->hash(3.14));
        self::assertStringStartsWith('string:', $hasher->hash('test'));
    }

    // -- StringHash --

    public function test_string_hash_uses_algorithm() : void
    {
        $hasher = new StringHash('md5');
        $hash   = $hasher->hash('hello');
        self::assertSame(32, strlen(string: $hash)); // md5 length

        $hasher2 = new StringHash('sha256');
        $hash2   = $hasher2->hash('hello');
        self::assertSame(64, strlen(string: $hash2)); // sha256 length
    }

    public function test_string_hash_is_deterministic() : void
    {
        $hasher = new StringHash();
        self::assertSame($hasher->hash('test'), $hasher->hash('test'));
    }

    // -- ObjectHash --

    public function test_object_hash_produces_different_hashes_for_different_objects() : void
    {
        $hasher = new ObjectHash();
        $a      = new stdClass();
        $b      = new stdClass();
        self::assertNotSame($hasher->hash($a), $hasher->hash($b));
    }

    public function test_object_hash_same_instance_same_hash() : void
    {
        $hasher = new ObjectHash();
        $obj    = new stdClass();
        self::assertSame($hasher->hash($obj), $hasher->hash($obj));
    }

    public function test_object_hash_rejects_non_objects() : void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ObjectHash())->hash('not an object');
    }

    // -- Pair --

    public function test_pair_stores_two_values() : void
    {
        $pair = new Pair(first: 'a', second: 1);
        self::assertSame('a', $pair->first);
        self::assertSame(1, $pair->second);
    }

    public function test_pair_swap() : void
    {
        $pair    = new Pair(first: 'a', second: 1);
        $swapped = $pair->swap();
        self::assertSame(1, $swapped->first);
        self::assertSame('a', $swapped->second);
    }

    public function test_pair_to_array() : void
    {
        $pair = new Pair(first: 'x', second: 'y');
        self::assertSame(['x', 'y'], $pair->toArray());
    }

    // -- Tuple --

    public function test_tuple_stores_values_in_order() : void
    {
        $tuple = new Tuple(1, 'two', 3.0);
        self::assertSame(3, $tuple->size());
        self::assertSame(1, $tuple->at(0));
        self::assertSame('two', $tuple->at(1));
        self::assertSame(3.0, $tuple->at(2));
    }

    public function test_tuple_out_of_bounds() : void
    {
        $this->expectException(OutOfBoundsException::class);
        (new Tuple('a'))->at(5);
    }

    public function test_tuple_first_and_last() : void
    {
        $tuple = new Tuple('a', 'b', 'c');
        self::assertSame('a', $tuple->first());
        self::assertSame('c', $tuple->last());
        self::assertSame('default', (new Tuple())->first('default'));
    }

    // -- Entry --

    public function test_entry_stores_key_value() : void
    {
        $entry = new Entry(key: 'name', value: 'AvaX');
        self::assertSame('name', $entry->key);
        self::assertSame('AvaX', $entry->value);
    }

    public function test_entry_with_value() : void
    {
        $entry   = new Entry(key: 'version', value: 1);
        $updated = $entry->withValue(2);
        self::assertSame(1, $entry->value);
        self::assertSame(2, $updated->value);
        self::assertSame('version', $updated->key);
    }

    // -- Range --

    public function test_range_contains_values() : void
    {
        $range = new Range(start: 1, end: 10);
        self::assertTrue($range->contains(1));
        self::assertTrue($range->contains(5));
        self::assertTrue($range->contains(10));
        self::assertFalse($range->contains(0));
        self::assertFalse($range->contains(11));
    }

    public function test_range_exclusive_boundaries() : void
    {
        $range = new Range(start: 1, end: 10, startInclusive: false, endInclusive: false);
        self::assertFalse($range->contains(1));
        self::assertFalse($range->contains(10));
        self::assertTrue($range->contains(2));
        self::assertTrue($range->contains(9));
    }

    public function test_range_intersects() : void
    {
        $r1 = new Range(start: 1, end: 5);
        $r2 = new Range(start: 3, end: 7);
        $r3 = new Range(start: 10, end: 15);
        self::assertTrue($r1->intersects($r2));
        self::assertFalse($r1->intersects($r3));
    }

    public function test_range_rejects_invalid_order() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new Range(start: 10, end: 1);
    }

    // -- Interval --

    public function test_interval_length_and_midpoint() : void
    {
        $interval = new Interval(start: 0, end: 10);
        self::assertSame(10, $interval->length());
        self::assertSame(5.0, $interval->midpoint());
    }

    public function test_interval_overlaps() : void
    {
        $i1 = new Interval(start: 0, end: 5);
        $i2 = new Interval(start: 3, end: 8);
        $i3 = new Interval(start: 10, end: 15);
        self::assertTrue($i1->overlaps($i2));
        self::assertFalse($i1->overlaps($i3));
    }

    // -- Coordinate --

    public function test_coordinate_dimensionality() : void
    {
        $c2 = new Coordinate(1, 2);
        $c3 = new Coordinate(1, 2, 3);
        self::assertSame(2, $c2->dimensionality());
        self::assertSame(3, $c3->dimensionality());
    }

    public function test_coordinate_distance() : void
    {
        $a = new Coordinate(0, 0);
        $b = new Coordinate(3, 4);
        self::assertSame(5.0, $a->distanceTo($b));
    }

    public function test_coordinate_rejects_empty() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new Coordinate();
    }

    // -- Point --

    public function test_point_distance() : void
    {
        $a = new Point(x: 0, y: 0);
        $b = new Point(x: 3, y: 4);
        self::assertSame(5.0, $a->distanceTo($b));
    }

    public function test_point_to_coordinate() : void
    {
        $point = new Point(x: 1, y: 2, z: 3);
        $coord = $point->toCoordinate();
        self::assertSame(3, $coord->dimensionality());
        self::assertSame(1, $coord->at(0));
    }

    // -- Edge --

    public function test_edge_self_loop() : void
    {
        self::assertTrue((new Edge(from: 'a', to: 'a'))->isSelfLoop());
        self::assertFalse((new Edge(from: 'a', to: 'b'))->isSelfLoop());
    }

    public function test_edge_reverse() : void
    {
        $edge     = new Edge(from: 'a', to: 'b');
        $reversed = $edge->reverse();
        self::assertSame('b', $reversed->from);
        self::assertSame('a', $reversed->to);
    }

    // -- WeightedEdge --

    public function test_weighted_edge() : void
    {
        $edge = new WeightedEdge(from: 'a', to: 'b', weight: 5.5);
        self::assertSame(5.5, $edge->weight);
        self::assertSame('a', $edge->from);
    }

    // -- Priority --

    public function test_priority_comparison() : void
    {
        $p1 = new Priority(value: 1);
        $p2 = new Priority(value: 5);
        self::assertTrue($p1->isHigherThan($p2));
        self::assertTrue($p2->isLowerThan($p1));
        self::assertSame(-1, $p1->compareTo($p2));
        self::assertSame(1, $p2->compareTo($p1));
        self::assertSame(0, $p1->compareTo($p1));
    }
}
