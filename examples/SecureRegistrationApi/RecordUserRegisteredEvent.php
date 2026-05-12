<?php

declare(strict_types=1);

namespace Avax\Examples\SecureRegistrationApi;

/**
 * RecordUserRegisteredEvent — Listener that stores the UserRegistered event
 * in the reference event-history store.
 *
 * IMPORTANT: This is reference/proof only.
 * NOT production Event Sourcing Kit.
 */
final class RecordUserRegisteredEvent
{
    public function __invoke(UserRegistered $event): void
    {
        ReferenceEventHistoryStore::append($event);
    }
}
