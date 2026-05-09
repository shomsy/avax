<?php

declare(strict_types=1);

namespace Avax\Components\Foundation\CallableSerialization\System\Capabilities\SerializeCallable;

use Laravel\SerializableClosure\SerializableClosure;

/**
 * Serializes and deserializes PHP closures for cross-process transport.
 *
 * Uses laravel/serializable-closure because native PHP serialize()
 * cannot serialize closures in PHP 8.5.
 */
final readonly class SerializeClosureThroughLibrary
{
    /**
     * Serialize a closure to a base64 string.
     */
    public function serialize(\Closure $closure) : string
    {
        SerializableClosure::setSecretKey(null);

        $serializable = new SerializableClosure($closure);

        return base64_encode(serialize($serializable));
    }

    /**
     * Deserialize a base64 string back to a closure.
     */
    public function unserialize(string $encoded) : \Closure
    {
        SerializableClosure::setSecretKey(null);

        $serialized = base64_decode($encoded, strict: true);

        if ($serialized === false) {
            throw new \InvalidArgumentException('Invalid base64 encoded closure');
        }

        /** @var SerializableClosure $serializable */
        $serializable = unserialize($serialized);

        return $serializable->getClosure();
    }
}
