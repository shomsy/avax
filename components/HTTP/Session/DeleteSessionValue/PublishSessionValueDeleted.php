<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\DeleteSessionValue;

final class PublishSessionValueDeleted
{
    private $events;

    public function __construct($events = null)
    {
        $this->events = $events;
    }

    public function handle(string $key) : void
    {
        $this->events?->dispatch('session.deleted', ['key' => $key]);
    }
}