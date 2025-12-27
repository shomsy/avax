<?php

declare(strict_types=1);

namespace Avax\Database\Support;

/**
 * The "Number Clicker" (Sequence Tracker).
 *
 * -- what is it?
 * This is a very simple tool that gives out numbers in order (1, 2, 3...).
 * It's primarily used to tag events so we know exactly which one happened
 * first.
 *
 * -- how to imagine it:
 * Think of the "Take a Number" machine at a deli counter. Every event
 * that happens in the database pulls a ticket from this machine, so
 * we can perfectly reconstruct the order of events later, even if
 * they happen only microseconds apart.
 *
 * -- why this exists:
 * To provide a "Source of Truth" for time. Computers can sometimes have
 * tiny fluctuations in their clocks, but a simple counter never lies
 * about the order of arrival.
 *
 * -- mental models:
 * - "Monotonic": It only ever goes UP. It never repeats and never goes
 *   backwards.
 */
final class SequenceTracker
{
    /** @var int The current number on the clicker. */
    private static int $counter = 0;

    /**
     * Pull the next number from the machine.
     *
     * @return int The next number in the chain.
     */
    public static function next() : int
    {
        return ++self::$counter;
    }
}
