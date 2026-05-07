<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\System\Capabilities\Mfa\Runtime\Records;

use DateTimeImmutable;

/**
 * One MFA verification attempt recorded against a challenge.
 */
final readonly class MfaVerificationAttempt
{
    public function __construct(public DateTimeImmutable $occurredAt, public bool $accepted, public string $reason) {}
}
