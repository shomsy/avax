<?php

declare(strict_types=1);

namespace Avax\Database\Foundation\Connection\Pool;

/**
 * The "Ledger" (Internal Accountant) of the connection pool.
 *
 * -- what is it?
 * This is a simple counter class. It doesn't open connections or talk to 
 * databases; it just keeps track of the numbers (How many connections are open? 
 * Are we full? How many times have we used the pool?).
 *
 * -- how to imagine it:
 * Think of a "Bouncer" at a club with a clicker counter. He doesn't know 
 * how to dance (run SQL), but he knows exactly how many people are inside 
 * and when to stop letting new people in because the room is full.
 *
 * -- why this exists:
 * To separate the "Business Logic" of pooling from the simple "Accounting" 
 * of numbers. It makes the code cleaner and ensures we never accidentally 
 * open more connections than we're allowed.
 *
 * -- mental models:
 * - "Slot": A permission space for one connection.
 * - "Spawned": A connection that has been created (born) and is currently 
 *    active or waiting.
 */
final class PoolState
{
    /** @var int The current number of connections we have "Created" and are still managing. */
    private int $spawnedCount      = 0;

    /** @var int A persistent counter of every single time someone borrowed a connection. */
    private int $totalAcquisitions = 0;

    /**
     * @param int $maxConnections The absolute maximum number of people allowed in at once.
     */
    public function __construct(private readonly int $maxConnections) {}

    /**
     * Try to "Check in" and reserve a space for a new connection.
     *
     * -- how it works:
     * We check the counter. If we are under the limit, we "click" the 
     * counter up and say "Yes, you can proceed". If we are full, we 
     * say "No".
     *
     * @return bool True if there was room, false if we are at capacity.
     */
    public function tryReserveSlot(): bool
    {
        if ($this->spawnedCount >= $this->maxConnections) {
            return false;
        }

        $this->spawnedCount++;
        $this->totalAcquisitions++;

        return true;
    }

    /**
     * Record that someone borrowed an ALREADY-EXISTING connection.
     *
     * -- why this exists:
     * Even if we didn't need to open a new connection, we still want to 
     * count that a "Borrow" happened for our statistics.
     */
    public function recordRecycledAcquisition(): void
    {
        $this->totalAcquisitions++;
    }

    /**
     * Tell the accountant that a connection was destroyed or closed.
     *
     * -- intent:
     * This "frees up" a slot so someone else can open a new connection 
     * later if they need to.
     */
    public function releaseSlot(): void
    {
        $this->spawnedCount = max(0, $this->spawnedCount - 1);
    }

    /**
     * Get the current number of living connections.
     */
    public function getSpawnedCount(): int
    {
        return $this->spawnedCount;
    }

    /**
     * Get the total historical number of "Borrows" performed.
     */
    public function getTotalAcquisitions(): int
    {
        return $this->totalAcquisitions;
    }
}
