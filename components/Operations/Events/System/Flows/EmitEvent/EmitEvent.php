<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Flows\EmitEvent;

use Avax\Components\Operations\Events\System\Foundation\EventEmitter;

/**
 * Flow: Emit an event object through the canonical event runtime.
 *
 * Usage:
 *   $emitter = new EventEmitter($compiledRegistry);
 *   $flow = new EmitEvent($emitter);
 *   $result = $flow->execute(new UserRegistered($userId));
 *   // $result === the same UserRegistered instance
 */
final readonly class EmitEvent
{
    public function __construct(
        private EventEmitter $emitter,
    ) {
    }

    /**
     * Dispatch the event and return it.
     *
     * @param object $event Plain event object. No EventInterface required.
     * @return object The same event object after listener invocation.
     */
    public function execute(object $event): object
    {
        return $this->emitter->emit($event);
    }
}
