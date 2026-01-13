<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Kernel;

use Avax\Container\Core\Kernel\ResolutionPipelineController;
use Avax\Container\Core\Kernel\ResolutionState;
use Avax\Container\Features\Core\Exceptions\ContainerException;
use PHPUnit\Framework\TestCase;

/**
 * @see docs/tests/Kernel/ResolutionPipelineControllerTest.md#quick-summary
 */
final class ResolutionPipelineControllerTest extends TestCase
{
    public function test_allows_valid_transition() : void
    {
        $controller = new ResolutionPipelineController;

        $controller->advanceTo(next: ResolutionState::DefinitionLookup);

        $this->assertSame(expected: ResolutionState::DefinitionLookup, actual: $controller->state());
    }

    public function test_throws_on_invalid_transition() : void
    {
        $controller = new ResolutionPipelineController;

        $this->expectException(exception: ContainerException::class);
        $controller->advanceTo(next: ResolutionState::Instantiate);
    }

    public function test_terminal_transition_without_hit_fails() : void
    {
        $controller = new ResolutionPipelineController;

        $this->expectException(exception: ContainerException::class);
        $controller->advanceTo(next: ResolutionState::Success);
    }

    public function test_cannot_skip_instantiate() : void
    {
        $controller = new ResolutionPipelineController;

        $controller->advanceTo(next: ResolutionState::DefinitionLookup);
        $controller->advanceTo(next: ResolutionState::Autowire);
        $controller->advanceTo(next: ResolutionState::Evaluate);

        $this->expectException(exception: ContainerException::class);
        $controller->advanceTo(next: ResolutionState::Success, hit: true);
    }
}
