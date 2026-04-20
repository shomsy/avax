<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Session;

use Avax\Auth\System\Capabilities\Session\SessionRecord;
use Avax\Auth\System\Capabilities\Session\SessionRegistryInterface;
use Avax\Auth\System\Capabilities\User\UserId;
use Avax\Auth\System\Flows\Diagnostics\AuditEvent;
use Avax\Auth\System\Flows\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flows\Diagnostics\NullAuditLog;
use Avax\Auth\System\Flows\Session\NativeSessionStore;
use Avax\Auth\System\Flows\Session\SessionStoreInterface;
use Avax\Auth\System\Foundation\Clock;
use DateMalformedStringException;
use DateTimeImmutable;
use Exception;
use SensitiveParameter;

/**
 * Standard implementation of session-based identity storage within the Auth System.
 */
final class SessionIdentity implements SessionIdentityInterface
{
    private string                        $lastSeenAtKey        = 'auth_session_last_seen_at';
    private string                        $issuedAtKey          = 'auth_session_issued_at';
    private string                        $phishingResistantKey = 'auth_phishing_resistant';
    private string                        $mfaVerifiedAtKey     = 'auth_mfa_verified_at';
    private string                        $sessionKey           = 'auth_user_id';
    private SessionRegistryInterface|null $sessionRegistry      = null;
    private SessionLifetime               $lifetime;
    private AuditLogInterface             $auditLog;
    private Clock                         $clock;
    private SessionStoreInterface         $store;

    public function __construct(
        SessionStoreInterface|null                          $store = null,
        Clock|null                                          $clock = null,
        AuditLogInterface|null                              $auditLog = null,
        SessionLifetime|null                                $lifetime = null,
        #[SensitiveParameter] SessionRegistryInterface|null $sessionRegistry = null,
        #[SensitiveParameter] string|null                   $sessionKey = null,
        string|null                                         $mfaVerifiedAtKey = null,
        string|null                                         $phishingResistantKey = null,
        string|null                                         $issuedAtKey = null,
        string                                              $lastSeenAtKey = 'auth_session_last_seen_at'
    )
    {
        $store                      ??= new NativeSessionStore();
        $clock                      ??= new Clock();
        $auditLog                   ??= new NullAuditLog();
        $lifetime                   ??= new SessionLifetime();
        $sessionKey                 ??= 'auth_user_id';
        $mfaVerifiedAtKey           ??= 'auth_mfa_verified_at';
        $phishingResistantKey       ??= 'auth_phishing_resistant';
        $issuedAtKey                ??= 'auth_session_issued_at';
        $this->store                = $store;
        $this->clock                = $clock;
        $this->auditLog             = $auditLog;
        $this->lifetime             = $lifetime;
        $this->sessionRegistry      = $sessionRegistry;
        $this->sessionKey           = $sessionKey;
        $this->mfaVerifiedAtKey     = $mfaVerifiedAtKey;
        $this->phishingResistantKey = $phishingResistantKey;
        $this->issuedAtKey          = $issuedAtKey;
        $this->lastSeenAtKey        = $lastSeenAtKey;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function issue(
        int                    $userId,
        DateTimeImmutable|null $mfaVerifiedAt = null,
        bool                   $phishingResistant = false
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

    /**
     * @throws DateMalformedStringException
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

    public function currentSessionId() : string|null
    {
        return $this->store->id();
    }

    /**
     * @throws DateMalformedStringException
     */
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

    /**
     * @throws DateMalformedStringException
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

        $issuedAt   = $this->readDate(key: $this->issuedAtKey);
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

    public function clear() : void
    {
        $this->store->invalidate();
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

    /**
     * @throws DateMalformedStringException
     */
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

    /**
     * @throws DateMalformedStringException
     */
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

    /**
     * @throws DateMalformedStringException
     */
    public function resolvePhishingResistant() : bool
    {
        if (! $this->isSessionActive()) {
            return false;
        }

        return $this->store->get(key: $this->phishingResistantKey) === '1';
    }
}
