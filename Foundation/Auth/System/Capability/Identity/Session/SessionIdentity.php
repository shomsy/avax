<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Identity\Session;

use Avax\Auth\System\Flow\Session\NativeSessionStore;
use Avax\Auth\System\Flow\Session\SessionStoreInterface;
use DateTimeImmutable;

/**
 * Standard implementation of session-based identity storage within the Auth System.
 */
final class SessionIdentity implements SessionIdentityInterface
{
    public function __construct(
        private SessionStoreInterface $store = new NativeSessionStore(),
        private string                $sessionKey = 'auth_user_id',
        private string                $mfaVerifiedAtKey = 'auth_mfa_verified_at'
    ) {}

    public function issue(int $userId, DateTimeImmutable|null $mfaVerifiedAt = null) : string|null
    {
        $sessionId = $this->store->regenerate();
        $this->store->put($this->sessionKey, $userId);
        $this->store->put($this->mfaVerifiedAtKey, $mfaVerifiedAt?->format(DATE_ATOM));

        return $sessionId !== '' ? $sessionId : null;
    }

    public function clear() : void
    {
        $this->store->invalidate();
    }

    public function currentSessionId() : string|null
    {
        return $this->store->id();
    }

    public function resolveUserId() : int|null
    {
        $storedUserId = $this->store->get($this->sessionKey);

        if (is_int($storedUserId)) {
            return $storedUserId;
        }

        if (is_string($storedUserId) && ctype_digit($storedUserId)) {
            return (int) $storedUserId;
        }

        return null;
    }

    public function resolveMfaVerifiedAt() : DateTimeImmutable|null
    {
        $stored = $this->store->get($this->mfaVerifiedAtKey);

        if (! is_string($stored) || $stored === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($stored);
        } catch (\Exception) {
            return null;
        }
    }
}
