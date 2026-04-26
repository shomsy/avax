<?php

declare(strict_types=1);

namespace components\HTTP\Session\SessionEvents;

final class PublishSessionEvent
{
    private $bus;

    public function __construct($bus)
    {
        $this->bus = $bus;
    }

    public function handle(string $event, array $data = []) : void
    {
        $this->bus->dispatch($event, $data);
    }
}