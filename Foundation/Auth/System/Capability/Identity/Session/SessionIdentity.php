<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Identity\Session;

use Avax\Auth\System\Capability\Session\SessionRecord;
use Avax\Auth\System\Capability\Session\SessionRegistryInterface;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Diagnostics\NullAuditLog;
use Avax\Auth\System\Flow\Session\NativeSessionStore;
use Avax\Auth\System\Flow\Session\SessionStoreInterface;
use Avax\Auth\System\Foundation\Clock;
use DateTimeImmutable;

/**
 * Standard implementation of session-based identity storage within the Auth System.
 */
final class SessionIdentity implements SessionIdentityInterface
{
    public function __construct(
        private SessionStoreInterface         $store = new NativeSessionStore(),
        private Clock                         $clock = new Clock(),
        private AuditLogInterface             $auditLog = new NullAuditLog(),
        private SessionLifetime               $lifetime = new SessionLifetime(),
        private SessionRegistryInterface|null $sessionRegistry = null,
        private string                        $sessionKey = 'auth_user_id',
        private string                        $mfaVerifiedAtKey = 'auth_mfa_verified_at',
        private string                        $issuedAtKey = 'auth_session_issued_at',
        private string                        $lastSeenAtKey = 'auth_session_last_seen_at'
    ) {}

    public function issue(int $userId, DateTimeImmutable|null $mfaVerifiedAt = null) : string|null
    {
        $sessionId = $this->store->regenerate();
        $now       = $this->clock->now();

        $this->store->put($this->sessionKey, $userId);
        $this->store->put($this->mfaVerifiedAtKey, $mfaVerifiedAt?->format(DATE_ATOM));
        $this->store->put($this->issuedAtKey, $now->format(DATE_ATOM));
        $this->store->put($this->lastSeenAtKey, $now->format(DATE_ATOM));

        if ($sessionId !== '') {
            $this->sessionRegistry?->track(new SessionRecord(
                sessionId        : $sessionId,
                userId           : new UserId($userId),
                createdAt        : $now,
                lastSeenAt       : $now,
                idleExpiresAt    : $now->modify("+{$this->lifetime->idleTimeoutSeconds} seconds"),
                absoluteExpiresAt: $now->modify("+{$this->lifetime->absoluteTimeoutSeconds} seconds")
            ));
        }

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

    public function captureCurrentSession(string|null $ipAddress = null, string|null $userAgent = null) : void
    {
        $sessionId = $this->currentSessionId();

        if ($sessionId === null || $this->sessionRegistry === null) {
            return;
        }

        $record = $this->sessionRegistry->find($sessionId);

        if ($record === null) {
            $userId = $this->resolveUserId();

            if ($userId === null) {
                return;
            }

            $now = $this->clock->now();
            $this->sessionRegistry->track(new SessionRecord(
                sessionId        : $sessionId,
                userId           : new UserId($userId),
                createdAt        : $now,
                lastSeenAt       : $now,
                idleExpiresAt    : $now->modify("+{$this->lifetime->idleTimeoutSeconds} seconds"),
                absoluteExpiresAt: $now->modify("+{$this->lifetime->absoluteTimeoutSeconds} seconds"),
                ipCreated        : $ipAddress,
                userAgentCreated : $userAgent
            ));

            return;
        }

        $this->sessionRegistry->save($record->withClientMetadata($ipAddress, $userAgent));
    }

    public function resolveUserId() : int|null
    {
        if (! $this->isSessionActive()) {
            return null;
        }

        $storedUserId = $this->store->get($this->sessionKey);

        if (is_int($storedUserId)) {
            $this->touch();

            return $storedUserId;
        }

        if (is_string($storedUserId) && ctype_digit($storedUserId)) {
            $this->touch();

            return (int) $storedUserId;
        }

        $this->expire('invalid_user');

        return null;
    }

    public function resolveMfaVerifiedAt() : DateTimeImmutable|null
    {
        if (! $this->isSessionActive()) {
            return null;
        }

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

    private function isSessionActive() : bool
    {
        $sessionId = $this->currentSessionId();

        if ($sessionId !== null && $this->sessionRegistry !== null) {
            $record = $this->sessionRegistry->find($sessionId);

            if ($record !== null) {
                $now = $this->clock->now();

                if ($record->isRevoked()) {
                    $this->clear();

                    return false;
                }

                if (! $record->isActiveAt($now)) {
                    $reason = $record->revokeReason
                        ?? ($record->absoluteExpiresAt <= $now ? 'absolute_timeout' : 'idle_timeout');
                    $this->expire($reason);

                    return false;
                }
            }
        }

        $issuedAt = $this->readDate($this->issuedAtKey);
        $lastSeenAt = $this->readDate($this->lastSeenAtKey);

        if ($issuedAt === null || $lastSeenAt === null) {
            if ($this->store->get($this->sessionKey) !== null) {
                $this->expire('missing_metadata');
            }

            return false;
        }

        $now = $this->clock->now();

        if ($issuedAt->modify("+{$this->lifetime->absoluteTimeoutSeconds} seconds") <= $now) {
            $this->expire('absolute_timeout');

            return false;
        }

        if ($lastSeenAt->modify("+{$this->lifetime->idleTimeoutSeconds} seconds") <= $now) {
            $this->expire('idle_timeout');

            return false;
        }

        return true;
    }

    private function touch() : void
    {
        $now = $this->clock->now();
        $this->store->put($this->lastSeenAtKey, $now->format(DATE_ATOM));

        $sessionId = $this->currentSessionId();

        if ($sessionId === null || $this->sessionRegistry === null) {
            return;
        }

        $record = $this->sessionRegistry->find($sessionId);

        if ($record === null || $record->isRevoked()) {
            return;
        }

        $this->sessionRegistry->save($record->withTouch($now, $this->lifetime->idleTimeoutSeconds));
    }

    private function readDate(string $key) : DateTimeImmutable|null
    {
        $stored = $this->store->get($key);

        if (! is_string($stored) || $stored === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($stored);
        } catch (\Exception) {
            return null;
        }
    }

    private function expire(string $reason) : void
    {
        $storedUserId = $this->store->get($this->sessionKey);
        $sessionId    = $this->currentSessionId();
        $userId       = is_int($storedUserId)
            ? $storedUserId
            : (is_string($storedUserId) && ctype_digit($storedUserId) ? (int) $storedUserId : null);

        if ($sessionId !== null) {
            $this->sessionRegistry?->revoke($sessionId, $this->clock->now(), $reason);
        }

        $this->auditLog->record(new AuditEvent(
            name      : 'auth.session.expired',
            occurredAt: $this->clock->now(),
            context   : [
                'user_id' => $userId,
                'reason'  => $reason,
            ]
        ));
        $this->clear();
    }
}
