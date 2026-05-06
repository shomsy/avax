<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Foundation\Compression;

use InvalidArgumentException;
use Override;
use RuntimeException;

final readonly class GzipCompressor implements CacheCompressor
{
    public const string ALGORITHM = 'gzip';

    public const int LEVEL = -1;

    #[Override]
    public function compress(string $data): CompressedCachePayload
    {
        $originalSize = strlen($data);
        $compressed = gzcompress($data, level: self::LEVEL);

        if ($compressed === false) {
            throw new RuntimeException(message: 'Failed to compress data using gzip');
        }

        return new CompressedCachePayload(
            data          : $compressed,
            algorithm     : self::ALGORITHM,
            originalSize  : $originalSize,
            compressedSize: strlen($compressed),
        );
    }

    #[Override]
    public function decompress(CompressedCachePayload $compressedCachePayload): string
    {
        if ($compressedCachePayload->algorithm !== self::ALGORITHM) {
            throw new InvalidArgumentException(
                message: sprintf('Cannot decompress payload with algorithm "%s"', $compressedCachePayload->algorithm),
            );
        }

        $decompressed = gzuncompress($compressedCachePayload->data);

        if ($decompressed === false) {
            throw new RuntimeException(message: 'Failed to decompress data');
        }

        return $decompressed;
    }

    #[Override]
    public function algorithm(): string
    {
        return self::ALGORITHM;
    }
}
