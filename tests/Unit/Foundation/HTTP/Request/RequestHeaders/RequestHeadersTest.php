<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Foundation\HTTP\Request\RequestHeaders;

use Avax\HTTP\Request\RequestHeaders\RequestHeaders;
use PHPUnit\Framework\TestCase;

/**
 * TDD Tests for RequestHeaders according to the DeepRefactor plan.
 */
class RequestHeadersTest extends TestCase
{
    public function test_stores_single_header_as_list_of_strings() : void
    {
        $headers = new RequestHeaders(['X-Custom' => 'Alpha']);

        $this->assertSame(expected: ['Alpha'], actual: $headers->read('X-Custom'));
    }

    public function test_stores_multiple_header_values_as_list_of_strings() : void
    {
        $headers = new RequestHeaders(['X-Custom' => ['Alpha', 'Beta']]);

        $this->assertSame(expected: ['Alpha', 'Beta'], actual: $headers->read('X-Custom'));
    }

    public function test_reads_header_case_insensitively() : void
    {
        $headers = new RequestHeaders(['x-cUsTom' => 'Alpha']);

        $this->assertTrue(condition: $headers->has('X-Custom'));
        $this->assertSame(expected: ['Alpha'], actual: $headers->read('X-CUSTOM'));
    }

    public function test_returns_empty_list_when_header_missing() : void
    {
        $headers = new RequestHeaders([]);

        $this->assertFalse(condition: $headers->has('X-Missing'));
        $this->assertSame(expected: [], actual: $headers->read('X-Missing'));
    }

    public function test_reads_header_line_as_comma_separated_values() : void
    {
        $headers = new RequestHeaders(['X-Custom' => ['Alpha', 'Beta']]);

        $this->assertSame(expected: 'Alpha, Beta', actual: $headers->readLine('X-Custom'));
    }

    public function test_replaces_header_values_immutably() : void
    {
        $headers    = new RequestHeaders(['X-Custom' => 'Alpha']);
        $newHeaders = $headers->put('X-Custom', 'Beta');

        $this->assertNotSame(expected: $headers, actual: $newHeaders);
        $this->assertSame(expected: ['Alpha'], actual: $headers->read('X-Custom'));
        $this->assertSame(expected: ['Beta'], actual: $newHeaders->read('X-Custom'));
    }

    public function test_appends_header_values_immutably() : void
    {
        $headers    = new RequestHeaders(['X-Custom' => 'Alpha']);
        $newHeaders = $headers->append('X-Custom', 'Beta');

        $this->assertNotSame(expected: $headers, actual: $newHeaders);
        $this->assertSame(expected: ['Alpha'], actual: $headers->read('X-Custom'));
        $this->assertSame(expected: ['Alpha', 'Beta'], actual: $newHeaders->read('X-Custom'));
    }

    public function test_drops_header_immutably() : void
    {
        $headers    = new RequestHeaders(['X-Custom' => 'Alpha']);
        $newHeaders = $headers->drop('X-Custom');

        $this->assertTrue(condition: $headers->has('X-Custom'));
        $this->assertFalse(condition: $newHeaders->has('X-Custom'));
    }

    public function test_returns_all_headers_in_normalized_public_shape() : void
    {
        $headers = new RequestHeaders([
                                          'x-custom' => 'Alpha',
                                          'host'     => 'example.com'
                                      ]);

        $all = $headers->all();

        $this->assertArrayHasKey(key: 'x-custom', array: $all);
        $this->assertArrayHasKey(key: 'host', array: $all);
        $this->assertSame(expected: ['Alpha'], actual: $all['x-custom']);
        $this->assertSame(expected: ['example.com'], actual: $all['host']);
    }
}
