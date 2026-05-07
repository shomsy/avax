<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\Flows\PublishEvent;

use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\EventBus;

final readonly class PublishEvent
{
    public function publish(EventBus $bus, object $event) : void
    {
        $bus->dispatch($event);
    }
}
