<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\TerminateSession;

final class PublishSessionTerminated
{
    private $events;

    public function __construct($events = null)
    {
        $this->events = $events;
    }

    public function handle(string $reason) : void
    {
        $this->events?->dispatch('session.terminated', ['reason' => $reason]);
    }
}