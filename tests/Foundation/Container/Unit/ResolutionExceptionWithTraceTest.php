<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Unit;

use Avax\Container\Core\Kernel\ResolutionState;
use Avax\Container\Features\Core\Exceptions\ResolutionExceptionWithTrace;
use Avax\Container\Observe\Trace\ResolutionTrace;
use PHPUnit\Framework\TestCase;

/**
 * @see docs/tests/Unit/ResolutionExceptionWithTraceTest.md#quick-summary
 */
final class ResolutionExceptionWithTraceTest extends TestCase
{
    /**
     * @see docs/tests/Unit/ResolutionExceptionWithTraceTest.md#method-testcarriestraceandserializes
     */
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
