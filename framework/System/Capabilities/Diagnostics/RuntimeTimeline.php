<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Diagnostics;

final class RuntimeTimeline
{
    /**
     * @var list<RuntimeEvent>
     */
    private array $events = [];

    public function record(RuntimeEvent $event) : void
    {
        $this->events[] = $event;
    }

    /**
     * @return list<RuntimeEvent>
     */
    public function events() : array
    {
        return $this->events;
    }

    public function clear() : void
    {
        $this->events = [];
    }
}
