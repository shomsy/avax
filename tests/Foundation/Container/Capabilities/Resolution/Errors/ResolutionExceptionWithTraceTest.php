<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Capability\Resolution\Errors;

use Avax\Container\DependencyInjection\Capability\Observability\Trace\ResolutionTrace;
use Avax\Container\DependencyInjection\Capability\Resolution\Errors\ResolutionExceptionWithTrace;
use Avax\Container\DependencyInjection\Capability\Resolution\Pipeline\Strategies\ResolutionState;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

final class ResolutionExceptionWithTraceTest extends TestCase
{
    public function test_carries_trace_and_serializes() : void
    {
        $trace = (new ResolutionTrace)
            ->record(state: ResolutionState::ContextualLookup, stage: 'contextual', outcome: 'start')
            ->record(state: ResolutionState::ContextualLookup, stage: 'contextual', outcome: 'miss');

        $exception = new ResolutionExceptionWithTrace(trace: $trace, message: 'not found');

        $this->assertSame(expected: $trace, actual: $exception->trace());
        $this->assertStringContainsString(needle: 'contextual', haystack: (string) $exception);

        $serialized = $exception->jsonSerialize();
        $this->assertArrayHasKey(key: 'trace', array: $serialized);
        $this->assertCount(expectedCount: 2, haystack: $serialized['trace']);
    }
}
