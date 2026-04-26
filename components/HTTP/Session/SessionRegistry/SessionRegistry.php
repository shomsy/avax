<?php

declare(strict_types=1);

namespace components\HTTP\Session\SessionRegistry;

use SensitiveParameter;

final class SessionRegistry
{
    private array $registry = [];

    public function register(#[SensitiveParameter] string $sessionId, array $metadata = []) : void
    {
        $this->registry[$sessionId] = [
            'registered_at' => time(),
            'last_activity' => time(),
            'metadata'      => $metadata,
        ];
    }

    public function has(#[SensitiveParameter] string $sessionId) : bool
    {
        return isset($this->registry[$sessionId]);
    }

    public function get(#[SensitiveParameter] string $sessionId) : array|null
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

    public function revoke(#[SensitiveParameter] string $sessionId) : bool
    {
        return $this->unregister(sessionId: $sessionId);
    }

    public function unregister(#[SensitiveParameter] string $sessionId) : bool
    {
        if (! isset($this->registry[$sessionId])) {
            return false;
        }

        unset($this->registry[$sessionId]);

        return true;
    }

    public function refreshActivity(#[SensitiveParameter] string $sessionId) : void
    {
        if (isset($this->registry[$sessionId])) {
            $this->registry[$sessionId]['last_activity'] = time();
        }
    }
}