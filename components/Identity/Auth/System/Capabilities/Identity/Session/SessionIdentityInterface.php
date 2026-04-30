<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Session;

use DateTimeImmutable;

/**
 * Interface SessionIdentityInterface within the Auth System.
 */
interface SessionIdentityInterface
{
    public function issue(
        int               $userId,
        DateTimeImmutable $mfaVerifiedAt = null,
        bool              $phishingResistant = false,
    ) : string|null;

    public function captureCurrentSession(string $ipAddress = null, string $userAgent = null) : void;

    public function resolveUserId() : int|null;

    public function resolveMfaVerifiedAt() : DateTimeImmutable|null;

    public function resolvePhishingResistant() : bool;

    public function currentSessionId() : string|null;

    public function clear() : void;
}
