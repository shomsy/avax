<?php

declare(strict_types=1);

namespace Avax\Cache\System\Foundation\Serialization;

use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\SystemClock;

final readonly class JsonCacheSerializer implements CacheSerializer
{
    public const FORMAT = 'json';

    private const ENCODING_OPTIONS = JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION;

    public function __construct(private Clock $clock = new SystemClock()) {}

    public function serialize(mixed $value) : SerializedCachePayload
    {
        $serialized = json_encode(value: $value, flags: self::ENCODING_OPTIONS);

        if ($serialized === false) {
            throw new CachePayloadCouldNotBeSerialized(
                message: sprintf('Failed to serialize value to JSON: %s', json_last_error_msg())
            );
        }

        return SerializedCachePayload::create(
            data  : $serialized,
            format: self::FORMAT,
            clock : $this->clock
        );
    }

    public function unserialize(SerializedCachePayload $payload) : mixed
    {
        if (! $this->canUnserialize(payload: $payload)) {
            throw new CachePayloadCouldNotBeSerialized(
                message: sprintf('Cannot unserialize payload with format "%s"', $payload->format)
            );
        }

        if (! $payload->verify()) {
            throw new CachePayloadCouldNotBeSerialized(
                message: 'Payload checksum verification failed'
            );
        }

        $result = json_decode(json: $payload->data, associative: false, depth: 512);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new CachePayloadCouldNotBeSerialized(
                message: sprintf('Failed to unserialize JSON payload: %s', json_last_error_msg())
            );
        }

        return $result;
    }

    public function canUnserialize(SerializedCachePayload $payload) : bool
    {
        return $payload->format === self::FORMAT;
    }

    public function supportedType() : string
    {
        return self::FORMAT;
    }
}