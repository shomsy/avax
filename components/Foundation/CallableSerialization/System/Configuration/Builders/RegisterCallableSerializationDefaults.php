<?php

declare(strict_types=1);

namespace Avax\Components\Foundation\CallableSerialization\System\Configuration\Builders;

use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Foundation\CallableSerialization\System\Capabilities\ComputeHmacSignature\ComputeHmacSignature;
use Avax\Components\Foundation\CallableSerialization\System\Capabilities\RejectUnsafeCallable\RejectUnsafeCallable;
use Avax\Components\Foundation\CallableSerialization\System\Capabilities\SerializeCallable\SerializeClosureThroughLibrary;
use Avax\Components\Foundation\CallableSerialization\System\Capabilities\VerifyHmacSignature\VerifyHmacSignature;
use Avax\Components\Foundation\CallableSerialization\System\Configuration\CallableSerializationConfig;
use Avax\Components\Foundation\CallableSerialization\System\Flows\DecodeCallable\DecodeCallable;
use Avax\Components\Foundation\CallableSerialization\System\Flows\EncodeCallable\EncodeCallable;

final readonly class RegisterCallableSerializationDefaults
{
    public function register(ContainerInterface $container) : void
    {
        // HMAC signature capabilities
        $container->singleton(ComputeHmacSignature::class, static fn () : ComputeHmacSignature => new ComputeHmacSignature());
        $container->singleton(VerifyHmacSignature::class, static fn () : VerifyHmacSignature => new VerifyHmacSignature());

        // Serialization capability
        $container->singleton(
            SerializeClosureThroughLibrary::class,
            static function (ContainerInterface $c) : SerializeClosureThroughLibrary {
                /** @var CallableSerializationConfig $config */
                $config = $c->get(CallableSerializationConfig::class);
                return new SerializeClosureThroughLibrary(secretKey: $config->signingKey);
            },
        );

        // Reject unsafe callable capability
        $container->singleton(RejectUnsafeCallable::class, static fn () : RejectUnsafeCallable => new RejectUnsafeCallable());

        // Configuration
        $container->singleton(CallableSerializationConfig::class, static fn () : CallableSerializationConfig => new CallableSerializationConfig());

        // Flows
        $container->singleton(
            EncodeCallable::class,
            static fn (ContainerInterface $c) : EncodeCallable => new EncodeCallable(
                serializeCallable: $c->get(SerializeClosureThroughLibrary::class),
                computeSignature : $c->get(ComputeHmacSignature::class),
                config           : $c->get(CallableSerializationConfig::class),
            ),
        );

        $container->singleton(
            DecodeCallable::class,
            static fn (ContainerInterface $c) : DecodeCallable => new DecodeCallable(
                unserializeCallable: $c->get(SerializeClosureThroughLibrary::class),
                verifySignature    : $c->get(VerifyHmacSignature::class),
                rejectUnsafe       : $c->get(RejectUnsafeCallable::class),
                config             : $c->get(CallableSerializationConfig::class),
            ),
        );
    }
}
