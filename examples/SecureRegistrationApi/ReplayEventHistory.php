<?php

declare(strict_types=1);

namespace Avax\Examples\SecureRegistrationApi;

/**
 * ReplayEventHistory — Replays stored events to rebuild projections.
 *
 * Reference/proof only — demonstrates that stored events can rebuild
 * the RegisteredUserView from scratch.
 *
 * IMPORTANT: This is not a production replay engine.
 * No event store, stream versioning, snapshots, or upcasting.
 */
final class ReplayEventHistory
{
    /**
     * Replay all UserRegistered events to rebuild RegisteredUserView.
     *
     * @param list<object> $events
     */
    public function replay(array $events): void
    {
        foreach ($events as $event) {
            if ($event instanceof UserRegistered) {
                $projector = new ProjectRegisteredUser();
                $projector($event);
            }
        }
    }
}
