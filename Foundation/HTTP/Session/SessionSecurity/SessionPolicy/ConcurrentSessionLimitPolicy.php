<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity\SessionPolicy;

final class ConcurrentSessionLimitPolicy
{
    private int $limit;
    private     $registry;

    public function __construct(int $limit, $registry = null)
    {
        $this->limit    = $limit;
        $this->registry = $registry;
    }

    public function evaluate(array $context) : bool
    {
        if ($this->registry === null) {
            return true;
        }

        $actorId = $context['actor_id'] ?? null;
        if ($actorId === null) {
            return true;
        }

        $count = $this->registry->count($actorId);

        return $count <= $this->limit;
    }
}