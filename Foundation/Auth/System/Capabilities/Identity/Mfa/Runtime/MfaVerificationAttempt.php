<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime;

use DateTimeImmutable;

/**
 * One MFA verification attempt recorded against a challenge.
 */
final readonly class MfaVerificationAttempt
{
    public string            $reason;
    public bool              $accepted;
    public DateTimeImmutable $occurredAt;

    public function __construct(
        DateTimeImmutable $occurredAt,
        bool              $accepted,
        string            $reason
    )
    {
        $this->occurredAt = $occurredAt;
        $this->accepted   = $accepted;
        $this->reason     = $reason;
    }
}
