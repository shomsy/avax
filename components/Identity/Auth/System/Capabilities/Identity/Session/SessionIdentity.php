<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Session;

use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\NullAuditLog;
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

    private readonly SessionLifetime $sessionLifetime;

    private readonly AuditLogInterface $auditLog;

    private readonly Clock $clock;

    private readonly SessionStoreInterface $sessionStore;

    public function __construct(SessionStoreInterface|null $sessionStore = null, Clock|null $clock = null, AuditLogInterface|null $auditLog = null, SessionLifetime|null $sessionLifetime = null,
        #[SensitiveParameter]
        private readonly ?SessionRegistryInterface $sessionRegistry = null,
        #[SensitiveParameter]
                                ?string                    $sessionKey = null, string|null $mfaVerifiedAtKey = null, string|null $phishingResistantKey = null, string|null $issuedAtKey = null,
        private readonly string                    $lastSeenAtKey = 'auth_session_last_seen_at',
    )
    {
        $sessionStore               ??= new NativeSessionStore();
        $clock                      ??= new Clock();
        $auditLog                   ??= new NullAuditLog();
        $sessionLifetime            ??= new SessionLifetime();
        $sessionKey                 ??= 'auth_user_id';
        $mfaVerifiedAtKey           ??= 'auth_mfa_verified_at';
        $phishingResistantKey       ??= 'auth_phishing_resistant';
        $issuedAtKey                ??= 'auth_session_issued_at';
        $this->sessionStore         = $sessionStore;
        $this->clock                = $clock;
        $this->auditLog             = $auditLog;
        $this->sessionLifetime      = $sessionLifetime;
        $this->sessionKey           = $sessionKey;
        $this->mfaVerifiedAtKey     = $mfaVerifiedAtKey;
        $this->phishingResistantKey = $phishingResistantKey;
        $this->issuedAtKey          = $issuedAtKey;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function issue(
        int $userId, DateTimeImmutable|null $mfaVerifiedAt = null,
        bool               $phishingResistant = false,
    ) : string|null
    {
        $sessionId = $this->sessionStore->regenerate();
        $now       = $this->clock->now();

        $this->sessionStore->put(key: $this->sessionKey, value: $userId);
        $this->sessionStore->put(key: $this->mfaVerifiedAtKey, value: $mfaVerifiedAt?->format(format: DATE_ATOM));
        $this->sessionStore->put(key: $this->phishingResistantKey, value: $phishingResistant ? '1' : '0');
        $this->sessionStore->put(key: $this->issuedAtKey, value: $now->format(format: DATE_ATOM));
        $this->sessionStore->put(key: $this->lastSeenAtKey, value: $now->format(format: DATE_ATOM));

        if ($sessionId !== '') {
            $this->sessionRegistry?->track(record: new SessionRecord(
                                                       sessionId        : $sessionId,
                                                       userId           : new UserId(value: $userId),
                                                       createdAt        : $now,
                                                       lastSeenAt       : $now,
                                                       idleExpiresAt    : $now->modify(modifier: sprintf('+%s seconds', $this->sessionLifetime->idleTimeoutSeconds)),
                                                       absoluteExpiresAt: $now->modify(modifier: sprintf('+%s seconds', $this->sessionLifetime->absoluteTimeoutSeconds)),
                                                   ));
        }

        return $sessionId !== '' ? $sessionId : null;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function captureCurrentSession(#[SensitiveParameter] ?string $ipAddress = null, string|null $userAgent = null) : void
    {
        $sessionId = $this->currentSessionId();

        if ($sessionId === null || ! $this->sessionRegistry instanceof SessionRegistryInterface) {
            return;
        }

        $record = $this->sessionRegistry->find(sessionId: $sessionId);

        if (! $record instanceof SessionRecord) {
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
                                                      idleExpiresAt    : $now->modify(modifier: sprintf('+%s seconds', $this->sessionLifetime->idleTimeoutSeconds)),
                                                      absoluteExpiresAt: $now->modify(modifier: sprintf('+%s seconds', $this->sessionLifetime->absoluteTimeoutSeconds)),
                                                      ipCreated        : $ipAddress,
                                                      userAgentCreated : $userAgent,
                                                  ));

            return;
        }

        $this->sessionRegistry->save(record: $record->withClientMetadata(ipAddress: $ipAddress, userAgent: $userAgent));
    }

    public function currentSessionId() : string|null
    {
        return $this->sessionStore->id();
    }

    /**
     * @throws DateMalformedStringException
     */
    public function resolveUserId() : int|null
    {
        if (! $this->isSessionActive()) {
            return null;
        }

        $storedUserId = $this->sessionStore->get(key: $this->sessionKey);

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
    private function isSessionActive() : bool
    {
        $sessionId = $this->currentSessionId();

        if ($sessionId !== null && $this->sessionRegistry instanceof SessionRegistryInterface) {
            $record = $this->sessionRegistry->find(sessionId: $sessionId);

            if ($record instanceof SessionRecord) {
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

        if (! $issuedAt instanceof DateTimeImmutable || ! $lastSeenAt instanceof DateTimeImmutable) {
            if ($this->sessionStore->get(key: $this->sessionKey) !== null) {
                $this->expire(reason: 'missing_metadata');
            }

            return false;
        }

        $now = $this->clock->now();

        if ($issuedAt->modify(modifier: sprintf('+%s seconds', $this->sessionLifetime->absoluteTimeoutSeconds)) <= $now) {
            $this->expire(reason: 'absolute_timeout');

            return false;
        }

        if ($lastSeenAt->modify(modifier: sprintf('+%s seconds', $this->sessionLifetime->idleTimeoutSeconds)) <= $now) {
            $this->expire(reason: 'idle_timeout');

            return false;
        }

        return true;
    }

    public function clear() : void
    {
        $this->sessionStore->invalidate();
    }

    private function expire(string $reason) : void
    {
        $storedUserId = $this->sessionStore->get(key: $this->sessionKey);
        $sessionId    = $this->currentSessionId();
        $userId       = is_int(value: $storedUserId)
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
                                                           'reason'  => $reason,
                                                       ],
                                       ));
        $this->clear();
    }

    private function readDate(string $key) : DateTimeImmutable|null
    {
        $stored = $this->sessionStore->get(key: $key);

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
    private function touch() : void
    {
        $now = $this->clock->now();
        $this->sessionStore->put(key: $this->lastSeenAtKey, value: $now->format(format: DATE_ATOM));

        $sessionId = $this->currentSessionId();

        if ($sessionId === null || ! $this->sessionRegistry instanceof SessionRegistryInterface) {
            return;
        }

        $record = $this->sessionRegistry->find(sessionId: $sessionId);

        if (! $record instanceof SessionRecord || $record->isRevoked()) {
            return;
        }

        $this->sessionRegistry->save(record: $record->withTouch(lastSeenAt: $now, idleTimeoutSeconds: $this->sessionLifetime->idleTimeoutSeconds));
    }

    /**
     * @throws DateMalformedStringException
     */
    public function resolveMfaVerifiedAt() : DateTimeImmutable|null
    {
        if (! $this->isSessionActive()) {
            return null;
        }

        $stored = $this->sessionStore->get(key: $this->mfaVerifiedAtKey);

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
    public function resolvePhishingResistant() : bool
    {
        if (! $this->isSessionActive()) {
            return false;
        }

        return $this->sessionStore->get(key: $this->phishingResistantKey) === '1';
    }
}
