<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\SessionIdentity;

use Avax\Components\HTTP\Session\System\PublicSurface\Session;
use Avax\Components\Identity\Auth\System\Configuration\SessionLifetime;
use DateTimeImmutable;
use Exception;

final readonly class SessionIdentity
{
    public function __construct(
        private Session         $session,
        private SessionLifetime $sessionLifetime,
        private string          $sessionKey = 'auth_user_id',
        private string          $mfaVerifiedAtKey = 'auth_mfa_verified_at',
        private string          $phishingResistantKey = 'auth_phishing_resistant',
        private string          $issuedAtKey = 'auth_session_issued_at',
        private string          $lastSeenAtKey = 'auth_session_last_seen_at',
    ) {}

    public function issue(int $userId, ?DateTimeImmutable $mfaVerifiedAt = null, bool $phishingResistant = false) : string
    {
        $this->session->regenerate();
        $now = new DateTimeImmutable()->format(DATE_ATOM);

        $this->session->put($this->sessionKey, $userId);
        $this->session->put($this->mfaVerifiedAtKey, $mfaVerifiedAt?->format(DATE_ATOM));
        $this->session->put($this->phishingResistantKey, $phishingResistant ? '1' : '0');
        $this->session->put($this->issuedAtKey, $now);
        $this->session->put($this->lastSeenAtKey, $now);

        return $this->session->id();
    }

    public function resolveUserId() : ?int
    {
        if (! $this->isSessionActive()) {
            return null;
        }

        $userId = $this->session->get($this->sessionKey);

        if (is_int($userId) || (is_string($userId) && ctype_digit($userId))) {
            $this->touch();

            return (int) $userId;
        }

        $this->expire();

        return null;
    }

    private function isSessionActive() : bool
    {
        $issuedAt   = $this->readDate($this->issuedAtKey);
        $lastSeenAt = $this->readDate($this->lastSeenAtKey);

        if (! $issuedAt || ! $lastSeenAt) {
            return false;
        }

        $now = new DateTimeImmutable();

        if ($issuedAt->modify(sprintf('+%s seconds', $this->sessionLifetime->absoluteTimeoutSeconds)) <= $now) {
            $this->expire();

            return false;
        }

        if ($lastSeenAt->modify(sprintf('+%s seconds', $this->sessionLifetime->idleTimeoutSeconds)) <= $now) {
            $this->expire();

            return false;
        }

        return true;
    }

    private function readDate(string $key) : ?DateTimeImmutable
    {
        $stored = $this->session->get($key);
        if (! $stored) {
            return null;
        }

        try {
            return new DateTimeImmutable($stored);
        } catch (Exception) {
            return null;
        }
    }

    private function expire() : void
    {
        $this->session->flush();
    }

    private function touch() : void
    {
        $this->session->put($this->lastSeenAtKey, new DateTimeImmutable()->format(DATE_ATOM));
    }

    public function resolveMfaVerifiedAt() : ?DateTimeImmutable
    {
        return $this->readDate($this->mfaVerifiedAtKey);
    }

    public function resolvePhishingResistant() : bool
    {
        return $this->session->get($this->phishingResistantKey) === '1';
    }
}
