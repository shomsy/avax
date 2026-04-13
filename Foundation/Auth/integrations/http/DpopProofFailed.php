<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use RuntimeException;

final class DpopProofFailed extends RuntimeException
{
    public static function missing() : self
    {
        return new self('DPoP proof is required.');
    }

    public static function invalid(string $reason = 'invalid_proof') : self
    {
        return new self("DPoP proof is invalid: {$reason}.");
    }
}
