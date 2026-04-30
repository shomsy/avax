<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Foundation\Compression;

interface CacheCompressor
{
    public function compress(string $data) : CompressedCachePayload;

    public function decompress(CompressedCachePayload $compressedCachePayload) : string;

    public function algorithm() : string;
}
