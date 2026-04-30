<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Diagnostics;

final class RuntimeTimeline
{
    private array $events = [];

    public function record(RuntimeEvent $event) : void
    {
        $this->events[] = $event;
    }

    public function events() : array
    {
        return $this->events;
    }

    public function clear() : void
    {
        $this->events = [];
    }
}
