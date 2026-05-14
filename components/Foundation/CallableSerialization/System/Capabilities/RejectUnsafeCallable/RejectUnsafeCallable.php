<?php

declare(strict_types=1);

namespace Avax\Components\Foundation\CallableSerialization\System\Capabilities\RejectUnsafeCallable;

use Avax\Components\Foundation\CallableSerialization\System\Foundation\Failure\CallablePayloadFailure;
use Avax\Components\Foundation\CallableSerialization\System\Foundation\Values\CallablePayload;

/**
 * Rejects unsigned, corrupted, or otherwise unsafe callable payloads.
 */
final readonly class RejectUnsafeCallable
{
    public function check(CallablePayload $payload) : CallablePayloadFailure|null
    {
        if ($payload->signature === '') {
            return new CallablePayloadFailure(
                reason : 'unsigned',
                message: 'Callable payload has no signature',
            );
        }

        if ($payload->encoded === '') {
            return new CallablePayloadFailure(
                reason : 'invalid',
                message: 'Callable payload has empty encoded data',
            );
        }

        if (!in_array($payload->algorithm, ['hmac-sha256'], true)) {
            return new CallablePayloadFailure(
                reason : 'invalid',
                message: "Unsupported algorithm: {$payload->algorithm}",
            );
        }

        return null;
    }
}
