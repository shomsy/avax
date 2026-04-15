<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Unit\IncomingHttp\ReadRequest\RequestHeaders;

use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestHeaders\RequestHeaders;
use PHPUnit\Framework\TestCase;

/**
 * TDD Tests for RequestHeaders according to Sprint 3 of the Protected Rewrite Plan.
 */
class RequestHeadersTest extends TestCase
{
    public function test_it_creates_empty_headers_if_none_provided() : void
    {
        $headers = new RequestHeaders(headers: []);
        $this->assertSame(expected: [], actual: $headers->all());
    }

    public function test_it_normalizes_single_header_to_list() : void
    {
        $headers = new RequestHeaders(headers: [
                                                   'X-Custom' => 'Alpha'
                                               ]);

        $this->assertSame(expected: ['Alpha'], actual: $headers->get(name: 'X-Custom'));
    }

    public function test_it_handles_array_values_correctly() : void
    {
        $headers = new RequestHeaders(headers: [
                                                   'X-Multiple' => ['Alpha', 'Beta']
                                               ]);

        $this->assertSame(expected: ['Alpha', 'Beta'], actual: $headers->get(name: 'X-Multiple'));
    }

    public function test_it_performs_case_insensitive_lookup() : void
    {
        $headers = new RequestHeaders(headers: [
                                                   'x-CuStOm' => 'Alpha'
                                               ]);

        $this->assertTrue(condition: $headers->has(name: 'X-CUSTOM'));
        $this->assertSame(expected: ['Alpha'], actual: $headers->get(name: 'X-Custom'));
        $this->assertSame(expected: ['Alpha'], actual: $headers->get(name: 'x-custom'));
    }

    public function test_get_returns_empty_array_for_missing() : void
    {
        $headers = new RequestHeaders(headers: []);
        $this->assertSame(expected: [], actual: $headers->get(name: 'Missing'));
    }

    public function test_it_can_return_comma_separated_line() : void
    {
        $headers = new RequestHeaders(headers: [
                                                   'Accept' => ['text/html', 'application/xhtml+xml']
                                               ]);

        $this->assertSame(expected: 'text/html, application/xhtml+xml', actual: $headers->getLine(name: 'Accept'));
        $this->assertSame(expected: '', actual: $headers->getLine(name: 'Missing'));
    }

    public function test_it_can_replace_values() : void
    {
        $headers = new RequestHeaders(headers: [
                                                   'Content-Type' => 'text/html'
                                               ]);

        $newHeaders = $headers->put(name: 'Content-Type', value: 'application/json');

        $this->assertNotSame(expected: $headers, actual: $newHeaders);
        $this->assertSame(expected: ['text/html'], actual: $headers->get(name: 'Content-Type'));
        $this->assertSame(expected: ['application/json'], actual: $newHeaders->get(name: 'Content-Type'));
    }

    public function test_it_can_append_values() : void
    {
        $headers = new RequestHeaders(headers: [
                                                   'X-Trace' => '123'
                                               ]);

        $newHeaders = $headers->append(name: 'X-Trace', value: '456');

        $this->assertSame(expected: ['123', '456'], actual: $newHeaders->get(name: 'X-Trace'));
    }

    public function test_it_can_drop_headers() : void
    {
        $headers = new RequestHeaders(headers: [
                                                   'Authorization' => 'Bearer 123'
                                               ]);

        $newHeaders = $headers->drop(name: 'Authorization');

        $this->assertTrue(condition: $headers->has(name: 'Authorization'));
        $this->assertFalse(condition: $newHeaders->has(name: 'Authorization'));
    }

    public function test_all_returns_original_keys_with_normalized_values() : void
    {
        $headers = new RequestHeaders(headers: [
                                                   'CONtent-Type' => 'text/plain',
                                                   'x-custom'     => ['alpha', 'beta']
                                               ]);

        $expected = [
            'CONtent-Type' => ['text/plain'],
            'x-custom'     => ['alpha', 'beta']
        ];

        $this->assertSame(expected: $expected, actual: $headers->all());
    }
}
