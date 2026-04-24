<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\BindSessionActor;

final class PublishSessionActorBound
{
    private $events;

    public function __construct($events = null)
    {
        $this->events = $events;
    }

    public function handle(string $actorId, array $data) : void
    {
        $this->events?->dispatch('session.actor_bound', ['actor_id' => $actorId, 'data' => $data]);
    }
}