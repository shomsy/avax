<?php

declare(strict_types=1);

use Avax\HTTP\Enums\HttpMethod;
use PHPUnit\Framework\TestCase;

/**
 * Tests for HTTP method completeness - RFC 9110 coverage.
 *
 * Ensures TRACE and CONNECT methods are properly supported.
 */
class HttpMethodCompletenessTest extends TestCase
{
    /**
     * @test
     */
    public function supports_all_standard_http_methods() : void
    {
        $expectedMethods = [
            'GET', 'POST', 'PUT', 'DELETE', 'HEAD',
            'CONNECT', 'OPTIONS', 'TRACE', 'PATCH',
        ];

        $actualMethods = HttpMethod::getSupportedMethods();

        $this->assertEqualsCanonicalizing(expected: $expectedMethods, actual: $actualMethods);
    }

    /**
     * @test
     */
    public function trace_method_is_supported() : void
    {
        $this->assertTrue(condition: HttpMethod::isSupported(method: 'TRACE'));
        $this->assertContains(needle: 'TRACE', haystack: HttpMethod::getSupportedMethods());
    }

    /**
     * @test
     */
    public function connect_method_is_supported() : void
    {
        $this->assertTrue(condition: HttpMethod::isSupported(method: 'CONNECT'));
        $this->assertContains(needle: 'CONNECT', haystack: HttpMethod::getSupportedMethods());
    }

    /**
     * @test
     */
    public function all_enum_cases_are_supported() : void
    {
        foreach (HttpMethod::cases() as $method) {
            $this->assertTrue(
                condition: HttpMethod::isSupported(method: $method->value),
                message  : "HTTP method {$method->value} should be supported"
            );
        }
    }

    /**
     * @test
     */
    public function unsupported_methods_are_rejected() : void
    {
        $unsupportedMethods = [
            'CUSTOM',
            'SPECIAL',
            'NONSTANDARD',
            'LINK',
            'UNLINK',
        ];

        foreach ($unsupportedMethods as $method) {
            $this->assertFalse(
                condition: HttpMethod::isSupported(method: $method),
                message  : "HTTP method {$method} should not be supported"
            );
        }
    }

    /**
     * @test
     */
    public function enum_cases_match_supported_methods() : void
    {
        $enumValues = array_map(
            static fn($case) => $case->value,
            HttpMethod::cases()
        );

        $supportedMethods = HttpMethod::getSupportedMethods();

        $this->assertEqualsCanonicalizing(expected: $enumValues, actual: $supportedMethods);
    }

    /**
     * @test
     */
    public function method_case_sensitivity() : void
    {
        // Methods should be case-sensitive (RFC 9110)
        $this->assertTrue(condition: HttpMethod::isSupported(method: 'GET'));
        $this->assertFalse(condition: HttpMethod::isSupported(method: 'get'));
        $this->assertFalse(condition: HttpMethod::isSupported(method: 'Get'));
    }

    /**
     * @test
     */
    public function rfc9110_method_coverage() : void
    {
        // Core HTTP methods from RFC 9110
        $rfc9110Methods = [
            'GET', 'HEAD', 'POST', 'PUT', 'DELETE',
            'CONNECT', 'OPTIONS', 'TRACE',
        ];

        foreach ($rfc9110Methods as $method) {
            $this->assertTrue(
                condition: HttpMethod::isSupported(method: $method),
                message  : "RFC 9110 method {$method} should be supported"
            );
        }
    }

    /**
     * @test
     */
    public function patch_method_support() : void
    {
        // PATCH is widely supported even though not in core RFC 9110
        $this->assertTrue(condition: HttpMethod::isSupported(method: 'PATCH'));
        $this->assertContains(needle: 'PATCH', haystack: HttpMethod::getSupportedMethods());
    }

    /**
     * @test
     */
    public function enum_case_values_are_correct() : void
    {
        $expectedCases = [
            'GET'     => HttpMethod::GET,
            'POST'    => HttpMethod::POST,
            'PUT'     => HttpMethod::PUT,
            'DELETE'  => HttpMethod::DELETE,
            'HEAD'    => HttpMethod::HEAD,
            'CONNECT' => HttpMethod::CONNECT,
            'OPTIONS' => HttpMethod::OPTIONS,
            'TRACE'   => HttpMethod::TRACE,
            'PATCH'   => HttpMethod::PATCH,
        ];

        foreach ($expectedCases as $value => $case) {
            $this->assertEquals(expected: $value, actual: $case->value);
        }
    }
}
