<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\tests\Unit;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestHeaders\RequestHeaders;
use PHPUnit\Framework\TestCase;

class RequestHeadersTest extends TestCase
{
    public function test_headers_store_single_value()
    {
        $headers = new RequestHeaders(headersInput: [
                                                        'X-Custom' => 'value',
                                                    ]);

        $this->assertTrue(condition: $headers->has(name: 'X-Custom'));
        $this->assertSame(expected: ['value'], actual: $headers->get(name: 'X-Custom'));
    }

    public function test_headers_store_multiple_values()
    {
        $headers = new RequestHeaders(headersInput: [
                                                        'X-Custom' => 'value1,value2',
                                                    ]);

        $this->assertSame(expected: ['value1', 'value2'], actual: $headers->get(name: 'X-Custom'));
    }

    public function test_header_lookup_case_insensitive()
    {
        $headers = new RequestHeaders(headersInput: [
                                                        'X-Custom' => 'value',
                                                    ]);

        $this->assertTrue(condition: $headers->has(name: 'x-custom'));
        $this->assertTrue(condition: $headers->has(name: 'X-CUSTOM'));
        $this->assertSame(expected: ['value'], actual: $headers->get(name: 'X-CUSTOM'));
    }

    public function test_header_get_line_returns_single_line()
    {
        $headers = new RequestHeaders(headersInput: [
                                                        'Content-Type' => 'application/json',
                                                    ]);

        $this->assertEquals(expected: 'application/json', actual: $headers->getLine(name: 'Content-Type'));
    }

    public function test_header_get_line_returns_empty_for_missing()
    {
        $headers = new RequestHeaders;

        $this->assertEquals(expected: '', actual: $headers->getLine(name: 'X-Missing'));
    }

    public function test_header_append_adds_value()
    {
        $headers = new RequestHeaders(headersInput: [
                                                        'X-Custom' => 'value1',
                                                    ]);

        $newHeaders = $headers->append(name: 'X-Custom', value: 'value2');

        $this->assertSame(expected: ['value1', 'value2'], actual: $newHeaders->get(name: 'X-Custom'));
    }

    public function test_header_put_replaces_existing()
    {
        $headers = new RequestHeaders(headersInput: [
                                                        'X-Custom' => 'old',
                                                    ]);

        $newHeaders = $headers->put(name: 'X-Custom', value: 'new');

        $this->assertSame(expected: ['new'], actual: $newHeaders->get(name: 'X-Custom'));
    }

    public function test_header_drop_removes_header()
    {
        $headers = new RequestHeaders(headersInput: [
                                                        'X-Custom' => 'value',
                                                    ]);

        $newHeaders = $headers->drop(name: 'X-Custom');

        $this->assertFalse(condition: $newHeaders->has(name: 'X-Custom'));
    }

    public function test_header_get_returns_empty_array_for_missing()
    {
        $headers = new RequestHeaders;

        $this->assertSame(expected: [], actual: $headers->get(name: 'X-Missing'));
    }

    public function test_header_all_returns_all_headers()
    {
        $headers = new RequestHeaders(headersInput: [
                                                        'X-Header1' => 'value1',
                                                        'X-Header2' => 'value2',
                                                    ]);

        $all = $headers->all();

        $this->assertArrayHasKey(key: 'X-Header1', array: $all);
        $this->assertArrayHasKey(key: 'X-Header2', array: $all);
    }

    public function test_header_append_new_header()
    {
        $headers = new RequestHeaders;

        $newHeaders = $headers->append(name: 'X-New', value: 'value');

        $this->assertTrue(condition: $newHeaders->has(name: 'X-New'));
        $this->assertSame(expected: ['value'], actual: $newHeaders->get(name: 'X-New'));
    }

    public function test_returns_original_case()
    {
        $headers = new RequestHeaders(headersInput: [
                                                        'X-Custom-Header' => 'value',
                                                    ]);

        $all = $headers->all();

        $this->assertArrayHasKey(key: 'X-Custom-Header', array: $all);
    }

    public function test_immutability_preserves_original()
    {
        $headers = new RequestHeaders(headersInput: [
                                                        'X-Custom' => 'value',
                                                    ]);

        $newHeaders = $headers->append(name: 'X-Custom', value: 'new');

        $this->assertSame(expected: ['value'], actual: $headers->get(name: 'X-Custom'));
        $this->assertSame(expected: ['value', 'new'], actual: $newHeaders->get(name: 'X-Custom'));
    }
}
