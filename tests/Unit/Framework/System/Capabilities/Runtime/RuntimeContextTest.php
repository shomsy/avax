<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Capabilities\Runtime;

use Avax\Framework\System\Capabilities\RequestScope\RequestScopeId;
use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResult;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Avax\Tests\Framework\TestCase;

#[CoversClass(RuntimeContext::class)]
#[UsesClass(RequestScopeId::class)]
final class RuntimeContextTest extends TestCase
{
    #[Test]
    public function it_has_no_active_request_when_created(): void
    {
        $context = new RuntimeContext();

        self::assertFalse($context->hasActiveRequest());
        self::assertNull($context->currentRequest());
        self::assertNull($context->currentScopeId());
        self::assertNull($context->lastResult());
    }

    #[Test]
    public function it_starts_request_and_sets_active_request(): void
    {
        $context = new RuntimeContext();
        $scopeId = RequestScopeId::generate();
        $request = new RuntimeRequest(method: 'GET', uri: '/test');

        $context->startRequest(scopeId: $scopeId, request: $request);

        self::assertTrue($context->hasActiveRequest());
        self::assertSame($request, $context->currentRequest());
        self::assertSame($scopeId, $context->currentScopeId());
    }

    #[Test]
    public function it_throws_when_starting_request_while_another_is_active(): void
    {
        $context = new RuntimeContext();
        $scopeId = RequestScopeId::generate();
        $request = new RuntimeRequest(method: 'GET', uri: '/test');

        $context->startRequest(scopeId: $scopeId, request: $request);

        $this->expectException(FrameworkMisconfigured::class);
        $this->expectExceptionMessage('Runtime context already has an active request.');

        $context->startRequest(
            scopeId: RequestScopeId::generate(),
            request: new RuntimeRequest(method: 'POST', uri: '/other'),
        );
    }

    #[Test]
    public function it_finishes_request_and_clears_active_request(): void
    {
        $context = new RuntimeContext();
        $scopeId = RequestScopeId::generate();
        $request = new RuntimeRequest(method: 'GET', uri: '/test');
        $result = RuntimeResult::fromConsoleOutput(output: 'done');

        $context->startRequest(scopeId: $scopeId, request: $request);
        $context->finishRequest(result: $result);

        self::assertFalse($context->hasActiveRequest());
        self::assertNull($context->currentRequest());
        self::assertNull($context->currentScopeId());
        self::assertSame($result, $context->lastResult());
    }

    #[Test]
    public function it_records_result_without_affecting_active_request(): void
    {
        $context = new RuntimeContext();
        $scopeId = RequestScopeId::generate();
        $request = new RuntimeRequest(method: 'GET', uri: '/test');
        $result = RuntimeResult::fromConsoleOutput(output: 'output');

        $context->startRequest(scopeId: $scopeId, request: $request);
        $context->recordResult(result: $result);

        self::assertTrue($context->hasActiveRequest());
        self::assertSame($request, $context->currentRequest());
        self::assertSame($result, $context->lastResult());
    }

    #[Test]
    public function it_returns_last_result_when_no_request_is_active(): void
    {
        $context = new RuntimeContext();
        $result = RuntimeResult::fromConsoleOutput(output: 'result');

        $context->recordResult(result: $result);

        self::assertSame($result, $context->lastResult());
        self::assertNull($context->currentRequest());
    }

    #[Test]
    public function it_resets_state_and_clears_all_values(): void
    {
        $context = new RuntimeContext();
        $scopeId = RequestScopeId::generate();
        $request = new RuntimeRequest(method: 'GET', uri: '/test');
        $result = RuntimeResult::fromConsoleOutput(output: 'done');

        $context->startRequest(scopeId: $scopeId, request: $request);
        $context->recordResult(result: $result);
        $context->resetState();

        self::assertFalse($context->hasActiveRequest());
        self::assertNull($context->currentRequest());
        self::assertNull($context->currentScopeId());
        self::assertNull($context->lastResult());
    }
}