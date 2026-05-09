<?php

declare(strict_types=1);

namespace Avax\Components\Foundation\CallableSerialization\System\Configuration;

final readonly class CallableSerializationConfig
{
    public function __construct(
        public string $signingKey = '',
        public string $algorithm = 'hmac-sha256',
        public string $version = '1',
    ) {}

    /**
     * @param array{signing_key?: string, algorithm?: string, version?: string} $config
     */
    public static function fromArray(array $config) : self
    {
        return new self(
            signingKey: $config['signing_key'] ?? '',
            algorithm : $config['algorithm'] ?? 'hmac-sha256',
            version   : $config['version'] ?? '1',
        );
    }

    public function hasSigningKey() : bool
    {
        return $this->signingKey !== '';
    }

    public function withSigningKey(string $key) : self
    {
        return new self(
            signingKey: $key,
            algorithm : $this->algorithm,
            version   : $this->version,
        );
    }
}
