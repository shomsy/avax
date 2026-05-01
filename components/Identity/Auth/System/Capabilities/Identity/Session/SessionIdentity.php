<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Session;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\NullAuditLog;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRecord;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\NativeSessionStore;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\SessionStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use DateMalformedStringException;
use DateTimeImmutable;
use Exception;
use SensitiveParameter;

/**
 * Standard implementation of session-based identity storage within the Auth System.
 */
final class SessionIdentity implements SessionIdentityInterface
{
    private string $issuedAtKey = 'auth_session_issued_at';

    private string $phishingResistantKey = 'auth_phishing_resistant';

    private string $mfaVerifiedAtKey = 'auth_mfa_verified_at';

    private string $sessionKey = 'auth_user_id';

    private readonly SessionLifetime $lifetime;

    private readonly AuditLogInterface $auditLog;

    private readonly Clock $clock;

    private readonly SessionStoreInterface $store;

    public function __construct(
        SessionStoreInterface $store = null,
        Clock                 $clock = null,
        AuditLogInterface     $auditLog = null,
        SessionLifetime       $lifetime = null,
        #[SensitiveParameter]
        private readonly ?SessionRegistryInterface $sessionRegistry = null,
        #[SensitiveParameter]
        string                $sessionKey = null,
        string                $mfaVerifiedAtKey = null,
        string                $phishingResistantKey = null,
        string                $issuedAtKey = null,
        private readonly string $lastSeenAtKey = 'auth_session_last_seen_at',
    ) {
        $store                  ??= new NativeSessionStore();
        $clock                  ??= new Clock();
        $auditLog               ??= new NullAuditLog();
        $lifetime               ??= new SessionLifetime();
        $sessionKey             ??= 'auth_user_id';
        $mfaVerifiedAtKey       ??= 'auth_mfa_verified_at';
        $phishingResistantKey ??= 'auth_phishing_resistant';
        $issuedAtKey            ??= 'auth_session_issued_at';
        $this->store            = $store;
        $this->clock            = $clock;
        $this->auditLog         = $auditLog;
        $this->lifetime         = $lifetime;
        $this->sessionKey       = $sessionKey;
        $this->mfaVerifiedAtKey = $mfaVerifiedAtKey;
        $this->phishingResistantKey = $phishingResistantKey;
        $this->issuedAtKey      = $issuedAtKey;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function issue(
        int $userId,
        DateTimeImmutable $mfaVerifiedAt = null,
        bool $phishingResistant = false,
    ): ?string {
        $sessionId = $this->store->regenerate();
        $now = $this->clock->now();

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
                absoluteExpiresAt: $now->modify(modifier: "+{$this->lifetime->absoluteTimeoutSeconds} seconds"),
            ));
        }

        return $sessionId !== '' ? $sessionId : null;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function captureCurrentSession(#[SensitiveParameter] string $ipAddress = null, string $userAgent = null) : void
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
                userAgentCreated : $userAgent,
            ));

            return;
        }

        $this->sessionRegistry->save(record: $record->withClientMetadata(ipAddress: $ipAddress, userAgent: $userAgent));
    }

    public function currentSessionId(): ?string
    {
        return $this->store->id();
    }

    /**
     * @throws DateMalformedStringException
     */
    public function resolveUserId(): ?int
    {
        if (! $this->isSessionActive()) {
            return null;
        }

        $storedUserId = $this->store->get(key: $this->sessionKey);

        if (is_int(value: $storedUserId)) {
            $this->touch();

            return $storedUserId;
        }

        if (is_string(value: $storedUserId) && ctype_digit(text: $storedUserId)) {
            $this->touch();

            return (int) $storedUserId;
        }

        $this->expire(reason: 'invalid_user');

        return null;
    }

    /**
     * @throws DateMalformedStringException
     */
    private function isSessionActive(): bool
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

    public function clear(): void
    {
        $this->store->invalidate();
    }

    private function expire(string $reason): void
    {
        $storedUserId = $this->store->get(key: $this->sessionKey);
        $sessionId = $this->currentSessionId();
        $userId = is_int(value: $storedUserId)
            ? $storedUserId
            : (is_string(value: $storedUserId) && ctype_digit(text: $storedUserId) ? (int) $storedUserId : null);

        if ($sessionId !== null) {
            $this->sessionRegistry?->revoke(sessionId: $sessionId, revokedAt: $this->clock->now(), reason: $reason);
        }

        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.session.expired',
            occurredAt: $this->clock->now(),
            context   : [
                'user_id' => $userId,
                'reason' => $reason,
            ],
        ));
        $this->clear();
    }

    private function readDate(string $key): ?DateTimeImmutable
    {
        $stored = $this->store->get(key: $key);

        if (! is_string(value: $stored) || $stored === '') {
            return null;
        }

        try {
            return new DateTimeImmutable(datetime: $stored);
        } catch (Exception) {
            return null;
        }
    }

    /**
     * @throws DateMalformedStringException
     */
    private function touch(): void
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

    /**
     * @throws DateMalformedStringException
     */
    public function resolveMfaVerifiedAt(): ?DateTimeImmutable
    {
        if (! $this->isSessionActive()) {
            return null;
        }

        $stored = $this->store->get(key: $this->mfaVerifiedAtKey);

        if (! is_string(value: $stored) || $stored === '') {
            return null;
        }

        try {
            return new DateTimeImmutable(datetime: $stored);
        } catch (Exception) {
            return null;
        }
    }

    /**
     * @throws DateMalformedStringException
     */
    public function resolvePhishingResistant(): bool
    {
        if (! $this->isSessionActive()) {
            return false;
        }

        return $this->store->get(key: $this->phishingResistantKey) === '1';
    }
}
