<?php

declare(strict_types=1);

namespace Avax\HTTP\Session;

use Closure;
use Avax\HTTP\Session\Contracts\BagRegistryInterface;
use Avax\HTTP\Session\Contracts\SessionInterface;
use Avax\HTTP\Session\Contracts\SessionLoggerInterface;

/**
 * Class LoggableSessionDecorator
 *
 * This class implements the Decorator pattern to transparently wrap a SessionInterface instance
 * and provide logging behavior via a SessionLoggerInterface.
 *
 * Each session operation is logged with appropriate context to aid debugging, traceability,
 * analytics, and observability in production-grade systems.
 */
final readonly class LoggableSessionDecorator implements SessionInterface
{
    public function __construct(
        private SessionInterface       $inner,
        private SessionLoggerInterface $logger
    ) {}

    /**
     * Start the session lifecycle and log the event.
     */
    public function start() : void
    {
        $this->logger->debug(message: 'Session started.');
        $this->inner->start();
    }

    /**
     * Retrieve the entire session data array and log the access.
     */
    public function all() : array
    {
        $this->logger->debug(message: 'Session::all called');

        return $this->inner->all();
    }

    /**
     * Remove all session data and log the flush action.
     */
    public function flush() : void
    {
        $this->logger->info(message: 'Flushing all session data.');
        $this->inner->flush();
    }

    /**
     * Invalidate the session and log the invalidation event.
     */
    public function invalidate() : void
    {
        $this->logger->warning(message: 'Session invalidated.');
        $this->inner->invalidate();
    }

    /**
     * Regenerate session ID and optionally delete the old session.
     * Logs the operation and its parameter.
     */
    public function regenerateId(bool $deleteOldSession = true) : void
    {
        $this->logger->info(message: 'Session ID regenerated.', context: [
            'delete_old' => $deleteOldSession,
        ]);

        $this->inner->regenerateId(deleteOldSession: $deleteOldSession);
    }

    /**
     * Retrieve a session value by key and log whether fallback default was used.
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        $value = $this->inner->get(key: $key, default: $default);

        $this->logger->debug(message: 'Session::get', context: [
            'key'              => $key,
            'returned_default' => $value === $default,
        ]);

        return $value;
    }

    /**
     * Store a value in the session and log the operation with value type.
     */
    public function set(string $key, mixed $value) : void
    {
        $this->logger->info(message: 'Session::set', context: [
            'key'        => $key,
            'value_type' => get_debug_type($value),
        ]);

        $this->inner->set(key: $key, value: $value);
    }

    /**
     * Set session value (same as `set`) with detailed logging including value content.
     */
    public function put(string $key, mixed $value) : void
    {
        $this->logger->info(message: 'Session::put', context: [
            'key'        => $key,
            'value_type' => get_debug_type($value),
            'value'      => $value,
        ]);

        $this->inner->put(key: $key, value: $value);
    }

    /**
     * Remove a key from the session and log the deletion.
     */
    public function delete(string $key) : void
    {
        $this->logger->notice(message: 'Session::delete', context: ['key' => $key]);
        $this->inner->delete(key: $key);
    }

    /**
     * Flash a value to the session for next request and log the key.
     */
    public function flash(string $key, mixed $value) : void
    {
        $this->logger->info(message: 'Session::flash', context: ['key' => $key]);
        $this->inner->flash(key: $key, value: $value);
    }

    /**
     * Retrieve a flashed value and log the key being accessed.
     */
    public function getFlash(string $key, mixed $default = null) : mixed
    {
        $this->logger->debug(message: 'Session::getFlash', context: ['key' => $key]);

        return $this->inner->getFlash(key: $key, default: $default);
    }

    /**
     * Retain a flashed value for another request cycle and log the key.
     */
    public function keepFlash(string $key) : void
    {
        $this->logger->info(message: 'Session::keepFlash', context: ['key' => $key]);
        $this->inner->keepFlash(key: $key);
    }

    /**
     * Flash an entire input array to the session (used for old input support).
     */
    public function flashInput(array $input) : void
    {
        $this->logger->info(message: 'Session::flashInput invoked.');
        $this->inner->flashInput(input: $input);
    }

    /**
     * Retrieve old form input (flashed) and log the key.
     */
    public function getOldInput(string $key, mixed $default = null) : mixed
    {
        $this->logger->debug(message: 'Session::getOldInput', context: ['key' => $key]);

        return $this->inner->getOldInput(key: $key, default: $default);
    }

    /**
     * Store a key with TTL (Time To Live) and log the duration.
     */
    public function putWithTTL(string $key, mixed $value, int $ttl) : void
    {
        $this->logger->debug(message: 'Session::putWithTTL', context: [
            'key' => $key,
            'ttl' => $ttl,
        ]);

        $this->inner->putWithTTL(key: $key, value: $value, ttl: $ttl);
    }

    /**
     * Retrieve a key and remove it from the session. Logs retrieval.
     */
    public function pull(string $key, mixed $default = null) : mixed
    {
        $this->logger->debug(message: 'Session::pull', context: ['key' => $key]);

        return $this->inner->pull(key: $key, default: $default);
    }

    /**
     * Attempt to get a value, or compute and store via callback.
     * Logs if cache hit occurred.
     */
    public function remember(string $key, Closure $callback) : mixed
    {
        $exists = $this->inner->has(key: $key);

        $this->logger->debug(message: 'Session::remember', context: [
            'key'    => $key,
            'cached' => $exists,
        ]);

        return $this->inner->remember(key: $key, callback: $callback);
    }

    /**
     * Check for the existence of a key and log result.
     */
    public function has(string $key) : bool
    {
        $exists = $this->inner->has(key: $key);

        $this->logger->debug(message: 'Session::has', context: [
            'key'    => $key,
            'exists' => $exists,
        ]);

        return $exists;
    }

    /**
     * Increment a numeric value in session and log amount.
     */
    public function increment(string $key, int $amount = 1) : int
    {
        $this->logger->info(message: 'Session::increment', context: [
            'key'    => $key,
            'amount' => $amount,
        ]);

        return $this->inner->increment(key: $key, amount: $amount);
    }

    /**
     * Decrement a numeric value in session and log amount.
     */
    public function decrement(string $key, int $amount = 1) : int
    {
        $this->logger->info(message: 'Session::decrement', context: [
            'key'    => $key,
            'amount' => $amount,
        ]);

        return $this->inner->decrement(key: $key, amount: $amount);
    }

    /**
     * Check if key exists using array-access interface. Logs the check.
     */
    public function offsetExists(mixed $offset) : bool
    {
        $exists = $this->inner->offsetExists($offset);

        $this->logger->debug(message: 'Session::offsetExists', context: [
            'key'    => $offset,
            'exists' => $exists,
        ]);

        return $exists;
    }

    /**
     * Retrieve a key via array-access interface. Logs access.
     */
    public function offsetGet(mixed $offset) : mixed
    {
        $this->logger->debug(message: 'Session::offsetGet', context: ['key' => $offset]);

        return $this->inner->offsetGet($offset);
    }

    /**
     * Assign a value via array-access interface. Logs metadata.
     */
    public function offsetSet(mixed $offset, mixed $value) : void
    {
        $this->logger->debug(message: 'Session::offsetSet', context: [
            'key'        => $offset,
            'value_type' => get_debug_type($value),
        ]);

        $this->inner->offsetSet($offset, $value);
    }

    /**
     * Unset a key using array-access syntax. Logs removal.
     */
    public function offsetUnset(mixed $offset) : void
    {
        $this->logger->notice(message: 'Session::offsetUnset', context: ['key' => $offset]);
        $this->inner->offsetUnset($offset);
    }

    /**
     * Return internal BagRegistry for managing session bags.
     */
    public function getRegistry() : BagRegistryInterface
    {
        $this->logger->debug(message: 'Session::getRegistry');

        return $this->inner->getRegistry();
    }
}
