<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Foundation\CallableSerialization\Configuration;

use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use Avax\Components\Foundation\CallableSerialization\System\Capabilities\ComputeHmacSignature\ComputeHmacSignature;
use Avax\Components\Foundation\CallableSerialization\System\Capabilities\RejectUnsafeCallable\RejectUnsafeCallable;
use Avax\Components\Foundation\CallableSerialization\System\Capabilities\SerializeCallable\SerializeClosureThroughLibrary;
use Avax\Components\Foundation\CallableSerialization\System\Capabilities\VerifyHmacSignature\VerifyHmacSignature;
use Avax\Components\Foundation\CallableSerialization\System\Configuration\CallableSerializationConfig;
use Avax\Components\Foundation\CallableSerialization\System\Configuration\CallableSerializationServiceProvider;
use Avax\Components\Foundation\CallableSerialization\System\Flows\DecodeCallable\DecodeCallable;
use Avax\Components\Foundation\CallableSerialization\System\Flows\EncodeCallable\EncodeCallable;
use PHPUnit\Framework\TestCase;

final class CallableSerializationServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private CallableSerializationServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();

        $this->provider = new CallableSerializationServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);

        // SerializeClosureThroughLibrary requires a non-empty signing key.
        // Override the default config instance after the provider registers.
        $this->container->instance(
            CallableSerializationConfig::class,
            new CallableSerializationConfig(
                signingKey: 'test-signing-key-for-resolution',
            ),
        );
    }

    public function test_compute_hmac_signature_resolves(): void
    {
        $capability = $this->container->get(ComputeHmacSignature::class);

        $this->assertInstanceOf(ComputeHmacSignature::class, $capability);
    }

    public function test_verify_hmac_signature_resolves(): void
    {
        $capability = $this->container->get(VerifyHmacSignature::class);

        $this->assertInstanceOf(VerifyHmacSignature::class, $capability);
    }

    public function test_serialize_closure_through_library_resolves(): void
    {
        $capability = $this->container->get(SerializeClosureThroughLibrary::class);

        $this->assertInstanceOf(SerializeClosureThroughLibrary::class, $capability);
    }

    public function test_reject_unsafe_callable_resolves(): void
    {
        $capability = $this->container->get(RejectUnsafeCallable::class);

        $this->assertInstanceOf(RejectUnsafeCallable::class, $capability);
    }

    public function test_callable_serialization_config_resolves(): void
    {
        $config = $this->container->get(CallableSerializationConfig::class);

        $this->assertInstanceOf(CallableSerializationConfig::class, $config);
    }

    public function test_encode_callable_resolves(): void
    {
        $flow = $this->container->get(EncodeCallable::class);

        $this->assertInstanceOf(EncodeCallable::class, $flow);
    }

    public function test_decode_callable_resolves(): void
    {
        $flow = $this->container->get(DecodeCallable::class);

        $this->assertInstanceOf(DecodeCallable::class, $flow);
    }
}
