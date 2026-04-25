<?php

declare(strict_types=1);

namespace Avax\Cache\System\Foundation\Serialization;

use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\SystemClock;

final readonly class PhpCacheSerializer implements CacheSerializer
{
    public const FORMAT = 'php-serialized';

    public function __construct(
        private Clock $clock = new SystemClock()
    ) {}

    public function serialize(mixed $value) : SerializedCachePayload
    {
        $serialized = serialize(value: $value);

        if ($serialized === false) {
            throw new CachePayloadCouldNotBeSerialized(
                message: 'Failed to serialize value using PHP serialization'
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
        if (! $this->canUnserialize($payload)) {
            throw new CachePayloadCouldNotBeSerialized(
                message: sprintf('Cannot unserialize payload with format "%s"', $payload->format)
            );
        }

        if (! $payload->verify()) {
            throw new CachePayloadCouldNotBeSerialized(
                message: 'Payload checksum verification failed'
            );
        }

        $result = unserialize($payload->data, ['allowed_classes' => true]);

        if ($result === false && $payload->data !== 'b:0;') {
            throw new CachePayloadCouldNotBeSerialized(
                message: 'Failed to unserialize payload data'
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