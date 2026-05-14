<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Foundation\Serialization;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Override;

final readonly class JsonCacheSerializer implements CacheSerializer
{
    public const string FORMAT = 'json';

    private const ENCODING_OPTIONS = JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION;

    public function __construct(private Clock $clock)
    {
    }

    #[Override]
    public function serialize(mixed $value): SerializedCachePayload
    {
        $serialized = json_encode(value: $value, flags: self::ENCODING_OPTIONS);

        return SerializedCachePayload::create(
            data  : $serialized,
            format: self::FORMAT,
            clock : $this->clock,
        );
    }

    #[Override]
    /**
 * @throws CachePayloadCouldNotBeSerialized
 */
public function unserialize(SerializedCachePayload $serializedCachePayload): mixed
    {
        if (! $this->canUnserialize($serializedCachePayload)) {
            throw new CachePayloadCouldNotBeSerialized(
                message: sprintf('Cannot unserialize payload with format "%s"', $serializedCachePayload->format),
            );
        }

        if (! $serializedCachePayload->verify()) {
            throw new CachePayloadCouldNotBeSerialized(
                message: 'Payload checksum verification failed',
            );
        }

        $result = json_decode(json: $serializedCachePayload->data, associative: false, depth: 512);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new CachePayloadCouldNotBeSerialized(
                message: sprintf('Failed to unserialize JSON payload: %s', json_last_error_msg()),
            );
        }

        return $result;
    }

    public function canUnserialize(SerializedCachePayload $serializedCachePayload): bool
    {
        return $serializedCachePayload->format === self::FORMAT;
    }

    #[Override]
    public function supportedType(): string
    {
        return self::FORMAT;
    }
}
