<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\DataFoundation\Arrhae;

use ArrayIterator;
use Avax\DataFoundation\Arrhae;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Characterization tests for Arrhae public API.
 *
 * These tests protect existing behavior during refactor.
 */
final class ArrhaeCharacterizationTest extends TestCase
{
    /** READ OPERATIONS */

    public function testAllReturnsItems() : void
    {
        $items = ['a' => 1, 'b' => 2];
        $arrh  = $this->arrhae(items: $items);

        $this->assertSame($items, $arrh->all());
    }

    private function arrhae(array $items = []) : Arrhae
    {
        return new Arrhae($items);
    }

    public function testGetReturnsValueByKey() : void
    {
        $arrh = $this->arrhae(items: ['name' => 'Alice', 'age' => 30]);

        $this->assertSame('Alice', $arrh->get('name'));
        $this->assertSame(30, $arrh->get('age'));
    }

    public function testGetReturnsDefaultForMissingKey() : void
    {
        $arrh = $this->arrhae(items: ['name' => 'Alice']);

        $this->assertSame('unknown', $arrh->get('missing', 'unknown'));
        $this->assertNull($arrh->get('missing'));
    }

    public function testGetSupportsDotNotation() : void
    {
        $arrh = $this->arrhae(items: [
                                         'user' => [
                                             'name'    => 'Alice',
                                             'address' => ['city' => 'Wonderland'],
                                         ],
                                     ]);

        $this->assertSame('Alice', $arrh->get('user.name'));
        $this->assertSame('Wonderland', $arrh->get('user.address.city'));
        $this->assertNull($arrh->get('user.missing'));
    }

    public function testHasReturnsTrueForExistingKey() : void
    {
        $arrh = $this->arrhae(items: ['name' => 'Alice']);

        $this->assertTrue($arrh->has('name'));
        $this->assertFalse($arrh->has('missing'));
    }

    public function testHasSupportsDotNotation() : void
    {
        $arrh = $this->arrhae(items: [
                                         'user' => ['name' => 'Alice'],
                                     ]);

        $this->assertTrue($arrh->has('user.name'));
        $this->assertFalse($arrh->has('user.missing'));
        $this->assertFalse($arrh->has('user'));
    }

    public function testFirstReturnsFirstItem() : void
    {
        $arrh = $this->arrhae(items: [1, 2, 3]);

        $this->assertSame(1, $arrh->first());
    }

    public function testFirstReturnsDefaultForEmpty() : void
    {
        $arrh = $this->arrhae(items: []);

        $this->assertNull($arrh->first());
        $this->assertSame('default', $arrh->first('default'));
    }

    public function testLastReturnsLastItem() : void
    {
        $arrh = $this->arrhae(items: [1, 2, 3]);

        $this->assertSame(3, $arrh->last());
    }

    public function testPluckExtractsValuesByKey() : void
    {
        $arrh = $this->arrhae(items: [
                                         ['name' => 'Alice', 'age' => 30],
                                         ['name' => 'Bob', 'age' => 25],
                                     ]);

        $this->assertSame(['Alice', 'Bob'], $arrh->pluck('name'));
        $this->assertSame([30, 25], $arrh->pluck('age'));
    }

    public function testPluckWithCallable() : void
    {
        $arrh = $this->arrhae(items: [
                                         ['amount' => 100],
                                         ['amount' => 200],
                                     ]);

        $plucked = $arrh->pluck(fn (array $item) : int => $item['amount'] * 2);
        $this->assertSame([200, 400], $plucked);
    }

    /** WRITE OPERATIONS */

    public function testSetAddsValue() : void
    {
        $arrh   = $this->arrhae(items: ['name' => 'Alice']);
        $result = $arrh->set('age', 30);

        $this->assertSame(['name' => 'Alice', 'age' => 30], $result->all());
        $this->assertNotSame($arrh, $result);
    }

    public function testSetSupportsDotNotation() : void
    {
        $arrh   = $this->arrhae(items: []);
        $result = $arrh->set('user.name', 'Alice');

        $this->assertSame(['user' => ['name' => 'Alice']], $result->all());
    }

    public function testSetCreatesIntermediateArrays() : void
    {
        $arrh   = $this->arrhae(items: []);
        $result = $arrh->set('a.b.c', 'deep');

        $this->assertSame(['a' => ['b' => ['c' => 'deep']]], $result->all());
    }

    public function testForgetRemovesValue() : void
    {
        $arrh   = $this->arrhae(items: ['name' => 'Alice', 'age' => 30]);
        $result = $arrh->forget('name');

        $this->assertSame(['age' => 30], $result->all());
    }

    public function testForgetSupportsDotNotation() : void
    {
        $arrh   = $this->arrhae(items: [
                                           'user' => ['name' => 'Alice', 'age' => 30],
                                       ]);
        $result = $arrh->forget('user.name');

        $this->assertSame(['user' => ['age' => 30]], $result->all());
    }

    public function testAddAppendsValue() : void
    {
        $arrh   = $this->arrhae(items: [1, 2]);
        $result = $arrh->add(3);

        $this->assertSame([1, 2, 3], $result->all());
    }

    public function testPullRemovesAndReturnsValue() : void
    {
        $arrh   = $this->arrhae(items: ['name' => 'Alice']);
        $result = $arrh->pull('name');

        $this->assertSame('Alice', $result->first());
        $this->assertSame([], $result->second()->all());
        $this->assertSame(['name' => 'Alice'], $arrh->all());
    }

    public function testMergeCombinesArrays() : void
    {
        $arrh   = $this->arrhae(items: [1, 2]);
        $result = $arrh->merge([3, 4]);

        $this->assertSame([1, 2, 3, 4], $result->all());
    }

    public function testUnionPreservesExistingKeys() : void
    {
        $arrh   = $this->arrhae(items: [1 => 'one', 2 => 'two']);
        $result = $arrh->union([2 => 'TWO', 3 => 'three']);

        $this->assertSame([1 => 'one', 2 => 'two', 3 => 'three'], $result->all());
    }

    /** TRANSFORM OPERATIONS */

    public function testMapTransformsValues() : void
    {
        $arrh   = $this->arrhae(items: [1, 2, 3]);
        $result = $arrh->map(fn (int $n) : int => $n * 2);

        $this->assertSame([2, 4, 6], $result->all());
    }

    public function testFilterKeepsMatchingValues() : void
    {
        $arrh   = $this->arrhae(items: [1, 2, 3, 4]);
        $result = $arrh->filter(fn (int $n) : bool => $n % 2 === 0);

        $this->assertSame([1 => 2, 3 => 4], $result->all());
    }

    public function testReduceAggregatesValues() : void
    {
        $arrh   = $this->arrhae(items: [1, 2, 3, 4]);
        $result = $arrh->reduce(
            fn (int $carry, int $n) : int => $carry + $n,
            0
        );

        $this->assertSame(10, $result);
    }

    public function testChunkSplitsArray() : void
    {
        $arrh   = $this->arrhae(items: [1, 2, 3, 4, 5]);
        $result = $arrh->chunk(2);

        $this->assertSame([[1, 2], [3, 4], [5 => 5]], $result->all());
    }

    /** SEARCH OPERATIONS */

    public function testContainsFindsValue() : void
    {
        $arrh = $this->arrhae(items: ['apple', 'banana', 'cherry']);

        $this->assertTrue($arrh->contains('banana'));
        $this->assertFalse($arrh->contains('date'));
    }

    public function testSearchReturnsIndex() : void
    {
        $arrh = $this->arrhae(items: ['apple', 'banana', 'cherry']);

        $this->assertSame(1, $arrh->search('banana'));
        $this->assertFalse($arrh->search('date'));
    }

    public function testWhereFiltersByKey() : void
    {
        $arrh   = $this->arrhae(items: [
                                           ['name' => 'Alice', 'active' => true],
                                           ['name' => 'Bob', 'active' => false],
                                       ]);
        $result = $arrh->where('active', true);

        $this->assertCount(1, $result->all());
        $this->assertSame('Alice', $result->first()['name']);
    }

    public function testWhereInFiltersByKeyInArray() : void
    {
        $arrh   = $this->arrhae(items: [
                                           ['name' => 'Alice', 'role' => 'admin'],
                                           ['name' => 'Bob', 'role' => 'editor'],
                                           ['name' => 'Charlie', 'role' => 'subscriber'],
                                       ]);
        $result = $arrh->whereIn('role', ['admin', 'editor']);

        $this->assertCount(2, $result->all());
    }

    public function testWhereBetweenFiltersByRange() : void
    {
        $arrh   = $this->arrhae(items: [
                                           ['name' => 'Alice', 'score' => 85],
                                           ['name' => 'Bob', 'score' => 90],
                                           ['name' => 'Charlie', 'score' => 75],
                                       ]);
        $result = $arrh->whereBetween('score', [80, 90]);

        $this->assertCount(2, $result->all());
    }

    public function testWhereNullFiltersNullValues() : void
    {
        $arrh   = $this->arrhae(items: [
                                           ['name' => 'Alice', 'age' => null],
                                           ['name' => 'Bob', 'age' => 30],
                                       ]);
        $result = $arrh->whereNull('age');

        $this->assertCount(1, $result->all());
    }

    /** AGGREGATE OPERATIONS */

    public function testSumCalculatesTotal() : void
    {
        $arrh = $this->arrhae(items: [
                                         ['amount' => 100],
                                         ['amount' => 200],
                                         ['amount' => 150],
                                     ]);

        $this->assertSame(450, $arrh->sum('amount'));
    }

    public function testAverageCalculatesMean() : void
    {
        $arrh = $this->arrhae(items: [
                                         ['score' => 80],
                                         ['score' => 90],
                                         ['score' => 70],
                                     ]);

        $this->assertSameWithDelta(expected: 80.0, actual: $arrh->average('score'), delta: 0.01);
    }

    private function assertSameWithDelta(float $expected, float $actual, float $delta) : void
    {
        $this->assertLessThanOrEqual($expected + $delta, $actual);
        $this->assertGreaterThanOrEqual($expected - $delta, $actual);
    }

    public function testMinFindsMinimum() : void
    {
        $arrh = $this->arrhae(items: [
                                         ['score' => 80],
                                         ['score' => 90],
                                         ['score' => 70],
                                     ]);

        $this->assertSame(70, $arrh->min('score'));
    }

    public function testMaxFindsMaximum() : void
    {
        $arrh = $this->arrhae(items: [
                                         ['score' => 80],
                                         ['score' => 90],
                                         ['score' => 70],
                                     ]);

        $this->assertSame(90, $arrh->max('score'));
    }

    public function testCountByTalliesValues() : void
    {
        $arrh = $this->arrhae(items: [
                                         ['category' => 'A'],
                                         ['category' => 'B'],
                                         ['category' => 'A'],
                                     ]);

        $counts = $arrh->countBy('category');
        $this->assertSame(['A' => 2, 'B' => 1], $counts);
    }

    public function testGroupByOrganizesItems() : void
    {
        $arrh = $this->arrhae(items: [
                                         ['category' => 'A', 'name' => 'Alice'],
                                         ['category' => 'B', 'name' => 'Bob'],
                                         ['category' => 'A', 'name' => 'Charlie'],
                                     ]);

        $grouped = $arrh->aggregateGroupBy('category');
        $this->assertCount(2, $grouped['A']);
        $this->assertCount(1, $grouped['B']);
    }

    /** ORDER OPERATIONS */

    public function testSortOrdersItems() : void
    {
        $arrh   = $this->arrhae(items: [3, 1, 2]);
        $result = $arrh->sort();

        $this->assertSame([1, 2, 3], $result->all());
    }

    public function testSortWithCallback() : void
    {
        $arrh   = $this->arrhae(items: [3, 1, 2]);
        $result = $arrh->sort(fn (int $a, int $b) : int => $b <=> $a);

        $this->assertSame([3, 2, 1], $result->all());
    }

    public function testReverseReversesOrder() : void
    {
        $arrh   = $this->arrhae(items: [1, 2, 3]);
        $result = $arrh->reverse();

        $this->assertSame([3, 2, 1], $result->all());
    }

    public function testShuffleRandomizesOrder() : void
    {
        $arrh   = $this->arrhae(items: [1, 2, 3, 4, 5]);
        $result = $arrh->shuffle();

        $this->assertCount(5, $result->all());
        $this->assertNotSame($arrh, $result);
    }

    public function testUniqueRemovesDuplicates() : void
    {
        $arrh   = $this->arrhae(items: [1, 2, 2, 3, 3, 3]);
        $result = $arrh->unique();

        $this->assertCount(3, $result->all());
    }

    public function testKeyByReindexesByKey() : void
    {
        $arrh   = $this->arrhae(items: [
                                           ['id' => 'a', 'name' => 'Alice'],
                                           ['id' => 'b', 'name' => 'Bob'],
                                       ]);
        $result = $arrh->keyBy('id');

        $this->assertArrayHasKey('a', $result->all());
        $this->assertArrayHasKey('b', $result->all());
    }

    /** CONVERT OPERATIONS */

    public function testToJsonEncodesToJson() : void
    {
        $arrh = $this->arrhae(items: ['name' => 'Alice', 'age' => 30]);
        $json = $arrh->toJson();

        $decoded = json_decode(json: $json, associative: true);
        $this->assertSame(['name' => 'Alice', 'age' => 30], $decoded);
    }

    public function testToArrayNormalizesItems() : void
    {
        $arrh  = $this->arrhae(items: [['name' => 'Alice'], ['name' => 'Bob']]);
        $array = $arrh->toArray();

        $this->assertCount(2, $array);
    }

    public function testToXmlEncodesToXml() : void
    {
        $arrh = $this->arrhae(items: ['name' => 'Alice']);
        $xml  = $arrh->toXml('user');

        $this->assertStringContainsString('<name>Alice</name>', $xml);
    }

    public function testOnlyKeepsSpecifiedKeys() : void
    {
        $arrh   = $this->arrhae(items: ['name' => 'Alice', 'age' => 30, 'city' => 'Wonderland']);
        $result = $arrh->only(['name', 'city']);

        $this->assertSame(['name' => 'Alice', 'city' => 'Wonderland'], $result->all());
    }

    public function testExceptRemovesSpecifiedKeys() : void
    {
        $arrh   = $this->arrhae(items: ['name' => 'Alice', 'age' => 30, 'city' => 'Wonderland']);
        $result = $arrh->except(['age']);

        $this->assertSame(['name' => 'Alice', 'city' => 'Wonderland'], $result->all());
    }

    /** LOCK OPERATIONS */

    public function testLockMakesCollectionImmutable() : void
    {
        $arrh   = $this->arrhae(items: ['name' => 'Alice']);
        $locked = $arrh->lock();

        $this->assertTrue($locked->isLocked());
        $this->assertNotSame($arrh, $locked);
    }

    public function testLockedCollectionCannotBeModified() : void
    {
        $arrh   = $this->arrhae(items: ['name' => 'Alice']);
        $locked = $arrh->lock();

        $this->expectException(RuntimeException::class);
        $locked->set('age', 30);
    }

    public function testToImmutableCreatesLockedClone() : void
    {
        $arrh      = $this->arrhae(items: ['name' => 'Alice']);
        $immutable = $arrh->toImmutable();

        $this->assertTrue($immutable->isLocked());
    }

    /** STATIC FACTORIES */

    public function testMakeCreatesInstance() : void
    {
        $arrh = Arrhae::make(['name' => 'Alice']);

        $this->assertSame(['name' => 'Alice'], $arrh->all());
    }

    public function testMakeHandlesTraversable() : void
    {
        $arrh = Arrhae::make(new ArrayIterator(array: ['name' => 'Alice']));

        $this->assertSame(['name' => 'Alice'], $arrh->all());
    }

    public function testWrapWrapsValue() : void
    {
        $arrh = Arrhae::wrap('single');

        $this->assertSame(['single'], $arrh->all());
    }

    public function testWrapPreservesArray() : void
    {
        $arrh = Arrhae::wrap(['name' => 'Alice']);

        $this->assertSame(['name' => 'Alice'], $arrh->all());
    }

    public function testWrapPreservesInstance() : void
    {
        $original = Arrhae::make(['name' => 'Alice']);
        $wrapped  = Arrhae::wrap($original);

        $this->assertSame($original, $wrapped);
    }

    /** INTERFACE COMPLIANCE */

    public function testImplementsArrayAccess() : void
    {
        $arrh = $this->arrhae(items: ['name' => 'Alice']);

        $this->assertTrue(isset($arrh['name']));
        $this->assertSame('Alice', $arrh['name']);
    }

    public function testImplementsIteratorAggregate() : void
    {
        $arrh  = $this->arrhae(items: [1, 2, 3]);
        $items = [];

        foreach ($arrh as $item) {
            $items[] = $item;
        }

        $this->assertSame([1, 2, 3], $items);
    }

    public function testImplementsCountable() : void
    {
        $arrh = $this->arrhae(items: [1, 2, 3]);

        $this->assertSame(3, $arrh->count());
    }

    /** HELPER METHODS */

    public function testIsEmptyReturnsTrueForEmptyCollection() : void
    {
        $arrh = $this->arrhae(items: []);

        $this->assertTrue($arrh->isEmpty());
        $this->assertFalse($arrh->isNotEmpty());
    }

    public function testIsNotEmptyReturnsTrueForNonEmptyCollection() : void
    {
        $arrh = $this->arrhae(items: [1]);

        $this->assertFalse($arrh->isEmpty());
        $this->assertTrue($arrh->isNotEmpty());
    }

    public function testDiffReturnsDifference() : void
    {
        $arrh   = $this->arrhae(items: [1, 2, 3, 4]);
        $result = $arrh->diff([2, 3]);

        $this->assertSame([1 => 1, 3 => 4], $result->all());
    }

    public function testIntersectReturnsIntersection() : void
    {
        $arrh   = $this->arrhae(items: [1, 2, 3, 4]);
        $result = $arrh->intersect([2, 3, 5]);

        $this->assertSame([1 => 2, 2 => 3], $result->all());
    }
}
