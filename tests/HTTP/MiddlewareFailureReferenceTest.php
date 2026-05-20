<?php

declare(strict_types=1);

namespace Avax\Tests\HTTP;

use Avax\Components\HTTP\Middleware\System\Flows\RunMiddlewarePipeline\MiddlewarePipelineFailed;
use Avax\Components\HTTP\Middleware\System\Foundation\Failure\MiddlewareFailure;
use PHPUnit\Framework\TestCase;

/**
 * Regression test for TODO-016: broken reference semantics in HTTP Middleware.
 *
 * Proves that MiddlewarePipelineFailed correctly extends MiddlewareFailure
 * with the proper import path (Middleware/System/Foundation/Failure, not
 * System/Capabilities/MiddlewarePipeline/System/Foundation/Failure).
 */
final class MiddlewareFailureReferenceTest extends TestCase
{
    public function test_middleware_pipeline_failed_extends_middleware_failure() : void
    {
        $exception = new MiddlewarePipelineFailed('test');

        $this->assertInstanceOf(MiddlewareFailure::class, $exception);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertSame('test', $exception->getMessage());
    }

    public function test_middleware_failure_is_runtime_exception() : void
    {
        $exception = new MiddlewareFailure('base test');

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertSame('base test', $exception->getMessage());
    }
}
