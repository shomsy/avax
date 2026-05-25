<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Store;

use Avax\Components\Identity\Auth\System\Foundation\Ids\SessionId;
use Avax\Components\Identity\Auth\System\Foundation\State\ResettableIdentityState;

/**
 * InMemorySessionStore — in-memory session persistence for runtime.
 *
 * Adapted from the enterprise reference package.
 * Implements ResettableIdentityState for long-lived worker safety.
 */
final class InMemorySessionStore implements SessionStore, ResettableIdentityState
{
    /** @var array<string, IdentitySession> */
    private array $sessions = [];

    public function find(SessionId $sessionId): IdentitySession|null
    {
        return $this->sessions[$sessionId->value] ?? null;
    }

    public function save(IdentitySession $session): void
    {
        $this->sessions[$session->sessionId()->value] = $session;
    }

    public function remove(SessionId $sessionId): void
    {
        unset($this->sessions[$sessionId->value]);
    }

    public function reset(): void
    {
        $this->sessions = [];
    }
}
