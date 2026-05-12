<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Capabilities\ListenerProvider;

use Avax\Components\Operations\Events\System\Capabilities\Registry\ListenerRegistry;

/**
 * Provides listeners for a given event class.
 *
 * Thin capability that wraps ListenerRegistry with a PSR-14-compatible
 * listenersFor() signature. Returns callable listeners ready for dispatch.
 */
final readonly class ListenerProvider
{
    public function __construct(
        private ListenerRegistry $registry,
    ) {
    }

    /**
     * @return array<int, callable> Sorted listeners for the event class.
     */
    public function listenersFor(object $event): array
    {
        return $this->registry->getListenersFor($event::class);
    }
}
