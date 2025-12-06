<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Contracts;

/**
 * Interface FlashBagInterface
 *
 * This interface defines the contract for managing a flash-based temporary session store.
 * Flash bags are designed to hold transient data, meant to persist only across a single request-response cycle.
 * It extends the SessionBagInterface for consistent session operations, adding functionality specific to flash data.
 *
 * Example Use Cases:
 * - Storing success messages after form submissions.
 * - Passing transient errors or warnings between requests.
 * - Temporary state management that requires automatic expiration.
 *
 * @package Avax\HTTP\Session\Contracts
 */
interface FlashBagInterface extends SessionBagInterface
{
    /**
     * Preserves a specific key-value pair in the flash bag across the next request.
     *
     * Flash data is commonly designed to be cleared after the next access.
     * The `keep` method ensures that a particular key's value is retained for subsequent processing.
     *
     * Example:
     * ```php
     * $flashBag->keep('successMessage');
     * ```
     * Retention can be useful for cases where data should be available for longer interactions.
     *
     * @param string $key
     *   The unique identifier for the flash data to be retained.
     *
     * @return void
     *   This method does not return a value.
     *
     * @see self::reflash() for retaining all flash data at once.
     */
    public function keep(string $key) : void;

    /**
     * Re-flashes all existing flash data for the next request.
     *
     * This method reinitializes and retains all current flash messages, ensuring that no data is removed.
     * Useful when flash data needs to survive multiple request cycles for extended processing.
     *
     * Example:
     * ```php
     * $flashBag->reflash();
     * ```
     * Unlike `keep`, this applies globally for the entire flash bag.
     *
     * @return void
     *   This method does not return a value.
     *
     * @see self::keep() for retaining specific keys.
     */
    public function reflash() : void;
}