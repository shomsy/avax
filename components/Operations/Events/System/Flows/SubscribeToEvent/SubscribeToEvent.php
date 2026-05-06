<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Flows\SubscribeToEvent;

use Avax\Components\Operations\Events\System\Capabilities\Registry\ListenerRegistry;

final readonly class SubscribeToEvent
{
    public function __construct(private ListenerRegistry $listenerRegistry)
    {
    }

    public function execute(string $event, callable $listener, int $priority = 0): void
    {
        $this->listenerRegistry->subscribe(event: $event, listener: $listener, priority: $priority);
    }
}
