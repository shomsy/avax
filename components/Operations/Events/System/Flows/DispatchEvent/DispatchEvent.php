<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Flows\DispatchEvent;

use Avax\Components\Operations\Events\System\PublicSurface\EventsInterface;

final readonly class DispatchEvent
{
    public function __construct(
        private EventsInterface $events,
    ) {}

    public function execute(string|object $event, mixed $data = null) : void
    {
        $this->events->dispatch($event, $data);
    }
}
