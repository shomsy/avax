<?php

declare(strict_types=1);

namespace components\Auth\System\Flows\RecoverAccess\PasswordReset;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * Result of password reset challenge creation.
 */
final readonly class PasswordResetChallenge
{
    public function __construct(
        public bool                              $dispatched,
        #[SensitiveParameter] public string|null $token = null,
        public DateTimeImmutable|null            $expiresAt = null
    ) {}

    public static function hidden() : self
    {
        return new self(dispatched: true);
    }
}
