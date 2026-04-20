<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Mfa;

/**
 * Result of TOTP verification with replay-safe outcome details.
 */
final readonly class TotpVerification
{
    public int|null $timeStep;
    public string   $reason;
    public bool     $accepted;

    private function __construct(
        bool     $accepted,
        string   $reason,
        int|null $timeStep = null
    )
    {
        $this->accepted = $accepted;
        $this->reason   = $reason;
        $this->timeStep = $timeStep;
    }

    public static function accepted(int $timeStep) : self
    {
        return new self(
            accepted: true,
            reason  : 'accepted',
            timeStep: $timeStep
        );
    }

    public static function invalid(string $reason = 'invalid') : self
    {
        return new self(
            accepted: false,
            reason  : $reason
        );
    }
}
