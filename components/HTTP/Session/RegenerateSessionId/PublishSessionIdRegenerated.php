<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\RegenerateSessionId;

final class PublishSessionIdRegenerated
{
    private $events;

    public function __construct($events = null)
    {
        $this->events = $events;
    }

    public function handle(string $newId) : void
    {
        $this->events?->dispatch('session.id_regenerated', ['new_id' => $newId]);
    }
}