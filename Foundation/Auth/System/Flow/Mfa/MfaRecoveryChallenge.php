<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * Public MFA recovery challenge creation result.
 */
final readonly class MfaRecoveryChallenge
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

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo() : array
    {
        return [
            'dispatched' => $this->dispatched,
            'token'      => $this->token === null ? null : '[REDACTED]',
            'expiresAt'  => $this->expiresAt,
        ];
    }
}
