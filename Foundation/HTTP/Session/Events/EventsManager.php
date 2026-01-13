<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Events;

/**
 * 🧠 EventsManager - Session Events Operations Orchestrator
 * ============================================================
 *
 * The EventsManager orchestrates all event-driven operations within
 * the session system, providing pub/sub functionality for reactive
 * programming and integration with external systems.
 *
 * This manager provides:
 * - Event listener registration (persistent and one-time)
 * - Synchronous event dispatching
 * - Asynchronous event dispatching (if configured)
 * - Listener lifecycle management
 *
 * 💡 Design Philosophy:
 * Events enable reactive session architecture where external systems
 * can respond to session changes in real-time. This manager ensures
 * clean separation between event producers and consumers.
 *
 * Common Events:
 * - session.stored: When a value is stored
 * - session.retrieved: When a value is retrieved
 * - session.deleted: When a value is deleted
 * - session.flushed: When session is cleared
 * - session.login: When user logs in
 * - session.terminated: When session ends
 * - session.transaction.commit: When transaction commits
 * - session.transaction.rollback: When transaction rolls back
 *
 * @author  Milos
 *
 * @version 5.0
 */
final readonly class EventsManager
{
    /**
     * EventsManager Constructor.
     *
     * @param Events                    $events          The synchronous event dispatcher.
     * @param AsyncEventDispatcher|null $asyncDispatcher Optional async event dispatcher.
     */
    public function __construct(
        private Events                    $events,
        private AsyncEventDispatcher|null $asyncDispatcher = null
    ) {}

    // ----------------------------------------------------------------
    // 🔹 Event Listener Registration
    // ----------------------------------------------------------------

    /**
     * Register a persistent event listener.
     *
     * The listener will be called every time the event is dispatched.
     *
     * @param string   $event    Event name to listen for.
     * @param callable $callback Function to execute when event fires.
     *
     * @example
     * ```php
     * $events->listen('session.stored', function($data) {
     *     logger()->info('Session updated', $data);
     * });
     * ```
     */
    public function listen(string $event, callable $callback) : void
    {
        $this->events->listen(event: $event, callback: $callback);
    }

    /**
     * Register a one-time event listener.
     *
     * The listener will be called only once, then automatically removed.
     *
     * @param string   $event    Event name to listen for.
     * @param callable $callback Function to execute when event fires.
     *
     * @example
     * ```php
     * $events->once('session.login', function($data) {
     *     metrics()->increment('first_login');
     * });
     * ```
     */
    public function once(string $event, callable $callback) : void
    {
        $this->events->once(event: $event, callback: $callback);
    }

    /**
     * Remove a specific event listener.
     *
     * @param string   $event    Event name.
     * @param callable $callback The callback to remove.
     */
    public function removeListener(string $event, callable $callback) : void
    {
        $this->events->removeListener(event: $event, callback: $callback);
    }

    // ----------------------------------------------------------------
    // 🔹 Event Dispatching
    // ----------------------------------------------------------------

    /**
     * Dispatch an event asynchronously.
     *
     * Listeners will be executed in the background if async dispatcher
     * is configured. Falls back to synchronous dispatch otherwise.
     *
     * @param string               $event Event name.
     * @param array<string, mixed> $data  Event payload data.
     */
    public function dispatchAsync(string $event, array $data = []) : void
    {
        if ($this->asyncDispatcher !== null) {
            $this->asyncDispatcher->dispatch(event: $event, data: $data);
        } else {
            // Fallback to synchronous dispatch
            $this->events->dispatch(event: $event, data: $data);
        }
    }

    /**
     * Dispatch an event synchronously.
     *
     * All registered listeners will be called immediately in sequence.
     *
     * @param string               $event Event name.
     * @param array<string, mixed> $data  Event payload data.
     */
    public function dispatch(string $event, array $data = []) : void
    {
        $this->events->dispatch(event: $event, data: $data);
    }

    // ----------------------------------------------------------------
    // 🔹 Lifecycle Management
    // ----------------------------------------------------------------

    /**
     * Enable event dispatching.
     *
     * Activates the event system.
     */
    public function enable() : void
    {
        $this->events->boot();
    }

    /**
     * Disable event dispatching.
     *
     * Stops event processing and clears all listeners.
     */
    public function disable() : void
    {
        $this->events->terminate();
    }

    /**
     * Check if event system is enabled.
     *
     * @return bool True if enabled, false otherwise.
     */
    public function isEnabled() : bool
    {
        return $this->events->isEnabled();
    }

    /**
     * Get the feature name.
     *
     * @return string The events feature identifier.
     */
    public function getName() : string
    {
        return $this->events->getName();
    }

    // ----------------------------------------------------------------
    // 🔹 Internal Access
    // ----------------------------------------------------------------

    /**
     * Get the underlying Events instance.
     *
     * Provides direct access to the event dispatcher for advanced operations.
     *
     * @return Events The events instance.
     */
    public function events() : Events
    {
        return $this->events;
    }

    /**
     * Get the async event dispatcher if configured.
     *
     * @return AsyncEventDispatcher|null The async dispatcher or null.
     */
    public function asyncDispatcher() : AsyncEventDispatcher|null
    {
        return $this->asyncDispatcher;
    }
}
