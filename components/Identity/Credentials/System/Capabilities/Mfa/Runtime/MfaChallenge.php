<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime;

use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enums\MfaChallengePurpose;
use DateTimeImmutable;

/**
 * Public MFA challenge metadata returned to the application boundary.
 */
final readonly class MfaChallenge
{
    public function __construct(public string $challengeId, public MfaChallengePurpose $purpose, public DateTimeImmutable $expiresAt, public int $remainingAttempts) {}

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
