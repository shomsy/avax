<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Capabilities\Runtime;

use Avax\Framework\System\Capabilities\Runtime\RuntimeResponse;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResult;
use Avax\Tests\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RuntimeResult::class)]
#[UsesClass(RuntimeResponse::class)]
final class RuntimeResultTest extends TestCase
{
    #[Test]
    public function it_creates_result_from_response(): void
    {
        $runtimeResponse = new RuntimeResponse(
            statusCode: 200,
            headers: ['Content-Type' => ['application/json']],
            body: '{"data":"test"}',
        );

        $runtimeResult = RuntimeResult::fromResponse(response: $runtimeResponse);

        self::assertSame($runtimeResponse, $runtimeResult->response());
        self::assertNull($runtimeResult->response()->headers() !== [] ? $runtimeResult->response() : null);
        self::assertSame(0, $runtimeResult->exitCode());
        self::assertSame('{"data":"test"}', $runtimeResult->output());
    }

    #[Test]
    public function it_creates_result_from_console_output(): void
    {
        $runtimeResult = RuntimeResult::fromConsoleOutput(output: 'Command completed', exitCode: 0);

        self::assertNull($runtimeResult->response());
        self::assertSame(0, $runtimeResult->exitCode());
        self::assertSame('Command completed', $runtimeResult->output());
    }

    #[Test]
    public function it_creates_console_result_with_failure_exit_code(): void
    {
        $runtimeResult = RuntimeResult::fromConsoleOutput(output: 'Error occurred', exitCode: 1);

        self::assertNull($runtimeResult->response());
        self::assertSame(1, $runtimeResult->exitCode());
        self::assertSame('Error occurred', $runtimeResult->output());
    }

    #[Test]
    public function it_returns_body_as_output_for_http_response(): void
    {
        $runtimeResponse = new RuntimeResponse(statusCode: 200, body: 'Hello World');

        $runtimeResult = RuntimeResult::fromResponse(response: $runtimeResponse);

        self::assertSame('Hello World', $runtimeResult->output());
        self::assertSame(0, $runtimeResult->exitCode());
    }

    #[Test]
    public function it_returns_null_response_for_console_results(): void
    {
        $runtimeResult = RuntimeResult::fromConsoleOutput(output: 'output');

        self::assertNull($runtimeResult->response());
    }

    #[Test]
    public function it_returns_response_object_for_http_results(): void
    {
        $runtimeResponse = new RuntimeResponse(statusCode: 404, body: 'Not Found');

        $runtimeResult = RuntimeResult::fromResponse(response: $runtimeResponse);

        self::assertNotNull($runtimeResult->response());
        self::assertSame(404, $runtimeResult->response()->statusCode());
    }
}
