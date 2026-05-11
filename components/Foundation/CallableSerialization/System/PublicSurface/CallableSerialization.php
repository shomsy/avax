<?php

declare(strict_types=1);

namespace Avax\Components\Foundation\CallableSerialization\System\PublicSurface;

use Avax\Components\Foundation\CallableSerialization\System\Configuration\BuildCallableSerialization;
use Avax\Components\Foundation\CallableSerialization\System\Configuration\CallableSerializationConfig;
use Avax\Components\Foundation\CallableSerialization\System\Configuration\EncodeDecodePair;
use Avax\Components\Foundation\CallableSerialization\System\Foundation\Failure\CallablePayloadFailure;
use Closure;

/**
 * Static facade for the CallableSerialization capability.
 *
 * Encode: closure -> signed JSON payload (safe for cross-process transport)
 * Decode: signed JSON payload -> closure (verified before deserialization)
 */
final class CallableSerialization
{
    private static ?EncodeDecodePair $pair = null;

    /**
     * Configure the global signing key.
     */
    public static function configure(string $signingKey) : void
    {
        self::$pair = (new BuildCallableSerialization())->build([
            'signing_key' => $signingKey,
        ]);
    }

    /**
     * Serialize and sign a closure. Returns JSON payload string.
     */
    public static function encode(Closure $closure, string|null $signingKey = null) : string
    {
        $pair = self::pair($signingKey);

        return $pair->encoder->encode($closure);
    }

    /**
     * Verify signature and deserialize a closure from JSON payload.
     *
     * @return array{closure: Closure}|array{failure: CallablePayloadFailure}
     */
    public static function decode(string $jsonPayload, string|null $signingKey = null) : array
    {
        $pair = self::pair($signingKey);

        return $pair->decoder->decode($jsonPayload);
    }

    private static function pair(string|null $signingKey) : EncodeDecodePair
    {
        if ($signingKey !== null) {
            return (new BuildCallableSerialization())->build([
                'signing_key' => $signingKey,
            ]);
        }

        if (self::$pair === null) {
            self::$pair = (new BuildCallableSerialization())->build();
        }

        return self::$pair;
    }
}
