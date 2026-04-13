<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * Public MFA enrollment material shown during authenticator setup.
 */
final readonly class MfaEnrollment
{
    public function __construct(
        public MfaMethod                     $method,
        public MfaStatus                     $status,
        #[SensitiveParameter] public string  $accountLabel,
        public string                        $issuer,
        #[SensitiveParameter] private string $secret,
        public string                        $otpauthUri,
        public DateTimeImmutable             $startedAt,
        public DateTimeImmutable             $expiresAt
    ) {}

    public function secret() : string
    {
        return $this->secret;
    }

    public function qrPayload() : string
    {
        return $this->otpauthUri;
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo() : array
    {
        return [
            'method'       => $this->method->value,
            'status'       => $this->status->value,
            'accountLabel' => $this->accountLabel,
            'issuer'       => $this->issuer,
            'secret'       => '[REDACTED]',
            'otpauthUri'   => '[REDACTED]',
            'startedAt'    => $this->startedAt,
            'expiresAt'    => $this->expiresAt,
        ];
    }
}
