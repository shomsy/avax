<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\SessionRegistry;

final class RevokeAllActorSessions
{
    private $registry;

    public function __construct($registry)
    {
        $this->registry = $registry;
    }

    public function handle(string $actorId) : int
    {
        $revoked = 0;
        $all     = $this->registry->all();

        foreach ($all as $sessionId => $data) {
            if (($data['metadata']['actor_id'] ?? null) === $actorId) {
                if ($this->registry->unregister($sessionId)) {
                    $revoked++;
                }
            }
        }

        return $revoked;
    }
}