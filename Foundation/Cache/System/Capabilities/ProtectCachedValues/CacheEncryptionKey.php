<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ProtectCachedValues;

use InvalidArgumentException;

final readonly class CacheEncryptionKey
{
    private const MIN_KEY_LENGTH = 32;

    public function __construct(
        public string $key
    )
    {
        if (strlen($key) < self::MIN_KEY_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Encryption key must be at least %d bytes', self::MIN_KEY_LENGTH)
            );
        }
    }

    public static function fromEnvironment(string $envVar = 'CACHE_ENCRYPTION_KEY') : self
    {
        $key = getenv($envVar);

        if ($key === false || $key === '') {
            throw new InvalidArgumentException(
                sprintf('Environment variable "%s" is not set', $envVar)
            );
        }

        return new self($key);
    }

    public static function generate() : self
    {
        return new self(random_bytes(32));
    }

    public function toString() : string
    {
        return $this->key;
    }
}