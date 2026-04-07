<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Resolution\Pipeline\Strategies;

use Avax\Container\DependencyInjection\Capability\Resolution\Errors\ContainerException;

/**
 * Small state machine that validates ordered resolution transitions.
 */
final class ResolutionStateMachine
{
    /** @var ResolutionState */
    private const TRANSITIONS = [
        ResolutionState::ContextualLookup->value => [ResolutionState::DefinitionLookup],
        ResolutionState::DefinitionLookup->value => [ResolutionState::Autowire],
        ResolutionState::Autowire->value         => [ResolutionState::Evaluate, ResolutionState::NotFound],
        ResolutionState::Evaluate->value         => [ResolutionState::Instantiate],
        ResolutionState::Instantiate->value      => [ResolutionState::Success, ResolutionState::Failure, ResolutionState::NotFound],
        ResolutionState::Success->value          => [],
        ResolutionState::Failure->value          => [],
        ResolutionState::NotFound->value         => [],
    ];

    private ResolutionState $state;

    public function __construct(ResolutionState $initial = ResolutionState::ContextualLookup)
    {
        $this->state = $initial;
    }

    public function advanceTo(ResolutionState $next, bool $hit = false) : void
    {
        $allowed = self::TRANSITIONS[$this->state->value] ?? [];
        if (! in_array($next, $allowed, true)) {
            throw new ContainerException(
                message: sprintf(
                    'Invalid resolution transition from [%s] to [%s]',
                    $this->state->value,
                    $next->value
                )
            );
        }

        if ($this->isTerminal(state: $next) && $hit === false && $next !== ResolutionState::NotFound) {
            throw new ContainerException(
                message: sprintf(
                    'Terminal transition to [%s] requires a resolution hit from [%s]',
                    $next->value,
                    $this->state->value
                )
            );
        }

        $this->state = $next;
    }

    public function isTerminal(ResolutionState $state) : bool
    {
        return in_array($state, [ResolutionState::Success, ResolutionState::Failure, ResolutionState::NotFound], true);
    }

    public function state() : ResolutionState
    {
        return $this->state;
    }
}
