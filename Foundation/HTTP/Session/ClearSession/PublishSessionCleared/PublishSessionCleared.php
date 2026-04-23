<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\ClearSession;

final class PublishSessionCleared
{
    private $events;

    public function __construct($events = null)
    {
        $this->events = $events;
    }

    public function handle() : void
    {
        $this->events?->dispatch('session.cleared');
    }
}