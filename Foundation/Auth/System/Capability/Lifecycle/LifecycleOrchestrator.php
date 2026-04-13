<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Lifecycle;

use Avax\Auth\System\Foundation\Clock;

/**
 * Orchestrates identity lifecycle transitions across multiple sources.
 *
 * Manages joiner/mover/leaver patterns and conflict resolution.
 */
final readonly class LifecycleOrchestrator
{
    public function __construct(
        private Clock $clock
    ) {}

    /**
     * Determines the effective lifecycle state given multiple source inputs.
     */
    public function resolveState(
        LifecycleState $localState,
        LifecycleState $federatedState,
        LifecycleState $scimState
    ) : LifecycleState {
        if ($scimState === LifecycleState::DEPROVISIONED) {
            return LifecycleState::DEPROVISIONED;
        }

        if ($federatedState === LifecycleState::DEPROVISIONED) {
            return LifecycleState::DEPROVISIONED;
        }

        if ($scimState === LifecycleState::SUSPENDED) {
            return LifecycleState::SUSPENDED;
        }

        if ($federatedState === LifecycleState::SUSPENDED) {
            return LifecycleState::SUSPENDED;
        }

        if ($localState === LifecycleState::DISABLED) {
            return LifecycleState::DISABLED;
        }

        if ($scimState === LifecycleState::ACTIVE) {
            return LifecycleState::ACTIVE;
        }

        if ($federatedState === LifecycleState::ACTIVE) {
            return LifecycleState::ACTIVE;
        }

        return $localState;
    }

    /**
     * Determines source priority for conflict resolution.
     */
    public function resolveSource(
        LifecycleState $localState,
        LifecycleState $federatedState,
        LifecycleState $scimState,
        SourcePriority $priority
    ) : ResolvedLifecycleSource {
        return match ($priority) {
            SourcePriority::LOCAL => new ResolvedLifecycleSource(
                Source::LOCAL,
                $localState,
                $this->clock->now()
            ),
            SourcePriority::FEDERATION => new ResolvedLifecycleSource(
                Source::FEDERATION,
                $federatedState,
                $this->clock->now()
            ),
            SourcePriority::SCIM => new ResolvedLifecycleSource(
                Source::SCIM,
                $scimState,
                $this->clock->now()
            ),
        };
    }

    /**
     * Determines allowed transitions from current state.
     *
     * @return list<LifecycleEvent>
     */
    public function allowedTransitions(LifecycleState $state) : array
    {
        return match ($state) {
            LifecycleState::PENDING => [LifecycleEvent::JOIN, LifecycleEvent::LEAVE],
            LifecycleState::ACTIVE => [LifecycleEvent::MOVE, LifecycleEvent::SUSPEND, LifecycleEvent::DISABLE, LifecycleEvent::DEPROVISION],
            LifecycleState::SUSPENDED => [LifecycleEvent::REINSTATE, LifecycleEvent::ENABLE, LifecycleEvent::DEPROVISION],
            LifecycleState::DISABLED => [LifecycleEvent::ENABLE, LifecycleEvent::DEPROVISION],
            LifecycleState::DISABLED, LifecycleState::DEPROVISIONED => [],
        };
    }
}