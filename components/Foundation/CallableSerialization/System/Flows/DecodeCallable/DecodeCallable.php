<?php

declare(strict_types=1);

namespace Avax\Components\Foundation\CallableSerialization\System\Flows\DecodeCallable;

use Avax\Components\Foundation\CallableSerialization\System\Capabilities\RejectUnsafeCallable\RejectUnsafeCallable;
use Avax\Components\Foundation\CallableSerialization\System\Capabilities\SerializeCallable\SerializeClosureThroughLibrary;
use Avax\Components\Foundation\CallableSerialization\System\Capabilities\VerifyHmacSignature\VerifyHmacSignature;
use Avax\Components\Foundation\CallableSerialization\System\Configuration\CallableSerializationConfig;
use Avax\Components\Foundation\CallableSerialization\System\Foundation\Failure\CallablePayloadFailure;
use Avax\Components\Foundation\CallableSerialization\System\Foundation\Values\CallablePayload;

/**
 * Decodes a signed payload string back into a callable after verifying integrity.
 */
final readonly class DecodeCallable
{
    public function __construct(
        private SerializeClosureThroughLibrary $unserializeCallable,
        private VerifyHmacSignature $verifySignature,
        private RejectUnsafeCallable $rejectUnsafe,
        private CallableSerializationConfig $config,
    ) {}

    /**
     * Verify signature and deserialize a closure from JSON payload.
     *
     * @return array{closure: \Closure}|array{failure: CallablePayloadFailure}
     */
    public function decode(string $jsonPayload) : array
    {
        try {
            $payload = CallablePayload::fromJson($jsonPayload);
        } catch (\Throwable $e) {
            return ['failure' => new CallablePayloadFailure(
                reason : 'corrupted',
                message: "Invalid JSON payload: {$e->getMessage()}",
            )];
        }

        $rejection = $this->rejectUnsafe->check($payload);
        if ($rejection !== null) {
            // Allow unsigned payloads when no signing key is configured
            if ($rejection->isUnsigned() && !$this->config->hasSigningKey()) {
                // Skip rejection, proceed without signature verification
            } else {
                return ['failure' => $rejection];
            }
        }

        if ($this->config->hasSigningKey() && $payload->signature !== '') {
            $valid = $this->verifySignature->verify(
                data    : $payload->encoded,
                key     : $this->config->signingKey,
                expectedSignature: $payload->signature,
            );

            if (!$valid) {
                return ['failure' => new CallablePayloadFailure(
                    reason : 'corrupted',
                    message: 'Signature verification failed',
                )];
            }
        }

        try {
            $closure = $this->unserializeCallable->unserialize($payload->encoded);
        } catch (\Throwable $e) {
            return ['failure' => new CallablePayloadFailure(
                reason : 'invalid',
                message: "Failed to deserialize closure: {$e->getMessage()}",
            )];
        }

        return ['closure' => $closure];
    }
}
