<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Records;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enums\MfaMethod;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Stored pending MFA enrollment state.
 */
final readonly class MfaEnrollmentRecord
{
    public function __construct(
        public UserId $userId,
        public MfaMethod $method,
        #[SensitiveParameter]
        public string $accountLabel,
        public string $issuer,
        #[SensitiveParameter]
        public string $secret,
        public DateTimeImmutable $startedAt,
        public DateTimeImmutable $expiresAt,
    ) {
    }

    public function isExpiredAt(DateTimeImmutable $moment): bool
    {
        return $this->expiresAt <= $moment;
    }
}
