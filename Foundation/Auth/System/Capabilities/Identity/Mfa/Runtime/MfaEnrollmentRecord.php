<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime;

use Avax\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Stored pending MFA enrollment state.
 */
final readonly class MfaEnrollmentRecord
{
    public DateTimeImmutable $expiresAt;
    public DateTimeImmutable $startedAt;
    public string            $secret;
    public string            $issuer;
    public string            $accountLabel;
    public MfaMethod         $method;
    public UserId            $userId;

    public function __construct(
        UserId                       $userId,
        MfaMethod                    $method,
        #[SensitiveParameter] string $accountLabel,
        string                       $issuer,
        #[SensitiveParameter] string $secret,
        DateTimeImmutable            $startedAt,
        DateTimeImmutable            $expiresAt
    )
    {
        $this->userId       = $userId;
        $this->method       = $method;
        $this->accountLabel = $accountLabel;
        $this->issuer       = $issuer;
        $this->secret       = $secret;
        $this->startedAt    = $startedAt;
        $this->expiresAt    = $expiresAt;
    }

    public function isExpiredAt(DateTimeImmutable $moment) : bool
    {
        return $this->expiresAt <= $moment;
    }
}
