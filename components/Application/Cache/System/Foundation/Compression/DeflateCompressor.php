<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Foundation\Compression;

use InvalidArgumentException;
use Override;
use RuntimeException;

final readonly class DeflateCompressor implements CacheCompressor
{
    public const string ALGORITHM = 'deflate';

    public const int LEVEL = -1;

    #[Override]
    public function compress(string $data): CompressedCachePayload
    {
        $originalSize = strlen($data);
        $compressed   = zlib_encode($data, encoding: ZLIB_ENCODING_DEFLATE, level: self::LEVEL);

        if ($compressed === false) {
            throw new RuntimeException(message: 'Failed to compress data using deflate');
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

        $decompressed = zlib_decode($compressedCachePayload->data);

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
