<?php

declare(strict_types=1);

namespace Avax\Cache\System\Foundation\Compression;

use InvalidArgumentException;
use RuntimeException;

final readonly class GzipCompressor implements CacheCompressor
{
    public const ALGORITHM = 'gzip';
    public const LEVEL     = -1;

    public function compress(string $data) : CompressedCachePayload
    {
        $originalSize = strlen($data);
        $compressed   = gzcompress($data, level: self::LEVEL);

        if ($compressed === false) {
            throw new RuntimeException(message: 'Failed to compress data using gzip');
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
                message: sprintf('Cannot decompress payload with algorithm "%s"', $payload->algorithm)
            );
        }

        $decompressed = gzuncompress($payload->data);

        if ($decompressed === false) {
            throw new RuntimeException(message: 'Failed to decompress data');
        }

        return $decompressed;
    }

    public function algorithm() : string
    {
        return self::ALGORITHM;
    }
}