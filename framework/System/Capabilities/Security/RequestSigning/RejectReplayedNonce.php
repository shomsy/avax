<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\RequestSigning;

use Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation\NonceStore;

final readonly class RejectReplayedNonce
{
    public static function check(
        string $nonce,
        NonceStore $nonceStore,
        int $timestamp,
    ): SignatureVerificationResult {
        if ($nonceStore->has($nonce)) {
            return SignatureVerificationResult::failure('Nonce replay detected.');
        }

        return SignatureVerificationResult::success();
    }
}
