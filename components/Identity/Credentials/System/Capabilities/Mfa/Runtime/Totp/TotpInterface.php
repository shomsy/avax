<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Totp;

use DateTimeImmutable;

/**
 * Package-owned TOTP contract used by MFA flows.
 */
interface TotpInterface
{
    public function generateSecret(): string;

    public function provisioningUri(string $issuer, string $accountLabel, string $secret): string;

    public function verify(
        string $secret,
        string $code,
        DateTimeImmutable $moment,
        ?int $lastAcceptedTimeStep = null,
    ): TotpVerification;
}
