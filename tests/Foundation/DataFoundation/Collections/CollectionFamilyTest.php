<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\DataFoundation\Collections;

use Avax\DataFoundation\Collections\DataList\DataList;
use Avax\DataFoundation\Collections\Map\Map;
use Avax\DataFoundation\Collections\MultiMap\MultiMap;
use Avax\DataFoundation\Collections\Sequence\Sequence;
use Avax\DataFoundation\Collections\Set\Set;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

final class CollectionFamilyTest extends TestCase
{
    public function testDataListKeepsSequentialKeys() : void
    {
        $list = new DataList(items: [2 => 'a', 5 => 'b']);

        $this->assertSame(['a', 'b'], $list->all());
    }

    public function testSetKeepsUniqueValues() : void
    {
        $set = new Set(items: [1, 2, 2, 3]);

        $this->assertSame([1, 2, 3], $set->all());
    }

    public function testMapStoresKeyValuePairs() : void
    {
        $map = new Map(items: ['name' => 'Alice'])->put(key: 'age', value: 30);

        $this->assertSame('Alice', $map->get(key: 'name'));
        $this->assertSame(30, $map->get(key: 'age'));
    }

    public function testMultiMapKeepsMultipleValuesPerKey() : void
    {
        $map = new MultiMap()->put(key: 'role', value: 'admin')->put(key: 'role', value: 'editor');

        $this->assertSame(['admin', 'editor'], $map->get(key: 'role'));
    }

    public function testSequenceTransformsValuesInOrder() : void
    {
        $sequence = new Sequence(items: [1, 2, 3])
            ->map(static fn (int $value) : int => $value * 2)
            ->filter(static fn (int $value) : bool => $value > 2);

        $this->assertSame([4, 6], $sequence->all());
    }
}
