<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Recover;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * Result of password reset challenge creation.
 */
final readonly class PasswordResetChallenge
{
    public DateTimeImmutable|null $expiresAt;
    public string|null            $token;
    public bool                   $dispatched;

    public function __construct(
        bool                              $dispatched,
        #[SensitiveParameter] string|null $token = null,
        DateTimeImmutable|null            $expiresAt = null
    )
    {
        $this->dispatched = $dispatched;
        $this->token      = $token;
        $this->expiresAt  = $expiresAt;
    }

    public static function hidden() : self
    {
        return new self(dispatched: true);
    }
}
