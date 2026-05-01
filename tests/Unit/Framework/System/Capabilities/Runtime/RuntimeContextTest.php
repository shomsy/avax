<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Capabilities\Runtime;

use Avax\Framework\System\Capabilities\RequestScope\RequestScopeId;
use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResult;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;
use Avax\Tests\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(RuntimeContext::class)]
#[UsesClass(RequestScopeId::class)]
final class RuntimeContextTest extends TestCase
{
    #[Test]
    public function it_has_no_active_request_when_created(): void
    {
        $runtimeContext = new RuntimeContext();

        self::assertFalse($runtimeContext->hasActiveRequest());
        self::assertNull($runtimeContext->currentRequest());
        self::assertNull($runtimeContext->currentScopeId());
        self::assertNull($runtimeContext->lastResult());
    }

    #[Test]
    public function it_starts_request_and_sets_active_request(): void
    {
        $runtimeContext = new RuntimeContext();
        $requestScopeId = RequestScopeId::generate();
        $runtimeRequest = new RuntimeRequest(method: 'GET', uri: '/test');

        $runtimeContext->startRequest(scopeId: $requestScopeId, request: $runtimeRequest);

        self::assertTrue($runtimeContext->hasActiveRequest());
        self::assertSame($runtimeRequest, $runtimeContext->currentRequest());
        self::assertSame($requestScopeId, $runtimeContext->currentScopeId());
    }

    #[Test]
    public function it_throws_when_starting_request_while_another_is_active(): void
    {
        $runtimeContext = new RuntimeContext();
        $requestScopeId = RequestScopeId::generate();
        $runtimeRequest = new RuntimeRequest(method: 'GET', uri: '/test');

        $runtimeContext->startRequest(scopeId: $requestScopeId, request: $runtimeRequest);

        $this->expectException(FrameworkMisconfigured::class);
        $this->expectExceptionMessage('Runtime context already has an active request.');

        $runtimeContext->startRequest(
            scopeId: RequestScopeId::generate(),
            request: new RuntimeRequest(method: 'POST', uri: '/other'),
        );
    }

    #[Test]
    public function it_finishes_request_and_clears_active_request(): void
    {
        $runtimeContext = new RuntimeContext();
        $requestScopeId = RequestScopeId::generate();
        $runtimeRequest = new RuntimeRequest(method: 'GET', uri: '/test');
        $runtimeResult  = RuntimeResult::fromConsoleOutput(output: 'done');

        $runtimeContext->startRequest(scopeId: $requestScopeId, request: $runtimeRequest);
        $runtimeContext->finishRequest(result: $runtimeResult);

        self::assertFalse($runtimeContext->hasActiveRequest());
        self::assertNull($runtimeContext->currentRequest());
        self::assertNull($runtimeContext->currentScopeId());
        self::assertSame($runtimeResult, $runtimeContext->lastResult());
    }

    #[Test]
    public function it_records_result_without_affecting_active_request(): void
    {
        $runtimeContext = new RuntimeContext();
        $requestScopeId = RequestScopeId::generate();
        $runtimeRequest = new RuntimeRequest(method: 'GET', uri: '/test');
        $runtimeResult  = RuntimeResult::fromConsoleOutput(output: 'output');

        $runtimeContext->startRequest(scopeId: $requestScopeId, request: $runtimeRequest);
        $runtimeContext->recordResult(result: $runtimeResult);

        self::assertTrue($runtimeContext->hasActiveRequest());
        self::assertSame($runtimeRequest, $runtimeContext->currentRequest());
        self::assertSame($runtimeResult, $runtimeContext->lastResult());
    }

    #[Test]
    public function it_returns_last_result_when_no_request_is_active(): void
    {
        $runtimeContext = new RuntimeContext();
        $runtimeResult  = RuntimeResult::fromConsoleOutput(output: 'result');

        $runtimeContext->recordResult(result: $runtimeResult);

        self::assertSame($runtimeResult, $runtimeContext->lastResult());
        self::assertNull($runtimeContext->currentRequest());
    }

    #[Test]
    public function it_resets_state_and_clears_all_values(): void
    {
        $runtimeContext = new RuntimeContext();
        $requestScopeId = RequestScopeId::generate();
        $runtimeRequest = new RuntimeRequest(method: 'GET', uri: '/test');
        $runtimeResult  = RuntimeResult::fromConsoleOutput(output: 'done');

        $runtimeContext->startRequest(scopeId: $requestScopeId, request: $runtimeRequest);
        $runtimeContext->recordResult(result: $runtimeResult);
        $runtimeContext->resetState();

        self::assertFalse($runtimeContext->hasActiveRequest());
        self::assertNull($runtimeContext->currentRequest());
        self::assertNull($runtimeContext->currentScopeId());
        self::assertNull($runtimeContext->lastResult());
    }
}
