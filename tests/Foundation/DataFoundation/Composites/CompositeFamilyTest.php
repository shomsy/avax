<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\DataFoundation\Composites;

use Avax\DataFoundation\Composites\MapEntry\MapEntry;
use Avax\DataFoundation\Composites\Pair\Pair;
use Avax\DataFoundation\Composites\Record\Record;
use Avax\DataFoundation\Composites\Record\RecordField;
use Avax\DataFoundation\Composites\Tuple\Tuple2;
use Avax\DataFoundation\Composites\Tuple\Tuple3;
use Avax\DataFoundation\Composites\Tuple\Tuple4;
use Avax\Tests\TestCase;

final class CompositeFamilyTest extends TestCase
{
    public function testPairKeepsBothSlots() : void
    {
        $pair = new Pair(first: 'left', second: 'right');

        $this->assertSame('left', $pair->first());
        $this->assertSame('right', $pair->second());
        $this->assertSame(['left', 'right'], $pair->toArray());
    }

    public function testTupleTypesPreserveFixedShape() : void
    {
        $tuple2 = new Tuple2(first: 1, second: 2);
        $tuple3 = new Tuple3(first: 1, second: 2, third: 3);
        $tuple4 = new Tuple4(first: 1, second: 2, third: 3, fourth: 4);

        $this->assertSame([1, 2], $tuple2->toArray());
        $this->assertSame([1, 2, 3], $tuple3->toArray());
        $this->assertSame([1, 2, 3, 4], $tuple4->toArray());
    }

    public function testRecordCanBeBuiltFromFields() : void
    {
        $record = Record::fromFields(
            new RecordField(name: 'name', value: 'Alice'),
            new RecordField(name: 'age', value: 30),
        );

        $this->assertTrue($record->has(name: 'name'));
        $this->assertSame('Alice', $record->get(name: 'name'));
        $this->assertSame(30, $record->get(name: 'age'));
        $this->assertSame(['name' => 'Alice', 'age' => 30], $record->toArray());
    }

    public function testMapEntryKeepsKeyAndValue() : void
    {
        $entry = new MapEntry(key: 'role', value: 'admin');

        $this->assertSame('role', $entry->key());
        $this->assertSame('admin', $entry->value());
    }
}
