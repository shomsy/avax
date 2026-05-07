<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Recover;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * Public MFA recovery challenge creation result.
 */
final readonly class MfaRecoveryChallenge
{
    public function __construct(
        public bool               $dispatched,
        #[SensitiveParameter]
        public ?string            $token = null,
        public ?DateTimeImmutable $expiresAt = null,
    ) {}

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
