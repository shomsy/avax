<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Unit;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\RequestedInputs;
use PHPUnit\Framework\TestCase;

class RequestedInputsTest extends TestCase
{
    public function test_get_prefers_body_over_query_for_same_key()
    {
        $inputs = new RequestedInputs(
            queryParams: ['foo' => 'query'],
            parsedBody : ['foo' => 'body']
        );

        $this->assertEquals(expected: 'body', actual: $inputs->get(key: 'foo'));
    }

    public function test_all_prefers_body_over_query_for_same_key()
    {
        $inputs = new RequestedInputs(
            queryParams: ['foo' => 'query', 'bar' => 'baz'],
            parsedBody : ['foo' => 'body']
        );

        $all = $inputs->all();
        $this->assertEquals(expected: 'body', actual: $all['foo']);
        $this->assertEquals(expected: 'baz', actual: $all['bar']);
    }

    public function test_bool_accepts_valid_boolean_strings()
    {
        $inputs = new RequestedInputs(parsedBody: [
                                                      't'   => 'true',
                                                      'f'   => 'false',
                                                      '1'   => '1',
                                                      '0'   => '0',
                                                      'on'  => 'on',
                                                      'off' => 'off',
                                                      'yes' => 'yes',
                                                      'no'  => 'no',
                                                  ]);

        $this->assertTrue(condition: $inputs->bool(key: 't'));
        $this->assertFalse(condition: $inputs->bool(key: 'f'));
        $this->assertTrue(condition: $inputs->bool(key: '1'));
        $this->assertFalse(condition: $inputs->bool(key: '0'));
        $this->assertTrue(condition: $inputs->bool(key: 'on'));
        $this->assertFalse(condition: $inputs->bool(key: 'off'));
        // yes/no are NOT standard FILTER_VALIDATE_BOOLEAN values
        $this->assertFalse(condition: $inputs->bool(key: 'yes'));
        $this->assertTrue(condition: $inputs->bool(key: 'no', default: true));
    }

    public function test_bool_returns_default_for_invalid_value()
    {
        $inputs = new RequestedInputs(parsedBody: ['foo' => 'not-a-bool']);
        $this->assertTrue(condition: $inputs->bool(key: 'foo', default: true));
        $this->assertFalse(condition: $inputs->bool(key: 'foo', default: false));
    }

    public function test_int_accepts_valid_integer_string()
    {
        $inputs = new RequestedInputs(parsedBody: ['foo' => '123']);
        $this->assertEquals(expected: 123, actual: $inputs->int(key: 'foo'));
    }

    public function test_int_returns_default_for_invalid_string()
    {
        $inputs = new RequestedInputs(parsedBody: ['foo' => 'abc']);
        $this->assertEquals(expected: 5, actual: $inputs->int(key: 'foo', default: 5));
    }

    public function test_float_accepts_valid_float_string()
    {
        $inputs = new RequestedInputs(parsedBody: ['foo' => '1.23']);
        $this->assertEquals(expected: 1.23, actual: $inputs->float(key: 'foo'));
    }

    public function test_float_returns_default_for_invalid_value()
    {
        $inputs = new RequestedInputs(parsedBody: ['foo' => 'abc']);
        $this->assertEquals(expected: 1.1, actual: $inputs->float(key: 'foo', default: 1.1));
    }

    public function test_string_casts_scalar_to_string()
    {
        $inputs = new RequestedInputs(parsedBody: ['foo' => 123]);
        $this->assertEquals(expected: '123', actual: $inputs->string(key: 'foo'));
    }

    public function test_array_returns_array_as_is()
    {
        $inputs = new RequestedInputs(parsedBody: ['foo' => ['a', 'b']]);
        $this->assertEquals(expected: ['a', 'b'], actual: $inputs->array(key: 'foo'));
    }

    public function test_array_returns_default_for_scalar()
    {
        $inputs = new RequestedInputs(parsedBody: ['foo' => 'bar']);
        $this->assertEquals(expected: [], actual: $inputs->array(key: 'foo'));
    }

    public function test_has_uses_array_key_exists_not_isset()
    {
        $inputs = new RequestedInputs(parsedBody: ['foo' => null]);
        $this->assertTrue(condition: $inputs->has(key: 'foo'));
        $this->assertFalse(condition: $inputs->hasNonNull(key: 'foo'));
    }

    public function test_from_slices_static_factory()
    {
        $inputs = RequestedInputs::fromSlices(
            queryParams: ['page' => '1'],
            parsedBody : ['email' => 'test@example.com'],
        );

        $this->assertEquals(expected: 'test@example.com', actual: $inputs->get(key: 'email'));
        $this->assertEquals(expected: '1', actual: $inputs->get(key: 'page'));
    }

    public function test_sanitized_html_prevents_xss()
    {
        $inputs = new RequestedInputs(parsedBody: ['content' => '<script>alert("xss")</script>']);
        $safe   = $inputs->sanitizedHtml(key: 'content');

        $this->assertStringNotContainsString(needle: '<script>', haystack: $safe);
        $this->assertStringContainsString(needle: '&lt;script&gt;', haystack: $safe);
    }

    public function test_value_returns_inputvalue_with_source()
    {
        $inputs = new RequestedInputs(
            queryParams: ['from' => 'query'],
            parsedBody : ['from' => 'body'],
        );

        $value = $inputs->value(key: 'from');
        $this->assertTrue(condition: $value->isFromBody());
        $this->assertFalse(condition: $value->isFromQuery());
        $this->assertEquals(expected: 'body', actual: $value->value);
    }

    public function test_query_and_body_accessors()
    {
        $inputs = new RequestedInputs(
            queryParams: ['q' => 'search'],
            parsedBody : ['b' => 'data'],
        );

        $this->assertEquals(expected: 'search', actual: $inputs->fromQuery(key: 'q'));
        $this->assertEquals(expected: 'data', actual: $inputs->fromBody(key: 'b'));
    }
}
