<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Capabilities\Runtime;

use Avax\Framework\System\Capabilities\Runtime\RuntimeResult;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Avax\Tests\Framework\TestCase;

#[CoversClass(RuntimeResult::class)]
#[UsesClass(RuntimeResponse::class)]
final class RuntimeResultTest extends TestCase
{
    #[Test]
    public function it_creates_result_from_response(): void
    {
        $response = new RuntimeResponse(
            statusCode: 200,
            headers: ['Content-Type' => ['application/json']],
            body: '{"data":"test"}',
        );

        $result = RuntimeResult::fromResponse(response: $response);

        self::assertSame($response, $result->response());
        self::assertNull($result->response()->headers() !== [] ? $result->response() : null);
        self::assertSame(0, $result->exitCode());
        self::assertSame('{"data":"test"}', $result->output());
    }

    #[Test]
    public function it_creates_result_from_console_output(): void
    {
        $result = RuntimeResult::fromConsoleOutput(output: 'Command completed', exitCode: 0);

        self::assertNull($result->response());
        self::assertSame(0, $result->exitCode());
        self::assertSame('Command completed', $result->output());
    }

    #[Test]
    public function it_creates_console_result_with_failure_exit_code(): void
    {
        $result = RuntimeResult::fromConsoleOutput(output: 'Error occurred', exitCode: 1);

        self::assertNull($result->response());
        self::assertSame(1, $result->exitCode());
        self::assertSame('Error occurred', $result->output());
    }

    #[Test]
    public function it_returns_body_as_output_for_http_response(): void
    {
        $response = new RuntimeResponse(statusCode: 200, body: 'Hello World');

        $result = RuntimeResult::fromResponse(response: $response);

        self::assertSame('Hello World', $result->output());
        self::assertSame(0, $result->exitCode());
    }

    #[Test]
    public function it_returns_null_response_for_console_results(): void
    {
        $result = RuntimeResult::fromConsoleOutput(output: 'output');

        self::assertNull($result->response());
    }

    #[Test]
    public function it_returns_response_object_for_http_results(): void
    {
        $response = new RuntimeResponse(statusCode: 404, body: 'Not Found');

        $result = RuntimeResult::fromResponse(response: $response);

        self::assertNotNull($result->response());
        self::assertSame(404, $result->response()->statusCode());
    }
}