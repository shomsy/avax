<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Foundation\Serialization;

interface CacheSerializer
{
    public function serialize(mixed $value): SerializedCachePayload;

    public function unserialize(SerializedCachePayload $serializedCachePayload): mixed;

    public function supportedType(): string;
}
