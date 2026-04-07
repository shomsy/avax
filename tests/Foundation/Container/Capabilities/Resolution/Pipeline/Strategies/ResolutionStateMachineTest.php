<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Capabilities\Resolution\Pipeline\Strategies;

use Avax\Container\Capabilities\Resolution\Errors\ContainerException;
use Avax\Container\Capabilities\Resolution\Pipeline\Strategies\ResolutionState;
use Avax\Container\Capabilities\Resolution\Pipeline\Strategies\ResolutionStateMachine;
use PHPUnit\Framework\TestCase;

final class ResolutionStateMachineTest extends TestCase
{
    public function test_allows_valid_transition() : void
    {
        $stateMachine = new ResolutionStateMachine;

        $stateMachine->advanceTo(next: ResolutionState::DefinitionLookup);

        $this->assertSame(expected: ResolutionState::DefinitionLookup, actual: $stateMachine->state());
    }

    public function test_throws_on_invalid_transition() : void
    {
        $stateMachine = new ResolutionStateMachine;

        $this->expectException(exception: ContainerException::class);
        $stateMachine->advanceTo(next: ResolutionState::Instantiate);
    }

    public function test_terminal_transition_without_hit_fails() : void
    {
        $stateMachine = new ResolutionStateMachine;

        $this->expectException(exception: ContainerException::class);
        $stateMachine->advanceTo(next: ResolutionState::Success);
    }

    public function test_cannot_skip_instantiate() : void
    {
        $stateMachine = new ResolutionStateMachine;

        $stateMachine->advanceTo(next: ResolutionState::DefinitionLookup);
        $stateMachine->advanceTo(next: ResolutionState::Autowire);
        $stateMachine->advanceTo(next: ResolutionState::Evaluate);

        $this->expectException(exception: ContainerException::class);
        $stateMachine->advanceTo(next: ResolutionState::Success, hit: true);
    }
}
