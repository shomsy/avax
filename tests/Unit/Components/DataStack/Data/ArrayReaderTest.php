<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Data;

use Avax\Components\DataStack\Data\System\Capabilities\Operators\Arrays\ArrayReader;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ArrayReaderTest extends TestCase
{
    private ArrayReader $reader;

    public function test_get_returns_value() : void
    {
        $data = ['name' => 'AvaX', 'version' => 4];

        $this->assertSame('AvaX', $this->reader->get($data, 'name'));
        $this->assertSame(4, $this->reader->get($data, 'version'));
    }

    // -- Basic Get / Has Tests --

    public function test_get_returns_default_for_missing_key() : void
    {
        $data = ['a' => 1];

        $this->assertNull($this->reader->get($data, 'missing'));
        $this->assertSame('default', $this->reader->get($data, 'missing', 'default'));
    }

    public function test_get_returns_default_for_null_value() : void
    {
        $data = ['key' => null];

        $this->assertNull($this->reader->get($data, 'key'));
        $this->assertSame('fallback', $this->reader->get($data, 'key', 'fallback'));
    }

    public function test_has_returns_true_for_existing_key() : void
    {
        $data = ['a' => 1, 'b' => 2];

        $this->assertTrue($this->reader->has($data, 'a'));
        $this->assertTrue($this->reader->has($data, 'b'));
    }

    public function test_has_returns_false_for_missing_key() : void
    {
        $data = ['a' => 1];

        $this->assertFalse($this->reader->has($data, 'c'));
    }

    public function test_has_returns_true_for_null_value() : void
    {
        $data = ['key' => null];

        $this->assertTrue($this->reader->has($data, 'key'));
    }

    public function test_only_keeps_specified_keys() : void
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];

        $result = $this->reader->only($data, ['a', 'c']);

        $this->assertSame(['a' => 1, 'c' => 3], $result);
    }

    // -- Only / Except Tests --

    public function test_only_ignores_missing_keys() : void
    {
        $data = ['a' => 1];

        $result = $this->reader->only($data, ['a', 'z']);

        $this->assertSame(['a' => 1], $result);
    }

    public function test_only_empty_keys_returns_empty() : void
    {
        $data = ['a' => 1, 'b' => 2];

        $result = $this->reader->only($data, []);

        $this->assertSame([], $result);
    }

    public function test_except_removes_specified_keys() : void
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];

        $result = $this->reader->except($data, ['b']);

        $this->assertSame(['a' => 1, 'c' => 3], $result);
    }

    public function test_except_ignores_missing_keys() : void
    {
        $data = ['a' => 1];

        $result = $this->reader->except($data, ['b']);

        $this->assertSame(['a' => 1], $result);
    }

    public function test_get_string_returns_string() : void
    {
        $data = ['name' => 'AvaX'];

        $this->assertSame('AvaX', $this->reader->getString($data, 'name'));
    }

    // -- Type-Safe Reader Tests --

    public function test_get_string_casts_to_string() : void
    {
        $data = ['count' => 42];

        $this->assertSame('42', $this->reader->getString($data, 'count'));
    }

    public function test_get_string_returns_default_for_null() : void
    {
        $data = ['name' => null];

        $this->assertNull($this->reader->getString($data, 'name'));
        $this->assertSame('default', $this->reader->getString($data, 'name', 'default'));
    }

    public function test_get_string_returns_default_for_missing() : void
    {
        $data = [];

        $this->assertNull($this->reader->getString($data, 'name'));
        $this->assertSame('default', $this->reader->getString($data, 'name', 'default'));
    }

    public function test_get_stringOrFail_returns_string() : void
    {
        $data = ['name' => 'AvaX'];

        $this->assertSame('AvaX', $this->reader->getStringOrFail($data, 'name'));
    }

    public function test_get_stringOrFail_throws_for_missing() : void
    {
        $data = [];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Key 'name' is required and must be a string.");

        $this->reader->getStringOrFail($data, 'name');
    }

    public function test_get_stringOrFail_throws_for_non_string() : void
    {
        $data = ['count' => 42];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Key 'count' is required and must be a string.");

        $this->reader->getStringOrFail($data, 'count');
    }

    public function test_get_stringOrFail_throws_for_null() : void
    {
        $data = ['name' => null];

        $this->expectException(InvalidArgumentException::class);

        $this->reader->getStringOrFail($data, 'name');
    }

    public function test_get_int_returns_int() : void
    {
        $data = ['count' => 42];

        $this->assertSame(42, $this->reader->getInt($data, 'count'));
    }

    public function test_get_int_casts_to_int() : void
    {
        $data = ['count' => '42'];

        $this->assertSame(42, $this->reader->getInt($data, 'count'));
    }

    public function test_get_int_returns_default_for_null() : void
    {
        $data = ['count' => null];

        $this->assertNull($this->reader->getInt($data, 'count'));
        $this->assertSame(0, $this->reader->getInt($data, 'count', 0));
    }

    public function test_get_int_returns_default_for_missing() : void
    {
        $data = [];

        $this->assertNull($this->reader->getInt($data, 'count'));
        $this->assertSame(0, $this->reader->getInt($data, 'count', 0));
    }

    public function test_get_intOrFail_returns_int() : void
    {
        $data = ['count' => 42];

        $this->assertSame(42, $this->reader->getIntOrFail($data, 'count'));
    }

    public function test_get_intOrFail_casts_numeric_string() : void
    {
        $data = ['count' => '42'];

        $this->assertSame(42, $this->reader->getIntOrFail($data, 'count'));
    }

    public function test_get_intOrFail_throws_for_missing() : void
    {
        $data = [];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Key 'count' is required and must be an integer.");

        $this->reader->getIntOrFail($data, 'count');
    }

    public function test_get_intOrFail_throws_for_non_numeric() : void
    {
        $data = ['count' => 'abc'];

        $this->expectException(InvalidArgumentException::class);

        $this->reader->getIntOrFail($data, 'count');
    }

    public function test_get_float_returns_float() : void
    {
        $data = ['price' => 3.14];

        $this->assertSame(3.14, $this->reader->getFloat($data, 'price'));
    }

    public function test_get_float_casts_to_float() : void
    {
        $data = ['price' => '3.14'];

        $this->assertSame(3.14, $this->reader->getFloat($data, 'price'));
    }

    public function test_get_float_returns_default_for_null() : void
    {
        $data = ['price' => null];

        $this->assertNull($this->reader->getFloat($data, 'price'));
        $this->assertSame(0.0, $this->reader->getFloat($data, 'price', 0.0));
    }

    public function test_get_float_returns_default_for_missing() : void
    {
        $data = [];

        $this->assertNull($this->reader->getFloat($data, 'price'));
        $this->assertSame(0.0, $this->reader->getFloat($data, 'price', 0.0));
    }

    public function test_get_bool_returns_true() : void
    {
        $data = ['active' => true];

        $this->assertTrue($this->reader->getBool($data, 'active'));
    }

    public function test_get_bool_returns_false() : void
    {
        $data = ['active' => false];

        $this->assertFalse($this->reader->getBool($data, 'active'));
    }

    public function test_get_bool_casts_string_true() : void
    {
        $data = ['active' => 'true'];

        $this->assertTrue($this->reader->getBool($data, 'active'));
    }

    public function test_get_bool_casts_string_false() : void
    {
        $data = ['active' => 'false'];

        $this->assertFalse($this->reader->getBool($data, 'active'));
    }

    public function test_get_bool_casts_numeric_1() : void
    {
        $data = ['active' => 1];

        $this->assertTrue($this->reader->getBool($data, 'active'));
    }

    public function test_get_bool_casts_numeric_0() : void
    {
        $data = ['active' => 0];

        $this->assertFalse($this->reader->getBool($data, 'active'));
    }

    public function test_get_bool_returns_default_for_null() : void
    {
        $data = ['active' => null];

        $this->assertNull($this->reader->getBool($data, 'active'));
        $this->assertTrue($this->reader->getBool($data, 'active', true));
    }

    public function test_get_bool_returns_default_for_missing() : void
    {
        $data = [];

        $this->assertNull($this->reader->getBool($data, 'active'));
        $this->assertFalse($this->reader->getBool($data, 'active', false));
    }

    public function test_get_array_returns_array() : void
    {
        $data = ['items' => [1, 2, 3]];

        $this->assertSame([1, 2, 3], $this->reader->getArray($data, 'items'));
    }

    public function test_get_array_returns_default_for_non_array() : void
    {
        $data = ['items' => 'not-an-array'];

        $this->assertSame([], $this->reader->getArray($data, 'items'));
        $this->assertSame(['fallback'], $this->reader->getArray($data, 'items', ['fallback']));
    }

    public function test_get_array_returns_default_for_missing() : void
    {
        $data = [];

        $this->assertSame([], $this->reader->getArray($data, 'items'));
        $this->assertSame(['fallback'], $this->reader->getArray($data, 'items', ['fallback']));
    }

    public function test_require_returns_value() : void
    {
        $data = ['name' => 'AvaX'];

        $this->assertSame('AvaX', $this->reader->require($data, 'name'));
    }

    // -- Require Tests --

    public function test_require_throws_for_missing() : void
    {
        $data = [];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Required key 'name' is missing");

        $this->reader->require($data, 'name');
    }

    public function test_require_returns_null_value() : void
    {
        $data = ['name' => null];

        $this->assertNull($this->reader->require($data, 'name'));
    }

    public function test_get_nested_reads_deep_path() : void
    {
        $data = [
            'user' => [
                'profile' => [
                    'name'    => 'John',
                    'address' => ['city' => 'NYC'],
                ],
            ],
        ];

        $this->assertSame('John', $this->reader->getNested($data, 'user.profile.name'));
        $this->assertSame('NYC', $this->reader->getNested($data, 'user.profile.address.city'));
    }

    // -- Nested Path Tests --

    public function test_get_nested_returns_default_for_missing_segment() : void
    {
        $data = ['user' => ['name' => 'John']];

        $this->assertNull($this->reader->getNested($data, 'user.profile.age'));
        $this->assertSame('unknown', $this->reader->getNested($data, 'user.profile.age', 'unknown'));
    }

    public function test_get_nested_returns_default_for_non_array_intermediate() : void
    {
        $data = ['user' => ['name' => 'John']];

        $this->assertNull($this->reader->getNested($data, 'user.name.extra'));
    }

    public function test_has_nested_returns_true() : void
    {
        $data = ['user' => ['profile' => ['age' => 30]]];

        $this->assertTrue($this->reader->hasNested($data, 'user.profile.age'));
    }

    public function test_has_nested_returns_false() : void
    {
        $data = ['user' => ['name' => 'John']];

        $this->assertFalse($this->reader->hasNested($data, 'user.profile.age'));
    }

    public function test_has_nested_returns_false_for_non_array_intermediate() : void
    {
        $data = ['user' => ['name' => 'John']];

        $this->assertFalse($this->reader->hasNested($data, 'user.name.extra'));
    }

    public function test_require_nested_returns_value() : void
    {
        $data = ['user' => ['name' => 'John']];

        $this->assertSame('John', $this->reader->requireNested($data, 'user.name'));
    }

    public function test_require_nested_throws_for_missing() : void
    {
        $data = ['user' => ['name' => 'John']];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Required nested path 'user.profile.age' is missing");

        $this->reader->requireNested($data, 'user.profile.age');
    }

    public function test_dot_flattens_nested_array() : void
    {
        $data = [
            'user' => [
                'name'    => 'John',
                'address' => ['city' => 'NYC'],
            ],
        ];

        $result = $this->reader->dot($data);

        $this->assertSame([
                              'user.name' => 'John',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           'user.address.city' => 'NYC',
                          ], $result);
    }

    // -- Dot / Undot Tests --

    public function test_dot_with_prefix() : void
    {
        $data = ['name' => 'John'];

        $result = $this->reader->dot($data, prefix: 'data');

        $this->assertSame(['data.name' => 'John'], $result);
    }

    public function test_dot_handles_empty_array() : void
    {
        $data = ['user' => []];

        $result = $this->reader->dot($data);

        // Empty arrays are kept as values (not recursed into)
        $this->assertSame(['user' => []], $result);
    }

    public function test_dot_handles_empty_array_value() : void
    {
        $data = ['user' => [], 'name' => 'John'];

        $result = $this->reader->dot($data);

        $this->assertSame(['user' => [], 'name' => 'John'], $result);
    }

    public function test_undot_expands_flat_array() : void
    {
        $data = [
            'user.name'         => 'John',
            'user.address.city' => 'NYC',
        ];

        $result = $this->reader->undot($data);

        $this->assertSame([
                              'user' => [
                                  'name' => 'John',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               'address' => ['city' => 'NYC'],
                              ],
                          ], $result);
    }

    public function test_undot_roundtrip() : void
    {
        $original = [
            'user' => [
                'name'    => 'John',
                'address' => ['city' => 'NYC'],
            ],
        ];

        $flattened = $this->reader->dot($original);
        $expanded  = $this->reader->undot($flattened);

        $this->assertSame($original, $expanded);
    }

    public function test_empty_data() : void
    {
        $data = [];

        $this->assertNull($this->reader->get($data, 'anything'));
        $this->assertFalse($this->reader->has($data, 'anything'));
        $this->assertSame([], $this->reader->only($data, ['a']));
        $this->assertSame([], $this->reader->except($data, ['a']));
    }

    // -- Edge Cases --

    public function test_nested_with_null_value() : void
    {
        $data = ['user' => ['name' => null]];

        $this->assertNull($this->reader->getNested($data, 'user.name'));
        $this->assertTrue($this->reader->hasNested($data, 'user.name'));
    }

    public function test_single_level_path() : void
    {
        $data = ['name' => 'John'];

        $this->assertSame('John', $this->reader->getNested($data, 'name'));
        $this->assertTrue($this->reader->hasNested($data, 'name'));
    }

    public function test_get_nested_with_default_for_empty_path() : void
    {
        $data = ['name' => 'John'];

        $this->assertSame('John', $this->reader->getNested($data, 'name'));
    }

    protected function setUp() : void
    {
        $this->reader = new ArrayReader();
    }
}
