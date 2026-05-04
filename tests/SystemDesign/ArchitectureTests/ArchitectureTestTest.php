<?php

declare(strict_types=1);

namespace Avax\Tests\SystemDesign\ArchitectureTests;

use Avax\Components\SystemDesign\ArchitectureTests\System\PublicSurface\ArchitectureTest;
use Avax\Tests\TestCase;

final class ArchitectureTestTest extends TestCase
{
    public function test_architecture_test_represents_result(): void
    {
        $test = new ArchitectureTest(
            name: 'no-circular-dependencies',
            passed: true,
            message: 'All dependencies are acyclic',
        );

        $this->assertTrue($test->passed);
        $this->assertSame('no-circular-dependencies', $test->name);
    }

    public function test_architecture_test_can_report_failure(): void
    {
        $test = new ArchitectureTest(
            name: 'no-shared-mutable-state',
            passed: false,
            message: 'Found 3 shared mutable statics',
        );

        $this->assertFalse($test->passed);
        $this->assertStringContainsString('shared mutable', $test->message);
    }
}
