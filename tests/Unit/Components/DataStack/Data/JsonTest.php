<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Data;

use Avax\Components\DataStack\Data\System\Foundation\Failure\InvalidJson;
use Avax\Components\DataStack\Data\System\Foundation\Failure\MutationException;
use Avax\Components\DataStack\Data\System\Capabilities\Forms\JsonForm\Json;
use PHPUnit\Framework\TestCase;

final class JsonTest extends TestCase
{
    // -- Factories ----------------------------------------------------------------

    public function test_decode_valid_json() : void
    {
        $json = Json::decode(json: '{"name":"AvaX","version":1}');

        self::assertSame(['name' => 'AvaX', 'version' => 1], $json->all());
    }

    public function test_decode_invalid_json_throws() : void
    {
        $this->expectException(InvalidJson::class);
        $this->expectExceptionMessage('The provided string is not valid JSON.');
        Json::decode(json: '{invalid}');
    }

    public function test_decode_scalar_wraps_in_array() : void
    {
        $json = Json::decode(json: '"hello"');

        self::assertSame(['hello'], $json->all());
    }

    public function test_make() : void
    {
        $json = Json::make(['name' => 'AvaX']);

        self::assertSame(['name' => 'AvaX'], $json->all());
    }

    public function test_wrap() : void
    {
        self::assertSame(['hello'], Json::wrap('hello')->all());
        self::assertSame(['a' => 1], Json::wrap(['a' => 1])->all());

        $original = Json::make([1, 2]);
        self::assertSame($original, Json::wrap($original));
    }

    // -- Access -------------------------------------------------------------------

    public function test_all() : void
    {
        $json = Json::make(['a' => 1]);

        self::assertSame(['a' => 1], $json->all());
    }

    public function test_get_existing() : void
    {
        $json = Json::decode(json: '{"name":"AvaX"}');

        self::assertSame('AvaX', $json->get(key: 'name'));
    }

    public function test_get_missing_returns_default() : void
    {
        $json = Json::decode(json: '{}');

        self::assertNull($json->get(key: 'missing'));
        self::assertSame('fallback', $json->get(key: 'missing', default: 'fallback'));
    }

    public function test_get_dot_path() : void
    {
        $json = Json::decode(json: '{"user":{"address":{"city":"NYC"}}}');

        self::assertSame('NYC', $json->get(key: 'user.address.city'));
    }

    public function test_has() : void
    {
        $json = Json::decode(json: '{"name":"AvaX"}');

        self::assertTrue($json->has(key: 'name'));
        self::assertFalse($json->has(key: 'version'));
    }

    public function test_has_dot_path() : void
    {
        $json = Json::decode(json: '{"user":{"name":"John"}}');

        self::assertTrue($json->has(key: 'user.name'));
        self::assertFalse($json->has(key: 'user.age'));
    }

    public function test_path_json_pointer() : void
    {
        $json = Json::decode(json: '{"user":{"name":"John","address":{"city":"NYC"}}}');

        self::assertSame('John', $json->path(pointer: '/user/name'));
        self::assertSame('NYC', $json->path(pointer: '/user/address/city'));
    }

    public function test_path_root_pointer() : void
    {
        $json = Json::decode(json: '{"a":1}');

        self::assertSame(['a' => 1], $json->path(pointer: '/'));
    }

    // -- Mutation -----------------------------------------------------------------

    public function test_set() : void
    {
        $json = Json::decode(json: '{"name":"AvaX"}');
        $new  = $json->set(key: 'version', value: 1);

        self::assertSame(1, $new->get(key: 'version'));
        self::assertNull($json->get(key: 'version')); // original unchanged
    }

    public function test_set_dot_path() : void
    {
        $json = Json::decode(json: '{"user":{}}');
        $new  = $json->set(key: 'user.name', value: 'John');

        self::assertSame('John', $new->get(key: 'user.name'));
    }

    public function test_forget() : void
    {
        $json = Json::decode(json: '{"name":"AvaX","version":1}');
        $new  = $json->forget(key: 'version');

        self::assertFalse($new->has(key: 'version'));
    }

    public function test_forget_dot_path() : void
    {
        $json = Json::decode(json: '{"user":{"name":"John","age":30}}');
        $new  = $json->forget(key: 'user.age');

        self::assertFalse($new->has(key: 'user.age'));
        self::assertTrue($new->has(key: 'user.name'));
    }

    public function test_merge() : void
    {
        $json = Json::decode(json: '{"name":"AvaX"}');
        $new  = $json->merge(data: ['version' => 1]);

        self::assertSame(1, $new->get(key: 'version'));
    }

    public function test_locked_set_throws() : void
    {
        $json = Json::make(['a' => 1])->lock();

        $this->expectException(MutationException::class);
        (void) $json->set(key: 'b', value: 2);
    }

    public function test_locked_forget_throws() : void
    {
        $json = Json::make(['a' => 1])->lock();

        $this->expectException(MutationException::class);
        (void) $json->forget(key: 'a');
    }

    // -- Selection ----------------------------------------------------------------

    public function test_only() : void
    {
        $json = Json::decode(json: '{"name":"AvaX","version":1,"author":"team"}');

        self::assertSame(
            ['name' => 'AvaX', 'version' => 1],
            $json->only(keys: ['name', 'version'])->all(),
        );
    }

    public function test_except() : void
    {
        $json = Json::decode(json: '{"name":"AvaX","version":1,"author":"team"}');

        self::assertSame(
            ['name' => 'AvaX', 'version' => 1],
            $json->except(keys: ['author'])->all(),
        );
    }

    // -- Info ---------------------------------------------------------------------

    public function test_keys() : void
    {
        $json = Json::decode(json: '{"name":"AvaX","version":1}');

        self::assertSame(['name', 'version'], $json->keys());
    }

    public function test_count() : void
    {
        $json = Json::decode(json: '{"a":1,"b":2,"c":3}');

        self::assertSame(3, $json->count());
    }

    public function test_is_empty() : void
    {
        self::assertTrue(Json::decode(json: '{}')->isEmpty());
        self::assertFalse(Json::decode(json: '{"a":1}')->isEmpty());
    }

    public function test_is_not_empty() : void
    {
        self::assertFalse(Json::decode(json: '{}')->isNotEmpty());
        self::assertTrue(Json::decode(json: '{"a":1}')->isNotEmpty());
    }

    // -- Conversion ---------------------------------------------------------------

    public function test_to_array() : void
    {
        $json = Json::decode(json: '{"name":"AvaX"}');

        self::assertSame(['name' => 'AvaX'], $json->toArray());
    }

    public function test_to_json() : void
    {
        $json = Json::decode(json: '{"name":"AvaX"}');

        self::assertSame('{"name":"AvaX"}', $json->toJson());
    }

    public function test_pretty() : void
    {
        $json   = Json::decode(json: '{"name":"AvaX"}');
        $pretty = $json->pretty();

        self::assertStringContainsString("\n", $pretty);
        self::assertStringContainsString('"name"', $pretty);
    }

    public function test_encode_alias() : void
    {
        $json = Json::decode(json: '{"name":"AvaX"}');

        self::assertSame('{"name":"AvaX"}', $json->encode());
    }

    // -- Validation ---------------------------------------------------------------

    public function test_validate_passes() : void
    {
        $json   = Json::decode(json: '{"name":"AvaX","version":1}');
        $result = $json->validate(schema: [
                                              'name'    => 'string',
                                              'version' => 'int',
                                          ]);

        self::assertTrue($result['valid']);
        self::assertSame([], $result['errors']);
    }

    public function test_validate_fails_missing_key() : void
    {
        $json   = Json::decode(json: '{"name":"AvaX"}');
        $result = $json->validate(schema: [
                                              'name'    => 'string',
                                              'version' => 'int',
                                          ]);

        self::assertFalse($result['valid']);
        self::assertStringContainsString('Missing required key', $result['errors'][0]);
    }

    public function test_validate_fails_wrong_type() : void
    {
        $json   = Json::decode(json: '{"name":123}');
        $result = $json->validate(schema: [
                                              'name' => 'string',
                                          ]);

        self::assertFalse($result['valid']);
        self::assertStringContainsString('expected string', $result['errors'][0]);
    }

    public function test_validate_multiple_types() : void
    {
        $json   = Json::decode(json: '{"value":3.14}');
        $result = $json->validate(schema: [
                                              'value' => ['int', 'float'],
                                          ]);

        self::assertTrue($result['valid']);
    }

    public function test_validate_dot_path_key() : void
    {
        $json   = Json::decode(json: '{"user":{"name":"John"}}');
        $result = $json->validate(schema: [
                                              'user.name' => 'string',
                                          ]);

        self::assertTrue($result['valid']);
    }

    public function test_validate_nullable() : void
    {
        $json   = Json::decode(json: '{"name":"AvaX","version":null}');
        $result = $json->validate(schema: [
                                              'name'    => 'string',
                                              'version' => ['int', 'null'],
                                          ]);

        self::assertTrue($result['valid']);
    }

    // -- Immutability -------------------------------------------------------------

    public function test_lock() : void
    {
        $json = Json::make(['a' => 1]);

        self::assertFalse($json->isLocked());

        $locked = $json->lock();

        self::assertTrue($locked->isLocked());
    }
}
