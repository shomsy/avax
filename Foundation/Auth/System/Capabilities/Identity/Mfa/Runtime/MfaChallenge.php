<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime;

use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enums\MfaChallengePurpose;
use DateTimeImmutable;

/**
 * Public MFA challenge metadata returned to the application boundary.
 */
final readonly class MfaChallenge
{
    public int                 $remainingAttempts;
    public DateTimeImmutable   $expiresAt;
    public MfaChallengePurpose $purpose;
    public string              $challengeId;

    public function __construct(
        string              $challengeId,
        MfaChallengePurpose $purpose,
        DateTimeImmutable   $expiresAt,
        int                 $remainingAttempts
    )
    {
        $this->challengeId       = $challengeId;
        $this->purpose           = $purpose;
        $this->expiresAt         = $expiresAt;
        $this->remainingAttempts = $remainingAttempts;
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo() : array
    {
        return [
            'challengeId'       => $this->challengeId,
            'purpose'           => $this->purpose->value,
            'expiresAt'         => $this->expiresAt,
            'remainingAttempts' => $this->remainingAttempts,
        ];
    }
}
