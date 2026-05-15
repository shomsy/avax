<?php

declare(strict_types=1);

namespace Avax\Components\Foundation\CallableSerialization\System\Configuration\Builders;

use Avax\Components\Foundation\CallableSerialization\System\Capabilities\ComputeHmacSignature\ComputeHmacSignature;
use Avax\Components\Foundation\CallableSerialization\System\Capabilities\RejectUnsafeCallable\RejectUnsafeCallable;
use Avax\Components\Foundation\CallableSerialization\System\Capabilities\SerializeCallable\SerializeClosureThroughLibrary;
use Avax\Components\Foundation\CallableSerialization\System\Capabilities\VerifyHmacSignature\VerifyHmacSignature;
use Avax\Components\Foundation\CallableSerialization\System\Flows\DecodeCallable\DecodeCallable;
use Avax\Components\Foundation\CallableSerialization\System\Flows\EncodeCallable\EncodeCallable;

/**
 * Factory that assembles the CallableSerialization capability from its parts.
 */
final readonly class BuildCallableSerialization
{
    /**
     * @param array{signing_key?: string, algorithm?: string, version?: string} $config
     */
    public function build(array $config = []) : EncodeDecodePair
    {
        $config = CallableSerializationConfig::fromArray($config);

        $serialize = new SerializeClosureThroughLibrary();
        $computeSignature = new ComputeHmacSignature();
        $verifySignature = new VerifyHmacSignature();
        $rejectUnsafe = new RejectUnsafeCallable();

        $encoder = new EncodeCallable(
            serializeCallable: $serialize,
            computeSignature : $computeSignature,
            config           : $config,
        );

        $decoder = new DecodeCallable(
            unserializeCallable: $serialize,
            verifySignature    : $verifySignature,
            rejectUnsafe       : $rejectUnsafe,
            config             : $config,
        );

        return new EncodeDecodePair(encoder: $encoder, decoder: $decoder);
    }
}

final readonly class EncodeDecodePair
{
    public function __construct(
        public EncodeCallable $encoder,
        public DecodeCallable $decoder,
    ) {}
}
