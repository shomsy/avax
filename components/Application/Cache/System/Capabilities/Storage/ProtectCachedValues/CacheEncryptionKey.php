<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\ProtectCachedValues;

use InvalidArgumentException;
use Random\RandomException;

final readonly class CacheEncryptionKey
{
    private const int MIN_KEY_LENGTH = 32;

    public function __construct(
        public string $key,
    )
    {
        if (strlen($key) < self::MIN_KEY_LENGTH) {
            throw new InvalidArgumentException(
                message: sprintf('Encryption key must be at least %d bytes', self::MIN_KEY_LENGTH),
            );
        }
    }

    public static function fromConfig(string $configKey = 'cache.encryption_key') : self
    {
        $key = config(key: $configKey);

        if ($key === null || $key === '') {
            throw new InvalidArgumentException(
                message: sprintf('Configuration key "%s" is not set', $configKey),
            );
        }

        return new self(key: (string) $key);
    }

    /**
     * @throws RandomException
     */
    public static function generate() : self
    {
        return new self(key: random_bytes(32));
    }

    public function toString() : string
    {
        return $this->key;
    }
}
