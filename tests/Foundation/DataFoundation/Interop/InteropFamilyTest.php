<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\DataFoundation\Interop;

use Avax\Components\DataStack\Database\Interop\Arrays\FromArray;
use Avax\Components\DataStack\Database\Interop\Arrays\ToArray;
use Avax\Components\DataStack\Database\Interop\Generators\FromGenerator;
use Avax\Components\DataStack\Database\Interop\Iterables\FromIterable;
use Avax\Components\DataStack\Database\Interop\Iterables\ToIterable;
use Avax\Components\DataStack\Database\Interop\Json\FromJson;
use Avax\Components\DataStack\Database\Interop\Json\ToJson;
use Avax\Components\DataStack\Database\Interop\Xml\FromXml;
use Avax\Components\DataStack\Database\Interop\Xml\ToXml;
use Avax\Tests\TestCase;

final class InteropFamilyTest extends TestCase
{
    public function test_array_interop_builds_public_types() : void
    {
        $collection = FromArray::toCollection(items: ['name' => 'Alice']);
        $list = FromArray::toDataList(items: ['a', 'b']);
        $map = FromArray::toMap(items: ['role' => 'admin']);

        $this->assertSame(['name' => 'Alice'], $collection->all());
        $this->assertSame(['a', 'b'], $list->all());
        $this->assertSame('admin', $map->get(key: 'role'));
    }

    public function test_array_and_iterable_conversion_returns_stable_shapes() : void
    {
        $iterable = ToIterable::from(value: ['name' => 'Alice']);
        $array = ToArray::from(value: FromIterable::toDataList(items: [1, 2, 3]));

        $this->assertSame(['name' => 'Alice'], iterator_to_array($iterable, true));
        $this->assertSame([1, 2, 3], $array);
    }

    public function test_generator_interop_remains_lazy_until_read() : void
    {
        $calls = 0;

        $lazy = FromGenerator::toLazySequence(factory: static function () use (&$calls) : iterable {
            $calls++;
            yield 1;
            yield 2;
            yield 3;
        });

        $this->assertSame(0, $calls);
        $this->assertSame([2, 4], $lazy->map(callback: static fn (int $value) : int => $value * 2)->take(limit: 2)->toArray());
        $this->assertSame(1, $calls);
    }

    public function test_json_interop_round_trips_array_backed_values() : void
    {
        $json = ToJson::from(value: ['name' => 'Alice', 'age' => 30]);

        $this->assertSame(['name' => 'Alice', 'age' => 30], FromJson::toArray(json: $json));
        $this->assertSame('Alice', FromJson::toCollection(json: $json)->get(key: 'name'));
        $this->assertSame(30, FromJson::toMap(json: $json)->get(key: 'age'));
    }

    public function test_xml_interop_round_trips_simple_shapes() : void
    {
        $xml = ToXml::from(value: ['name' => 'Alice', 'meta' => ['city' => 'Belgrade']], rootElement: 'user');
        $array = FromXml::toArray(xml: $xml);

        $this->assertSame('Alice', $array['name']);
        $this->assertSame('Belgrade', $array['meta']['city']);
        $this->assertSame($array, FromXml::toArrhae(xml: $xml)->all());
    }
}
