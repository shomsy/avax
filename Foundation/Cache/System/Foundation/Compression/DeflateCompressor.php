<?php

declare(strict_types=1);

namespace Avax\Cache\System\Foundation\Compression;

use InvalidArgumentException;
use RuntimeException;

final readonly class DeflateCompressor implements CacheCompressor
{
    public const ALGORITHM = 'deflate';
    public const LEVEL     = -1;

    public function compress(string $data) : CompressedCachePayload
    {
        $originalSize = strlen($data);
        $compressed   = zlib_encode($data, encoding: ZLIB_ENCODING_DEFLATE, level: self::LEVEL);

        if ($compressed === false) {
            throw new RuntimeException('Failed to compress data using deflate');
        }

        return new CompressedCachePayload(
            data          : $compressed,
            algorithm     : self::ALGORITHM,
            originalSize  : $originalSize,
            compressedSize: strlen($compressed)
        );
    }

    public function decompress(CompressedCachePayload $payload) : string
    {
        if ($payload->algorithm !== self::ALGORITHM) {
            throw new InvalidArgumentException(
                sprintf('Cannot decompress payload with algorithm "%s"', $payload->algorithm)
            );
        }

        $decompressed = zlib_decode($payload->data);

        if ($decompressed === false) {
            throw new RuntimeException('Failed to decompress data');
        }

        return $decompressed;
    }

    public function algorithm() : string
    {
        return self::ALGORITHM;
    }
}