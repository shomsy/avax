<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionRegistry;

final class SessionRegistry
{
    private array $registry = [];

    public function register(string $sessionId, array $metadata = []) : void
    {
        $this->registry[$sessionId] = [
            'registered_at' => time(),
            'last_activity' => time(),
            'metadata'      => $metadata,
        ];
    }

    public function has(string $sessionId) : bool
    {
        return isset($this->registry[$sessionId]);
    }

    public function get(string $sessionId) : ?array
    {
        return $this->registry[$sessionId] ?? null;
    }

    public function all() : array
    {
        return $this->registry;
    }

    public function count() : int
    {
        return count($this->registry);
    }

    public function revoke(string $sessionId) : bool
    {
        return $this->unregister($sessionId);
    }

    public function unregister(string $sessionId) : bool
    {
        if (! isset($this->registry[$sessionId])) {
            return false;
        }

        unset($this->registry[$sessionId]);

        return true;
    }

    public function refreshActivity(string $sessionId) : void
    {
        if (isset($this->registry[$sessionId])) {
            $this->registry[$sessionId]['last_activity'] = time();
        }
    }
}