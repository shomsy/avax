<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\DataFoundation\Composites;

use Avax\Components\DataStack\Database\Composites\MapEntry\MapEntry;
use Avax\Components\DataStack\Database\Composites\Pair\Pair;
use Avax\Components\DataStack\Database\Composites\Record\Record;
use Avax\Components\DataStack\Database\Composites\Record\RecordField;
use Avax\Components\DataStack\Database\Composites\Tuple\Tuple2;
use Avax\Components\DataStack\Database\Composites\Tuple\Tuple3;
use Avax\Components\DataStack\Database\Composites\Tuple\Tuple4;
use Avax\Tests\TestCase;

final class CompositeFamilyTest extends TestCase
{
    public function test_pair_keeps_both_slots() : void
    {
        $pair = new Pair(first: 'left', second: 'right');

        $this->assertSame('left', $pair->first());
        $this->assertSame('right', $pair->second());
        $this->assertSame(['left', 'right'], $pair->toArray());
    }

    public function test_tuple_types_preserve_fixed_shape() : void
    {
        $tuple2 = new Tuple2(first: 1, second: 2);
        $tuple3 = new Tuple3(first: 1, second: 2, third: 3);
        $tuple4 = new Tuple4(first: 1, second: 2, third: 3, fourth: 4);

        $this->assertSame([1, 2], $tuple2->toArray());
        $this->assertSame([1, 2, 3], $tuple3->toArray());
        $this->assertSame([1, 2, 3, 4], $tuple4->toArray());
    }

    public function test_record_can_be_built_from_fields() : void
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

    public function test_map_entry_keeps_key_and_value() : void
    {
        $entry = new MapEntry(key: 'role', value: 'admin');

        $this->assertSame('role', $entry->key());
        $this->assertSame('admin', $entry->value());
    }
}
