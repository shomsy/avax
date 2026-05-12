<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Capabilities\Psr14;

use Avax\Components\Operations\Events\System\Capabilities\InvokeEventListener\InvokeEventListener;
use Avax\Components\Operations\Events\System\Capabilities\ResolveEventListeners\ResolveEventListeners;
use Avax\Components\Operations\Events\System\Foundation\CompiledListenerRegistry;
use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * PSR-14 ListenerProviderInterface adapter.
 *
 * Delegates PSR-14 getListenersForEvent() calls to the AvaX CompiledListenerRegistry.
 * Returns callable listeners ready for PSR-14 dispatch.
 */
final class Psr14ListenerProviderAdapter implements ListenerProviderInterface
{
    public function __construct(
        private CompiledListenerRegistry $registry,
        private ResolveEventListeners $resolver = new ResolveEventListeners(),
    ) {
    }

    /**
     * Get all listeners for the given event.
     *
     * @param object $event The event object.
     * @return iterable<callable> Listeners ready to be invoked.
     */
    public function getListenersForEvent(object $event): iterable
    {
        $compiled = $this->registry->getListenersFor($event);
        $invoker = new InvokeEventListener();

        foreach ($compiled as $listener) {
            $callable = $this->resolver->resolve($listener);

            yield static function (object $e) use ($callable, $invoker): void {
                $invoker->invoke($callable, $e);
            };
        }
    }
}
