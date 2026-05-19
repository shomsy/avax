<?php

declare(strict_types=1);

namespace Avax\Components\Foundation\CallableSerialization\System\Capabilities\SerializeCallable;

use Laravel\SerializableClosure\SerializableClosure;
use RuntimeException;
use SensitiveParameter;

/**
 * Serializes and deserializes PHP closures for cross-process transport.
 *
 * Uses laravel/serializable-closure because native PHP serialize()
 * cannot serialize closures in PHP 8.5.
 *
 * Security: A non-null secret key is REQUIRED. The key provides HMAC
 * integrity protection for serialized closures. Without it, any
 * attacker with access to stored closure payloads can tamper with
 * or replay serialized closures.
 */
final class SerializeClosureThroughLibrary
{
    public function __construct(
        #[SensitiveParameter]
        private readonly string $secretKey,
    ) {
        if ($secretKey === '') {
            throw new RuntimeException('SerializeClosureThroughLibrary requires a non-empty secret key for HMAC integrity.');
        }
    }

    /**
     * Serialize a closure to a base64 string.
     *
     * The secret key is set before serialization so that the
     * SerializableClosure library applies HMAC signing.
     */
    public function serialize(\Closure $closure) : string
    {
        SerializableClosure::setSecretKey($this->secretKey);

        $serializable = new SerializableClosure($closure);

        return base64_encode(serialize($serializable));
    }

    /**
     * Deserialize a base64 string back to a closure.
     *
     * The same secret key must be set before deserialization so that
     * the SerializableClosure library verifies the HMAC signature.
     * A tampered payload will throw an exception from the library.
     *
     * @throws \InvalidArgumentException if base64 decoding fails or HMAC verification fails
     */
    public function unserialize(string $encoded) : \Closure
    {
        SerializableClosure::setSecretKey($this->secretKey);

        $serialized = base64_decode($encoded, strict: true);

        if ($serialized === false) {
            throw new \InvalidArgumentException('Invalid base64 encoded closure payload');
        }

        /** @var SerializableClosure|false $serializable */
        $serializable = @unserialize($serialized, [
            'allowed_classes' => [
                SerializableClosure::class,
                \Laravel\SerializableClosure\Serializers\Signed::class,
                \Laravel\SerializableClosure\Serializers\Native::class,
            ],
        ]);

        if (! $serializable instanceof SerializableClosure) {
            throw new \InvalidArgumentException('Serialized closure payload did not resolve to a SerializableClosure. The payload may have been tampered with.');
        }

        return $serializable->getClosure();
    }
}
