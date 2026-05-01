<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Foundation\Serialization;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Override;

final readonly class PhpCacheSerializer implements CacheSerializer
{
    public const string FORMAT = 'php-serialized';

    public function __construct(private Clock $clock = new SystemClock()) {}

    #[Override]
    public function serialize(mixed $value) : SerializedCachePayload
    {
        $serialized = serialize(value: $value);

        return SerializedCachePayload::create(
            data  : $serialized,
            format: self::FORMAT,
            clock : $this->clock,
        );
    }

    #[Override]
    public function unserialize(SerializedCachePayload $payload) : mixed
    {
        if (! $this->canUnserialize(payload: $payload)) {
            throw new CachePayloadCouldNotBeSerialized(
                message: sprintf('Cannot unserialize payload with format "%s"', $payload->format),
            );
        }

        if (! $payload->verify()) {
            throw new CachePayloadCouldNotBeSerialized(
                message: 'Payload checksum verification failed',
            );
        }

        $result = unserialize($payload->data, ['allowed_classes' => true]);

        if ($result === false && $payload->data !== 'b:0;') {
            throw new CachePayloadCouldNotBeSerialized(
                message: 'Failed to unserialize payload data',
            );
        }

        return $result;
    }

    public function canUnserialize(SerializedCachePayload $payload) : bool
    {
        return $payload->format === self::FORMAT;
    }

    #[Override]
    public function supportedType() : string
    {
        return self::FORMAT;
    }
}
