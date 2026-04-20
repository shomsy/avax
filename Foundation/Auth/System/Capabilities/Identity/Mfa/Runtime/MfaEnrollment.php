<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Mfa;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * Public MFA enrollment material shown during authenticator setup.
 */
final readonly class MfaEnrollment
{
    public DateTimeImmutable $expiresAt;
    public DateTimeImmutable $startedAt;
    public string            $otpauthUri;
    public string            $issuer;
    public string            $accountLabel;
    public MfaStatus         $status;
    public MfaMethod         $method;
    private string           $secret;

    public function __construct(
        MfaMethod                    $method,
        MfaStatus                    $status,
        #[SensitiveParameter] string $accountLabel,
        string                       $issuer,
        #[SensitiveParameter] string $secret,
        string                       $otpauthUri,
        DateTimeImmutable            $startedAt,
        DateTimeImmutable            $expiresAt
    )
    {
        $this->method       = $method;
        $this->status       = $status;
        $this->accountLabel = $accountLabel;
        $this->issuer       = $issuer;
        $this->secret       = $secret;
        $this->otpauthUri   = $otpauthUri;
        $this->startedAt    = $startedAt;
        $this->expiresAt    = $expiresAt;
    }

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
