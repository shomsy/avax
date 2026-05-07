<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\System;

use Avax\Components\Operations\System\Foundation\OperationsFailure;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class OperationsSystemCapabilitiesTest extends TestCase
{
    public function test_operations_failure_is_runtime_exception() : void
    {
        $failure = new OperationsFailure('Orchestration failed');
        $this->assertInstanceOf(RuntimeException::class, $failure);
        $this->assertSame('Orchestration failed', $failure->getMessage());
    }

    public function test_operations_failure_preserves_previous_exception() : void
    {
        $previous = new LogicException('Root cause');
        $failure  = new OperationsFailure('Wrapper', 0, $previous);
        $this->assertSame($previous, $failure->getPrevious());
    }
}
