<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Foundation\Serialization;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Override;

final readonly class PhpCacheSerializer implements CacheSerializer
{
    public const string FORMAT = 'php-serialized';

    public function __construct(private Clock $clock)
    {
    }

    #[Override]
    public function serialize(mixed $value): SerializedCachePayload
    {
        $serialized = serialize(value: $value);

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

        // Fail-closed: reject object instantiation from cached payloads.
        // PHP native serialization is only safe for scalar/array data here.
        // Callers needing typed objects must use JsonCacheSerializer with explicit schema.
        /** @var mixed $result */
        $result = @unserialize($serializedCachePayload->data, ['allowed_classes' => []]);

        // With allowed_classes => [], objects become __PHP_Incomplete_Class instead of returning false.
        // We must detect and reject these as they indicate attempted object injection.
        if ($result instanceof \__PHP_Incomplete_Class) {
            throw new CachePayloadCouldNotBeSerialized(
                message: 'Failed to unserialize payload data: object instantiation rejected',
            );
        }

        if ($result === false && $serializedCachePayload->data !== 'b:0;') {
            throw new CachePayloadCouldNotBeSerialized(
                message: 'Failed to unserialize payload data',
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
