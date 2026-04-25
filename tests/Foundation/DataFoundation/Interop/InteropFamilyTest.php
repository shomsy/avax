<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\DataFoundation\Interop;

use Avax\DataFoundation\Interop\Arrays\FromArray;
use Avax\DataFoundation\Interop\Arrays\ToArray;
use Avax\DataFoundation\Interop\Generators\FromGenerator;
use Avax\DataFoundation\Interop\Iterables\FromIterable;
use Avax\DataFoundation\Interop\Iterables\ToIterable;
use Avax\DataFoundation\Interop\Json\FromJson;
use Avax\DataFoundation\Interop\Json\ToJson;
use Avax\DataFoundation\Interop\Xml\FromXml;
use Avax\DataFoundation\Interop\Xml\ToXml;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

final class InteropFamilyTest extends TestCase
{
    public function testArrayInteropBuildsPublicTypes() : void
    {
        $collection = FromArray::toCollection(items: ['name' => 'Alice']);
        $list       = FromArray::toDataList(items: ['a', 'b']);
        $map        = FromArray::toMap(items: ['role' => 'admin']);

        $this->assertSame(['name' => 'Alice'], $collection->all());
        $this->assertSame(['a', 'b'], $list->all());
        $this->assertSame('admin', $map->get(key: 'role'));
    }

    public function testArrayAndIterableConversionReturnsStableShapes() : void
    {
        $iterable = ToIterable::from(value: ['name' => 'Alice']);
        $array    = ToArray::from(value: FromIterable::toDataList(items: [1, 2, 3]));

        $this->assertSame(['name' => 'Alice'], iterator_to_array($iterable, true));
        $this->assertSame([1, 2, 3], $array);
    }

    public function testGeneratorInteropRemainsLazyUntilRead() : void
    {
        $calls = 0;

        $lazy = FromGenerator::toLazySequence(factory: function () use (&$calls) : iterable {
            $calls++;
            yield 1;
            yield 2;
            yield 3;
        });

        $this->assertSame(0, $calls);
        $this->assertSame([2, 4], $lazy->map(callback: static fn (int $value) : int => $value * 2)->take(limit: 2)->toArray());
        $this->assertSame(1, $calls);
    }

    public function testJsonInteropRoundTripsArrayBackedValues() : void
    {
        $json = ToJson::from(value: ['name' => 'Alice', 'age' => 30]);

        $this->assertSame(['name' => 'Alice', 'age' => 30], FromJson::toArray(json: $json));
        $this->assertSame('Alice', FromJson::toCollection(json: $json)->get(key: 'name'));
        $this->assertSame(30, FromJson::toMap(json: $json)->get(key: 'age'));
    }

    public function testXmlInteropRoundTripsSimpleShapes() : void
    {
        $xml   = ToXml::from(value: ['name' => 'Alice', 'meta' => ['city' => 'Belgrade']], rootElement: 'user');
        $array = FromXml::toArray(xml: $xml);

        $this->assertSame('Alice', $array['name']);
        $this->assertSame('Belgrade', $array['meta']['city']);
        $this->assertSame($array, FromXml::toArrhae(xml: $xml)->all());
    }
}
