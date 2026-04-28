<?php

declare(strict_types=1);

namespace Avax\Cache\System\Foundation\Serialization;

interface CacheSerializer
{
    public function serialize(mixed $value) : SerializedCachePayload;

    public function unserialize(SerializedCachePayload $payload) : mixed;

    public function supportedType() : string;
}