<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Capabilities\Psr14;

use Avax\Components\Operations\Events\System\Foundation\EventEmitter;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * PSR-14 EventDispatcherInterface adapter.
 *
 * Delegates PSR-14 dispatch() calls to the AvaX EventEmitter.
 * This is an interop layer — AvaX users should use emit() and onEvent(),
 * not PSR-14 plumbing.
 */
final readonly class Psr14EventDispatcherAdapter implements EventDispatcherInterface
{
    public function __construct(
        private EventEmitter $avaxEmitter,
    ) {
    }

    /**
     * Dispatch the event to all registered listeners.
     *
     * @param object $event The event object.
     * @return object The same event object after listener invocation.
     */
    public function dispatch(object $event): object
    {
        return $this->avaxEmitter->emit($event);
    }
}
