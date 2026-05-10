<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Data;

use Avax\Components\DataStack\Data\System\Capabilities\Forms\CollectionForm\Collection;
use Avax\Components\DataStack\Data\System\Capabilities\Operators\Arrays\ArrayReader;
use Avax\Components\DataStack\Data\System\Capabilities\Operators\Arrays\ArrayWriter;
use Avax\Components\DataStack\Data\System\Flows\Aggregate\AverageValues;
use Avax\Components\DataStack\Data\System\Flows\Aggregate\SumValues;
use Avax\Components\DataStack\Data\System\Flows\Read\ReadNestedValue;
use Avax\Components\DataStack\Data\System\Flows\Write\WriteNestedValue;
use Avax\Components\DataStack\Data\System\PublicSurface\Data;
use PHPUnit\Framework\TestCase;

final class DataTest extends TestCase
{
    private Data $data;

    public function test_get_returns_value() : void
    {
        $array = ['name' => 'AvaX', 'version' => 4];

        $this->assertSame('AvaX', $this->data->get($array, 'name'));
        $this->assertSame(4, $this->data->get($array, 'version'));
    }

    // -- Get / Set Tests --

    public function test_get_returns_default_for_missing() : void
    {
        $array = ['a' => 1];

        $this->assertNull($this->data->get($array, 'missing'));
        $this->assertSame('default', $this->data->get($array, 'missing', 'default'));
    }

    public function test_get_supports_dot_notation() : void
    {
        $array = [
            'user' => ['name' => 'John', 'address' => ['city' => 'NYC']],
        ];

        $this->assertSame('John', $this->data->get($array, 'user.name'));
        $this->assertSame('NYC', $this->data->get($array, 'user.address.city'));
    }

    public function test_set_adds_new_key() : void
    {
        $array = ['a' => 1];
        $this->data->set($array, 'b', 2);

        $this->assertSame(['a' => 1, 'b' => 2], $array);
    }

    public function test_set_overwrites_existing_key() : void
    {
        $array = ['a' => 1];
        $this->data->set($array, 'a', 100);

        $this->assertSame(['a' => 100], $array);
    }

    public function test_set_creates_nested_path() : void
    {
        $array = [];
        $this->data->set($array, 'user.profile.name', 'John');

        $this->assertSame([
                              'user' => [
                                  'profile' => ['name' => 'John'],
                              ],
                          ], $array);
    }

    public function test_sum_with_scalar_values() : void
    {
        $this->assertSame(10, $this->data->sum([1, 2, 3, 4]));
    }

    // -- Sum / Avg Tests --

    public function test_sum_by_property() : void
    {
        $items = [
            ['name' => 'A', 'price' => 10],
            ['name' => 'B', 'price' => 20],
            ['name' => 'C', 'price' => 30],
        ];

        $this->assertSame(60, $this->data->sum($items, 'price'));
    }

    public function test_sum_by_callable() : void
    {
        $items = [1, 2, 3, 4];

        $this->assertSame(10, $this->data->sum($items, static fn (int $item) : int => $item));
    }

    public function test_sum_skips_non_numeric() : void
    {
        $items = [1, 'two', 3, null, 5];

        $this->assertSame(9, $this->data->sum($items));
    }

    public function test_sum_empty_returns_zero() : void
    {
        $this->assertSame(0, $this->data->sum([]));
    }

    public function test_avg_with_scalar_values() : void
    {
        $this->assertSame(2.5, $this->data->avg([1, 2, 3, 4]));
    }

    public function test_avg_by_property() : void
    {
        $items = [
            ['name' => 'A', 'score' => 10],
            ['name' => 'B', 'score' => 20],
            ['name' => 'C', 'score' => 30],
        ];

        $this->assertSame(20.0, $this->data->avg($items, 'score'));
    }

    public function test_avg_empty_returns_zero() : void
    {
        $this->assertSame(0.0, $this->data->avg([]));
    }

    public function test_array_returns_reader() : void
    {
        $reader = $this->data->array();

        $this->assertInstanceOf(ArrayReader::class, $reader);
    }

    // -- Array / Write Accessor Tests --

    public function test_write_returns_writer() : void
    {
        $writer = $this->data->write();

        $this->assertInstanceOf(ArrayWriter::class, $writer);
    }

    public function test_reader_get() : void
    {
        $data = ['name' => 'AvaX'];

        $this->assertSame('AvaX', $this->data->array()->get($data, 'name'));
    }

    public function test_writer_set() : void
    {
        $data = ['a' => 1];
        $this->data->write()->set($data, 'b', 2);

        $this->assertSame(['a' => 1, 'b' => 2], $data);
    }

    public function test_collect_creates_collection() : void
    {
        $collection = $this->data->collect([1, 2, 3]);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertSame([1, 2, 3], $collection->all());
    }

    // -- Collection Tests --

    public function test_collect_empty() : void
    {
        $collection = $this->data->collect();

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertSame([], $collection->all());
    }

    public function test_collect_preserves_keys() : void
    {
        $collection = $this->data->collect(['a' => 1, 'b' => 2]);

        $this->assertSame(['a' => 1, 'b' => 2], $collection->all());
    }

    public function test_full_pipeline_get_set_collect() : void
    {
        $data = ['users' => [
            ['name' => 'John', 'score' => 10],
            ['name' => 'Jane', 'score' => 20],
            ['name' => 'Bob', 'score' => 30],
        ]];

        $this->assertSame('John', $this->data->get($data, 'users.0.name'));

        $this->data->set($data, 'users.3.name', 'Alice');
        $this->data->set($data, 'users.3.score', 40);

        $collection = $this->data->collect($data['users']);

        $this->assertSame(100, $collection->sum('score'));
        $this->assertSame(25.0, $collection->average('score'));
        $this->assertSame(['John', 'Jane', 'Bob', 'Alice'], $collection->pluck('name'));
    }

    // -- Integration Tests --

    public function test_reader_dot_notation() : void
    {
        $data = [
            'config' => [
                'database' => ['host' => 'localhost', 'port' => 3306],
            ],
        ];

        $reader = $this->data->array();

        $this->assertTrue($reader->hasNested($data, 'config.database.host'));
        $this->assertSame('localhost', $reader->getNested($data, 'config.database.host'));
    }

    public function test_writer_forget_and_push() : void
    {
        $data = [
            'tags' => ['php', 'framework'],
            'temp' => 'value',
        ];

        $writer = $this->data->write();

        $writer->forget($data, 'temp');
        $writer->push($data, 'tags', 'testing');

        $this->assertSame(['tags' => ['php', 'framework', 'testing']], $data);
    }

    protected function setUp() : void
    {
        $this->data = new Data(
            arrayReader     : new ArrayReader(),
            arrayWriter     : new ArrayWriter(),
            readNestedValue : new ReadNestedValue(),
            writeNestedValue: new WriteNestedValue(),
            sumValues       : new SumValues(),
            averageValues   : new AverageValues(new SumValues()),
        );
    }
}
