<?php

declare(strict_types=1);

namespace Avax\Components\Foundation\CallableSerialization\System\Flows\EncodeCallable;

use Avax\Components\Foundation\CallableSerialization\System\Configuration\CallableSerializationConfig;
use Avax\Components\Foundation\CallableSerialization\System\Capabilities\ComputeHmacSignature\ComputeHmacSignature;
use Avax\Components\Foundation\CallableSerialization\System\Capabilities\SerializeCallable\SerializeClosureThroughLibrary;
use Avax\Components\Foundation\CallableSerialization\System\Foundation\Values\CallablePayload;

/**
 * Encodes a callable into a signed payload string safe for cross-process transport.
 */
final readonly class EncodeCallable
{
    public function __construct(
        private SerializeClosureThroughLibrary $serializeCallable,
        private ComputeHmacSignature $computeSignature,
        private CallableSerializationConfig $config,
    ) {}

    /**
     * Serialize and sign a closure. Returns JSON-encoded payload.
     */
    public function encode(\Closure $closure) : string
    {
        $encoded = $this->serializeCallable->serialize($closure);

        $signature = $this->config->hasSigningKey()
            ? $this->computeSignature->sign($encoded, $this->config->signingKey)
            : '';

        $payload = new CallablePayload(
            encoded   : $encoded,
            signature : $signature,
            version   : $this->config->version,
            algorithm : $this->config->algorithm,
        );

        return $payload->toJson();
    }
}
