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
use Exception;
use SensitiveParameter;

/**
 * Standard implementation of session-based identity storage within the Auth System.
 */
final class SessionIdentity implements SessionIdentityInterface
{
    public function __construct(
        private SessionStoreInterface                               $store = new NativeSessionStore(),
        private Clock                                               $clock = new Clock(),
        private AuditLogInterface                                   $auditLog = new NullAuditLog(),
        private SessionLifetime                                     $lifetime = new SessionLifetime(),
        #[SensitiveParameter] private SessionRegistryInterface|null $sessionRegistry = null,
        #[SensitiveParameter] private string                        $sessionKey = 'auth_user_id',
        private string                                              $mfaVerifiedAtKey = 'auth_mfa_verified_at',
        private string                                              $phishingResistantKey = 'auth_phishing_resistant',
        private string                                              $issuedAtKey = 'auth_session_issued_at',
        private string                                              $lastSeenAtKey = 'auth_session_last_seen_at'
    ) {}

    /**
     * @throws \DateMalformedStringException
     */
    public function issue(
        int $userId,
        DateTimeImmutable|null $mfaVerifiedAt = null,
        bool $phishingResistant = false
    ) : string|null
    {
        $sessionId = $this->store->regenerate();
        $now       = $this->clock->now();

        $this->store->put(key: $this->sessionKey, value: $userId);
        $this->store->put(key: $this->mfaVerifiedAtKey, value: $mfaVerifiedAt?->format(format: DATE_ATOM));
        $this->store->put(key: $this->phishingResistantKey, value: $phishingResistant ? '1' : '0');
        $this->store->put(key: $this->issuedAtKey, value: $now->format(format: DATE_ATOM));
        $this->store->put(key: $this->lastSeenAtKey, value: $now->format(format: DATE_ATOM));

        if ($sessionId !== '') {
            $this->sessionRegistry?->track(record: new SessionRecord(
                sessionId        : $sessionId,
                userId           : new UserId(value: $userId),
                createdAt        : $now,
                lastSeenAt       : $now,
                idleExpiresAt    : $now->modify(modifier: "+{$this->lifetime->idleTimeoutSeconds} seconds"),
                absoluteExpiresAt: $now->modify(modifier: "+{$this->lifetime->absoluteTimeoutSeconds} seconds")
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

    /**
     * @throws \DateMalformedStringException
     */
    public function captureCurrentSession(#[SensitiveParameter] string|null $ipAddress = null, string|null $userAgent = null) : void
    {
        $sessionId = $this->currentSessionId();

        if ($sessionId === null || $this->sessionRegistry === null) {
            return;
        }

        $record = $this->sessionRegistry->find(sessionId: $sessionId);

        if ($record === null) {
            $userId = $this->resolveUserId();

            if ($userId === null) {
                return;
            }

            $now = $this->clock->now();
            $this->sessionRegistry->track(record: new SessionRecord(
                sessionId        : $sessionId,
                userId           : new UserId(value: $userId),
                createdAt        : $now,
                lastSeenAt       : $now,
                idleExpiresAt    : $now->modify(modifier: "+{$this->lifetime->idleTimeoutSeconds} seconds"),
                absoluteExpiresAt: $now->modify(modifier: "+{$this->lifetime->absoluteTimeoutSeconds} seconds"),
                ipCreated        : $ipAddress,
                userAgentCreated : $userAgent
            ));

            return;
        }

        $this->sessionRegistry->save(record: $record->withClientMetadata(ipAddress: $ipAddress, userAgent: $userAgent));
    }

    public function resolveUserId() : int|null
    {
        if (! $this->isSessionActive()) {
            return null;
        }

        $storedUserId = $this->store->get(key: $this->sessionKey);

        if (is_int($storedUserId)) {
            $this->touch();

            return $storedUserId;
        }

        if (is_string($storedUserId) && ctype_digit($storedUserId)) {
            $this->touch();

            return (int) $storedUserId;
        }

        $this->expire(reason: 'invalid_user');

        return null;
    }

    public function resolveMfaVerifiedAt() : DateTimeImmutable|null
    {
        if (! $this->isSessionActive()) {
            return null;
        }

        $stored = $this->store->get(key: $this->mfaVerifiedAtKey);

        if (! is_string($stored) || $stored === '') {
            return null;
        }

        try {
            return new DateTimeImmutable(datetime: $stored);
        } catch (Exception) {
            return null;
        }
    }

    public function resolvePhishingResistant() : bool
    {
        if (! $this->isSessionActive()) {
            return false;
        }

        return $this->store->get(key: $this->phishingResistantKey) === '1';
    }

    /**
     * @throws \DateMalformedStringException
     */
    private function isSessionActive() : bool
    {
        $sessionId = $this->currentSessionId();

        if ($sessionId !== null && $this->sessionRegistry !== null) {
            $record = $this->sessionRegistry->find(sessionId: $sessionId);

            if ($record !== null) {
                $now = $this->clock->now();

                if ($record->isRevoked()) {
                    $this->clear();

                    return false;
                }

                if (! $record->isActiveAt(moment: $now)) {
                    $reason = $record->revokeReason
                        ?? ($record->absoluteExpiresAt <= $now ? 'absolute_timeout' : 'idle_timeout');
                    $this->expire(reason: $reason);

                    return false;
                }
            }
        }

        $issuedAt = $this->readDate(key: $this->issuedAtKey);
        $lastSeenAt = $this->readDate(key: $this->lastSeenAtKey);

        if ($issuedAt === null || $lastSeenAt === null) {
            if ($this->store->get(key: $this->sessionKey) !== null) {
                $this->expire(reason: 'missing_metadata');
            }

            return false;
        }

        $now = $this->clock->now();

        if ($issuedAt->modify(modifier: "+{$this->lifetime->absoluteTimeoutSeconds} seconds") <= $now) {
            $this->expire(reason: 'absolute_timeout');

            return false;
        }

        if ($lastSeenAt->modify(modifier: "+{$this->lifetime->idleTimeoutSeconds} seconds") <= $now) {
            $this->expire(reason: 'idle_timeout');

            return false;
        }

        return true;
    }

    private function touch() : void
    {
        $now = $this->clock->now();
        $this->store->put(key: $this->lastSeenAtKey, value: $now->format(format: DATE_ATOM));

        $sessionId = $this->currentSessionId();

        if ($sessionId === null || $this->sessionRegistry === null) {
            return;
        }

        $record = $this->sessionRegistry->find(sessionId: $sessionId);

        if ($record === null || $record->isRevoked()) {
            return;
        }

        $this->sessionRegistry->save(record: $record->withTouch(lastSeenAt: $now, idleTimeoutSeconds: $this->lifetime->idleTimeoutSeconds));
    }

    private function readDate(string $key) : DateTimeImmutable|null
    {
        $stored = $this->store->get(key: $key);

        if (! is_string($stored) || $stored === '') {
            return null;
        }

        try {
            return new DateTimeImmutable(datetime: $stored);
        } catch (Exception) {
            return null;
        }
    }

    private function expire(string $reason) : void
    {
        $storedUserId = $this->store->get(key: $this->sessionKey);
        $sessionId    = $this->currentSessionId();
        $userId       = is_int($storedUserId)
            ? $storedUserId
            : (is_string($storedUserId) && ctype_digit($storedUserId) ? (int) $storedUserId : null);

        if ($sessionId !== null) {
            $this->sessionRegistry?->revoke(sessionId: $sessionId, revokedAt: $this->clock->now(), reason: $reason);
        }

        $this->auditLog->record(event: new AuditEvent(
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
