<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Totp;

/**
 * Result of TOTP verification with replay-safe outcome details.
 */
final readonly class TotpVerification
{
    private function __construct(public bool $accepted, public string $reason, public int|null $timeStep = null) {}

    public static function accepted(int $timeStep) : self
    {
        return new self(
            accepted: true,
            reason  : 'accepted',
            timeStep: $timeStep,
        );
    }

    public static function invalid(string $reason = 'invalid') : self
    {
        return new self(
            accepted: false,
            reason  : $reason,
        );
    }
}
