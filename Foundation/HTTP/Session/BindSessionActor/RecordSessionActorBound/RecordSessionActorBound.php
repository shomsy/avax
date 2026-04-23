<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\BindSessionActor;

final class RecordSessionActorBound
{
    private $audit;

    public function __construct($audit = null)
    {
        $this->audit = $audit;
    }

    public function handle(string $actorId, array $data) : void
    {
        $this->audit?->record('session.actor_bound', ['actor_id' => $actorId]);
    }
}