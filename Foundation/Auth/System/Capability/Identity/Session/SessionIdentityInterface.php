<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Identity\Session;

use DateTimeImmutable;

/**
 * Interface SessionIdentityInterface within the Auth System.
 */
interface SessionIdentityInterface
{
    public function issue(
        int $userId,
        DateTimeImmutable|null $mfaVerifiedAt = null,
        bool $phishingResistant = false
    ) : string|null;

    public function captureCurrentSession(string|null $ipAddress = null, string|null $userAgent = null) : void;

    public function resolveUserId() : int|null;

    public function resolveMfaVerifiedAt() : DateTimeImmutable|null;

    public function resolvePhishingResistant() : bool;

    public function currentSessionId() : string|null;

    public function clear() : void;
}
