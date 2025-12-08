<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features;

use Avax\HTTP\Session\Contracts\FeatureInterface;

/**
 * Events - Simple Event Dispatcher
 *
 * Provides basic pub/sub functionality for session events.
 *
 * Events:
 * - stored: When a value is stored
 * - retrieved: When a value is retrieved
 * - deleted: When a value is deleted
 * - flushed: When session is flushed
 *
 * @example
 *   $events->listen('stored', function($data) {
 *       logger()->info('Value stored', $data);
 *   });
 *
 *   // One-time listener
 *   $events->once('stored', function($data) {
 *       metrics()->increment('session.first_write');
 *   });
 *
 * @package Avax\HTTP\Session
 */
final class Events implements FeatureInterface
{
    /**
     * @var array<string, array<callable>> Event listeners
     */
    private array $listeners = [];

    /**
     * @var bool Feature enabled state
     */
    private bool $enabled = true;

    /**
     * Register a one-time event listener.
     *
     * Listener will be automatically removed after first dispatch.
     *
     * @param string   $event    The event name.
     * @param callable $callback The callback.
     *
     * @return void
     */
    public function once(string $event, callable $callback) : void
    {
        $wrapper = function ($data) use ($event, $callback, &$wrapper) {
            $callback($data);
            $this->removeListener($event, $wrapper);
        };

        $this->listen($event, $wrapper);
    }

    /**
     * Remove an event listener.
     *
     * @param string   $event    The event name.
     * @param callable $callback The callback to remove.
     *
     * @return void
     */
    public function removeListener(string $event, callable $callback) : void
    {
        if (! isset($this->listeners[$event])) {
            return;
        }

        $this->listeners[$event] = array_filter(
            $this->listeners[$event],
            fn($listener) => $listener !== $callback
        );
    }

    /**
     * Register an event listener.
     *
     * @param string   $event    The event name.
     * @param callable $callback The callback.
     *
     * @return void
     */
    public function listen(string $event, callable $callback) : void
    {
        $this->listeners[$event][] = $callback;
    }

    /**
     * Dispatch an event to all registered listeners.
     *
     * @param string               $event The event name.
     * @param array<string, mixed> $data  Event data.
     *
     * @return void
     */
    public function dispatch(string $event, array $data = []) : void
    {
        if (! isset($this->listeners[$event])) {
            return;
        }

        foreach ($this->listeners[$event] as $callback) {
            $callback($data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function boot() : void
    {
        // Events are ready on construction
        $this->enabled = true;
    }

    /**
     * {@inheritdoc}
     */
    public function terminate() : void
    {
        // Clear all listeners on termination
        $this->listeners = [];
        $this->enabled   = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getName() : string
    {
        return 'events';
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled() : bool
    {
        return $this->enabled;
    }
}
