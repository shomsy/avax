<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Foundation\HTTP\Request\RequestHeaders;

use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestHeaders\RequestHeaders;
use Avax\Tests\TestCase;

/**
 * TDD Tests for RequestHeaders according to the DeepRefactor plan.
 */
class RequestHeadersTest extends TestCase
{
    public function test_stores_single_header_as_list_of_strings() : void
    {
        $headers = new RequestHeaders(headers: ['X-Custom' => 'Alpha']);

        $this->assertSame(expected: ['Alpha'], actual: $headers->get(name: 'X-Custom'));
    }

    public function test_stores_multiple_header_values_as_list_of_strings() : void
    {
        $headers = new RequestHeaders(headers: ['X-Custom' => ['Alpha', 'Beta']]);

        $this->assertSame(expected: ['Alpha', 'Beta'], actual: $headers->get(name: 'X-Custom'));
    }

    public function test_reads_header_case_insensitively() : void
    {
        $headers = new RequestHeaders(headers: ['x-cUsTom' => 'Alpha']);

        $this->assertTrue(condition: $headers->has(name: 'X-Custom'));
        $this->assertSame(expected: ['Alpha'], actual: $headers->get(name: 'X-CUSTOM'));
    }

    public function test_returns_empty_list_when_header_missing() : void
    {
        $headers = new RequestHeaders(headers: []);

        $this->assertFalse(condition: $headers->has(name: 'X-Missing'));
        $this->assertSame(expected: [], actual: $headers->get(name: 'X-Missing'));
    }

    public function test_reads_header_line_as_comma_separated_values() : void
    {
        $headers = new RequestHeaders(headers: ['X-Custom' => ['Alpha', 'Beta']]);

        $this->assertSame(expected: 'Alpha, Beta', actual: $headers->getLine(name: 'X-Custom'));
    }

    public function test_replaces_header_values_immutably() : void
    {
        $headers = new RequestHeaders(headers: ['X-Custom' => 'Alpha']);
        $newHeaders = $headers->put(name: 'X-Custom', value: 'Beta');

        $this->assertNotSame(expected: $headers, actual: $newHeaders);
        $this->assertSame(expected: ['Alpha'], actual: $headers->get(name: 'X-Custom'));
        $this->assertSame(expected: ['Beta'], actual: $newHeaders->get(name: 'X-Custom'));
    }

    public function test_appends_header_values_immutably() : void
    {
        $headers = new RequestHeaders(headers: ['X-Custom' => 'Alpha']);
        $newHeaders = $headers->append(name: 'X-Custom', value: 'Beta');

        $this->assertNotSame(expected: $headers, actual: $newHeaders);
        $this->assertSame(expected: ['Alpha'], actual: $headers->get(name: 'X-Custom'));
        $this->assertSame(expected: ['Alpha', 'Beta'], actual: $newHeaders->get(name: 'X-Custom'));
    }

    public function test_drops_header_immutably() : void
    {
        $headers = new RequestHeaders(headers: ['X-Custom' => 'Alpha']);
        $newHeaders = $headers->drop(name: 'X-Custom');

        $this->assertTrue(condition: $headers->has(name: 'X-Custom'));
        $this->assertFalse(condition: $newHeaders->has(name: 'X-Custom'));
    }

    public function test_returns_all_headers_in_normalized_public_shape() : void
    {
        $headers = new RequestHeaders(headers: [
            'x-custom' => 'Alpha',
            'host' => 'example.com',
        ]);

        $all = $headers->all();

        $this->assertArrayHasKey(key: 'x-custom', array: $all);
        $this->assertArrayHasKey(key: 'host', array: $all);
        $this->assertSame(expected: ['Alpha'], actual: $all['x-custom']);
        $this->assertSame(expected: ['example.com'], actual: $all['host']);
    }
}
