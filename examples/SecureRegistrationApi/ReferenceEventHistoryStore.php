<?php

declare(strict_types=1);

namespace Avax\Examples\SecureRegistrationApi;

/**
 * ReferenceEventHistoryStore — Minimal in-memory event-history store.
 *
 * Stores UserRegistered events for replay proof.
 *
 * IMPORTANT: This is reference/proof only.
 * NOT production Event Sourcing Kit.
 * No EventStore, stream identity, stream versioning, optimistic concurrency,
 * serializer, upcasting, snapshots, projection runner, or replay engine.
 */
final class ReferenceEventHistoryStore
{
    /** @var list<object> */
    private static array $events = [];

    /**
     * Append an event to the history.
     */
    public static function append(object $event): void
    {
        self::$events[] = $event;
    }

    /**
     * Return all events of the given class for replay.
     *
     * @param class-string $eventClass
     * @return list<object>
     */
    public static function eventsOf(string $eventClass): array
    {
        return array_values(array_filter(
            self::$events,
            static fn (object $e): bool => $e::class === $eventClass,
        ));
    }

    /**
     * Return all stored events.
     *
     * @return list<object>
     */
    public static function all(): array
    {
        return self::$events;
    }

    /**
     * Reset the store (test isolation only).
     */
    public static function reset(): void
    {
        self::$events = [];
    }
}
