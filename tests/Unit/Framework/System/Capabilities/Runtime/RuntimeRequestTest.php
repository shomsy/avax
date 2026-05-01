<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Capabilities\Runtime;

use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;
use Avax\Tests\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RuntimeRequest::class)]
final class RuntimeRequestTest extends TestCase
{
    #[Test]
    public function it_normalizes_method_and_uri(): void
    {
        $request = new RuntimeRequest(method: '  get  ', uri: '  /test  ');

        self::assertSame('GET', $request->method());
        self::assertSame('/test', $request->uri());
    }

    #[Test]
    public function it_stores_headers_and_body(): void
    {
        $request = new RuntimeRequest(
            method: 'POST',
            uri: '/api/test',
            headers: ['Content-Type' => ['application/json']],
            body: '{"key":"value"}',
        );

        self::assertSame('POST', $request->method());
        self::assertSame('/api/test', $request->uri());
        self::assertSame(['Content-Type' => ['application/json']], $request->headers());
        self::assertSame('{"key":"value"}', $request->body());
    }

    #[Test]
    public function it_stores_attributes(): void
    {
        $request = new RuntimeRequest(
            method: 'GET',
            uri: '/test',
            attributes: ['user_id' => 123, 'role' => 'admin'],
        );

        self::assertSame(['user_id' => 123, 'role' => 'admin'], $request->attributes());
    }

    #[Test]
    public function it_returns_empty_headers_by_default(): void
    {
        $request = new RuntimeRequest(method: 'GET', uri: '/test');

        self::assertSame([], $request->headers());
    }

    #[Test]
    public function it_returns_null_body_by_default(): void
    {
        $request = new RuntimeRequest(method: 'GET', uri: '/test');

        self::assertNull($request->body());
    }

    #[Test]
    public function it_returns_empty_attributes_by_default(): void
    {
        $request = new RuntimeRequest(method: 'GET', uri: '/test');

        self::assertSame([], $request->attributes());
    }

    #[Test]
    public function it_throws_when_method_is_empty(): void
    {
        $this->expectException(FrameworkMisconfigured::class);
        $this->expectExceptionMessage('Runtime request method cannot be empty.');

        new RuntimeRequest(method: '   ', uri: '/test');
    }

    #[Test]
    public function it_throws_when_uri_is_empty(): void
    {
        $this->expectException(FrameworkMisconfigured::class);
        $this->expectExceptionMessage('Runtime request uri cannot be empty.');

        new RuntimeRequest(method: 'GET', uri: '   ');
    }

    #[Test]
    public function it_handles_multiple_header_values(): void
    {
        $request = new RuntimeRequest(
            method: 'GET',
            uri: '/test',
            headers: [
                        'Accept'        => ['text/html', 'application/json'],
                        'Cache-Control' => ['no-cache', 'no-store'],
            ],
        );

        self::assertSame(['text/html', 'application/json'], $request->headers()['Accept']);
        self::assertSame(['no-cache', 'no-store'], $request->headers()['Cache-Control']);
    }
}
