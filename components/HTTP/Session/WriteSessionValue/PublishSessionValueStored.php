<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\WriteSessionValue;

final class PublishSessionValueStored
{
    private $events;

    public function __construct($events = null)
    {
        $this->events = $events;
    }

    public function handle(string $key) : void
    {
        $this->events?->dispatch('session.stored', ['key' => $key]);
    }
}