<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Records;

use Avax\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Stored MFA recovery token state.
 */
final readonly class MfaRecoveryRecord
{
    public function __construct(
        #[SensitiveParameter] public string $tokenHash,
        public UserId                       $userId,
        public DateTimeImmutable            $expiresAt
    )
    {
    }

    public function isExpiredAt(DateTimeImmutable $moment) : bool
    {
        return $this->expiresAt <= $moment;
    }
}
