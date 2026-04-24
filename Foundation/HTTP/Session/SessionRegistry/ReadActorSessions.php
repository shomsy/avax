<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionRegistry;

final class ReadActorSessions
{
    private $registry;

    public function __construct($registry)
    {
        $this->registry = $registry;
    }

    public function handle(string $actorId) : array
    {
        $sessions = [];
        $all      = $this->registry->all();

        foreach ($all as $sessionId => $data) {
            if (($data['metadata']['actor_id'] ?? null) === $actorId) {
                $sessions[$sessionId] = $data;
            }
        }

        return $sessions;
    }
}