<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Data;

use Avax\Components\DataStack\Data\System\Capabilities\Operators\Arrays\ArrayWriter;
use PHPUnit\Framework\TestCase;

final class ArrayWriterTest extends TestCase
{
    private ArrayWriter $writer;

    public function test_set_adds_new_key() : void
    {
        $data = ['a' => 1];
        $this->writer->set($data, 'b', 2);

        $this->assertSame(['a' => 1, 'b' => 2], $data);
    }

    // -- Set Tests --

    public function test_set_overwrites_existing_key() : void
    {
        $data = ['a' => 1];
        $this->writer->set($data, 'a', 100);

        $this->assertSame(['a' => 100], $data);
    }

    public function test_set_with_null_value() : void
    {
        $data = ['a' => 1];
        $this->writer->set($data, 'a', null);

        $this->assertSame(['a' => null], $data);
    }

    public function test_set_with_array_value() : void
    {
        $data = [];
        $this->writer->set($data, 'config', ['key' => 'value']);

        $this->assertSame(['config' => ['key' => 'value']], $data);
    }

    public function test_setNested_creates_nested_path() : void
    {
        $data = [];
        $this->writer->setNested($data, 'user.profile.name', 'John');

        $this->assertSame([
                              'user' => [
                                  'profile' => ['name' => 'John'],
                              ],
                          ], $data);
    }

    // -- SetNested Tests --

    public function test_setNested_adds_to_existing_structure() : void
    {
        $data = ['user' => ['name' => 'John']];
        $this->writer->setNested($data, 'user.email', 'john@example.com');

        $this->assertSame([
                              'user' => [
                                  'name' => 'John',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            'email' => 'john@example.com',
                              ],
                          ], $data);
    }

    public function test_setNested_overwrites_deep_value() : void
    {
        $data = ['user' => ['profile' => ['name' => 'John']]];
        $this->writer->setNested($data, 'user.profile.name', 'Jane');

        $this->assertSame([
                              'user' => [
                                  'profile' => ['name' => 'Jane'],
                              ],
                          ], $data);
    }

    public function test_setNested_creates_intermediate_arrays() : void
    {
        $data = ['user' => ['name' => 'John']];
        $this->writer->setNested($data, 'user.profile.address.city', 'NYC');

        $this->assertSame([
                              'user' => [
                                  'name' => 'John',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            'profile' => [
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                'address' => ['city' => 'NYC'],
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ],
                              ],
                          ], $data);
    }

    public function test_setNested_replaces_scalar_with_array() : void
    {
        $data = ['user' => 'John'];
        $this->writer->setNested($data, 'user.profile.name', 'John Doe');

        $this->assertSame([
                              'user' => [
                                  'profile' => ['name' => 'John Doe'],
                              ],
                          ], $data);
    }

    public function test_setNested_with_single_key() : void
    {
        $data = [];
        $this->writer->setNested($data, 'name', 'John');

        $this->assertSame(['name' => 'John'], $data);
    }

    public function test_forget_removes_key() : void
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];
        $this->writer->forget($data, 'b');

        $this->assertSame(['a' => 1, 'c' => 3], $data);
    }

    // -- Forget Tests --

    public function test_forget_missing_key_does_nothing() : void
    {
        $data = ['a' => 1];
        $this->writer->forget($data, 'missing');

        $this->assertSame(['a' => 1], $data);
    }

    public function test_forget_on_empty_array() : void
    {
        $data = [];
        $this->writer->forget($data, 'anything');

        $this->assertSame([], $data);
    }

    public function test_forgetNested_removes_deep_value() : void
    {
        $data = [
            'user' => [
                'name'  => 'John',
                'email' => 'john@example.com',
            ],
        ];
        $this->writer->forgetNested($data, 'user.email');

        $this->assertSame([
                              'user' => ['name' => 'John'],
                          ], $data);
    }

    // -- ForgetNested Tests --

    public function test_forgetNested_with_missing_intermediate() : void
    {
        $data = ['user' => ['name' => 'John']];
        $this->writer->forgetNested($data, 'user.profile.age');

        $this->assertSame(['user' => ['name' => 'John']], $data);
    }

    public function test_forgetNested_with_missing_path() : void
    {
        $data = ['user' => ['name' => 'John']];
        $this->writer->forgetNested($data, 'missing.profile.value');

        $this->assertSame(['user' => ['name' => 'John']], $data);
    }

    public function test_forgetNested_with_single_key() : void
    {
        $data = ['name' => 'John'];
        $this->writer->forgetNested($data, 'name');

        $this->assertSame([], $data);
    }

    public function test_push_adds_to_existing_array() : void
    {
        $data = ['items' => [1, 2]];
        $this->writer->push($data, 'items', 3);

        $this->assertSame(['items' => [1, 2, 3]], $data);
    }

    // -- Push Tests --

    public function test_push_creates_array_if_not_exists() : void
    {
        $data = [];
        $this->writer->push($data, 'items', 1);

        $this->assertSame(['items' => [1]], $data);
    }

    public function test_push_creates_array_if_scalar() : void
    {
        $data = ['items' => 'not-an-array'];
        $this->writer->push($data, 'items', 1);

        $this->assertSame(['items' => [1]], $data);
    }

    public function test_push_multiple_values() : void
    {
        $data = [];
        $this->writer->push($data, 'items', 1);
        $this->writer->push($data, 'items', 2);
        $this->writer->push($data, 'items', 3);

        $this->assertSame(['items' => [1, 2, 3]], $data);
    }

    public function test_set_with_integer_key() : void
    {
        $data = [];
        $this->writer->set($data, '0', 'first');

        $this->assertSame(['0' => 'first'], $data);
    }

    // -- Edge Cases --

    public function test_setNested_with_deep_numeric_keys() : void
    {
        $data = [];
        $this->writer->setNested($data, 'users.0.name', 'John');
        $this->writer->setNested($data, 'users.1.name', 'Jane');

        $this->assertSame([
                              'users' => [
                                  '0' => ['name' => 'John'],
                                  '1' => ['name' => 'Jane'],
                              ],
                          ], $data);
    }

    public function test_forgetNested_on_empty_data() : void
    {
        $data = [];
        $this->writer->forgetNested($data, 'user.name');

        $this->assertSame([], $data);
    }

    public function test_push_preserves_mixed_values() : void
    {
        $data = ['items' => [1]];
        $this->writer->push($data, 'items', 'two');
        $this->writer->push($data, 'items', ['three' => 3]);

        $this->assertSame(['items' => [1, 'two', ['three' => 3]]], $data);
    }

    protected function setUp() : void
    {
        $this->writer = new ArrayWriter();
    }
}
