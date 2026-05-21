<?php

declare(strict_types=1);

namespace Avax\Components\Identity\System\Capabilities\GuestSession;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use DateTimeImmutable;

/**
 * GuestSessionIdentity is the fail-closed session backend for the default root Identity DSL.
 */
final readonly class GuestSessionIdentity implements SessionIdentityInterface
{
    public function issue(
        int $userId,
        DateTimeImmutable|null $mfaVerifiedAt = null,
        bool $phishingResistant = false,
    ) : string|null {
        return null;
    }

    public function captureCurrentSession(string|null $ipAddress = null, string|null $userAgent = null) : void
    {
    }

    public function resolveUserId() : int|null
    {
        return null;
    }

    public function resolveMfaVerifiedAt() : DateTimeImmutable|null
    {
        return null;
    }

    public function resolvePhishingResistant() : bool
    {
        return false;
    }

    public function currentSessionId() : string|null
    {
        return null;
    }

    public function clear() : void
    {
    }
}
