<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestedInputs\RequestedInputs;

class RequestedInputsTest extends TestCase
{
    public function test_get_prefers_body_over_query_for_same_key()
    {
        $inputs = new RequestedInputs(
            queryParams: ['foo' => 'query'],
            parsedBody: ['foo' => 'body']
        );
        
        $this->assertEquals(expected: 'body', actual: $inputs->get(key: 'foo'));
    }

    public function test_all_prefers_body_over_query_for_same_key()
    {
        $inputs = new RequestedInputs(
            queryParams: ['foo' => 'query', 'bar' => 'baz'],
            parsedBody: ['foo' => 'body']
        );
        
        $all = $inputs->all();
        $this->assertEquals(expected: 'body', actual: $all['foo']);
        $this->assertEquals(expected: 'baz', actual: $all['bar']);
    }

    public function test_bool_accepts_true_false_zero_one_strings()
    {
        $inputs = new RequestedInputs(parsedBody: [
            't' => 'true',
            'f' => 'false',
            '1' => '1',
            '0' => '0',
            'y' => 'yes',
            'n' => 'no'
        ]);
        
        $this->assertTrue(condition: $inputs->bool(key: 't'));
        $this->assertFalse(condition: $inputs->bool(key: 'f'));
        $this->assertTrue(condition: $inputs->bool(key: '1'));
        $this->assertFalse(condition: $inputs->bool(key: '0'));
        $this->assertTrue(condition: $inputs->bool(key: 'y'));
        $this->assertFalse(condition: $inputs->bool(key: 'n'));
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

    public function test_text_casts_scalar_to_string()
    {
        $inputs = new RequestedInputs(parsedBody: ['foo' => 123]);
        $this->assertEquals(expected: '123', actual: $inputs->text(key: 'foo'));
    }

    public function test_list_wraps_scalar_into_array()
    {
        $inputs = new RequestedInputs(parsedBody: ['foo' => 'bar']);
        $this->assertEquals(expected: ['bar'], actual: $inputs->list(key: 'foo'));
    }

    public function test_list_returns_array_as_is()
    {
        $inputs = new RequestedInputs(parsedBody: ['foo' => ['a', 'b']]);
        $this->assertEquals(expected: ['a', 'b'], actual: $inputs->list(key: 'foo'));
    }
}
