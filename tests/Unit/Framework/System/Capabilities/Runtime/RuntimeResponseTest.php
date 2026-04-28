<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Capabilities\Runtime;

use Avax\Framework\System\Capabilities\Runtime\RuntimeResponse;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;
use Avax\Tests\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RuntimeResponse::class)]
final class RuntimeResponseTest extends TestCase
{
    #[Test]
    public function it_creates_response_with_status_code_headers_and_body(): void
    {
        $response = new RuntimeResponse(
            statusCode: 200,
            headers: ['Content-Type' => ['application/json']],
            body: '{"status":"ok"}',
        );

        self::assertSame(200, $response->statusCode());
        self::assertSame(['Content-Type' => ['application/json']], $response->headers());
        self::assertSame('{"status":"ok"}', $response->body());
    }

    #[Test]
    public function it_returns_empty_headers_by_default(): void
    {
        $response = new RuntimeResponse(statusCode: 200);

        self::assertSame([], $response->headers());
    }

    #[Test]
    public function it_returns_empty_body_by_default(): void
    {
        $response = new RuntimeResponse(statusCode: 200);

        self::assertSame('', $response->body());
    }

    #[Test]
    public function it_throws_for_invalid_status_code_below_100(): void
    {
        $this->expectException(FrameworkMisconfigured::class);
        $this->expectExceptionMessage('Runtime response status "99" is invalid.');

        new RuntimeResponse(statusCode: 99);
    }

    #[Test]
    public function it_throws_for_invalid_status_code_above_599(): void
    {
        $this->expectException(FrameworkMisconfigured::class);
        $this->expectExceptionMessage('Runtime response status "600" is invalid.');

        new RuntimeResponse(statusCode: 600);
    }

    #[Test]
    public function it_accepts_valid_status_codes(): void
    {
        $validCodes = [100, 200, 301, 404, 500, 599];

        foreach ($validCodes as $code) {
            $response = new RuntimeResponse(statusCode: $code);
            self::assertSame($code, $response->statusCode());
        }
    }

    #[Test]
    public function it_handles_informational_responses(): void
    {
        $response = new RuntimeResponse(statusCode: 100, body: 'Continue');

        self::assertSame(100, $response->statusCode());
        self::assertSame('Continue', $response->body());
    }

    #[Test]
    public function it_handles_server_error_responses(): void
    {
        $response = new RuntimeResponse(
            statusCode: 500,
            headers: ['Content-Type' => ['text/plain']],
            body: 'Internal Server Error',
        );

        self::assertSame(500, $response->statusCode());
        self::assertSame(['Content-Type' => ['text/plain']], $response->headers());
        self::assertSame('Internal Server Error', $response->body());
    }
}