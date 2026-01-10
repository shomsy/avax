<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Unit;

use Avax\Container\Features\Core\ContainerBuilder;
use Avax\Container\Features\Core\Exceptions\ContainerException;
use PHPUnit\Framework\TestCase;

/**
 * PHPUnit test coverage for Container component behavior.
 *
 * @see docs_md/tests/Unit/CircularDependencyTest.md#quick-summary
 */
class CircularDependencyTest extends TestCase
{
    public function test_it_detects_simple_circular_dependency(): void
    {
        $builder = ContainerBuilder::create();
        $builder->bind(CircularA::class, CircularA::class);
        $builder->bind(CircularB::class, CircularB::class);
        $container = $builder->build();

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Circular dependency detected');
        $this->expectExceptionMessage('CircularA -> Avax\Container\Tests\Unit\CircularB -> Avax\Container\Tests\Unit\CircularA');

        $container->get(CircularA::class);
    }
}

class CircularA
{
    public function __construct(CircularB $b) {}
}

class CircularB
{
    public function __construct(CircularA $a) {}
}
