<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\RequestSigning;

use Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation\SignatureTimestamp;

final readonly class RejectExpiredSignature
{
    public static function check(
        SignatureTimestamp $timestamp,
        int $toleranceSeconds,
        ?int $currentTime = null,
    ): SignatureVerificationResult {
        $now = $currentTime ?? time();

        if (!$timestamp->isWithinTolerance($toleranceSeconds, $now)) {
            $age = abs($now - $timestamp->epochSeconds);

            return SignatureVerificationResult::failure(
                "Signature expired. Age: {$age}s, tolerance: {$toleranceSeconds}s."
            );
        }

        return SignatureVerificationResult::success();
    }
}
