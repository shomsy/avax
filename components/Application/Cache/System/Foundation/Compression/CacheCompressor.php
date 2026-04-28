<?php

declare(strict_types=1);

namespace Avax\Cache\System\Foundation\Compression;

interface CacheCompressor
{
    public function compress(string $data) : CompressedCachePayload;

    public function decompress(CompressedCachePayload $payload) : string;

    public function algorithm() : string;
}