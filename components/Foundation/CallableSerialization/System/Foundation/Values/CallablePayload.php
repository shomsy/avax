<?php

declare(strict_types=1);

namespace Avax\Components\Foundation\CallableSerialization\System\Foundation\Values;

final readonly class CallablePayload
{
    public function __construct(
        public string $encoded,
        public string $signature,
        public string $version = '1',
        public string $algorithm = 'hmac-sha256',
    ) {}

    /**
     * @return array{encoded: string, signature: string, version: string, algorithm: string}
     */
    public function toArray() : array
    {
        return [
            'encoded'   => $this->encoded,
            'signature' => $this->signature,
            'version'   => $this->version,
            'algorithm' => $this->algorithm,
        ];
    }

    public function toJson() : string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * @param array{encoded?: string, signature?: string, version?: string, algorithm?: string} $data
     */
    public static function fromArray(array $data) : self
    {
        return new self(
            encoded   : $data['encoded'] ?? throw new \InvalidArgumentException('Missing encoded field'),
            signature : $data['signature'] ?? throw new \InvalidArgumentException('Missing signature field'),
            version   : $data['version'] ?? '1',
            algorithm : $data['algorithm'] ?? 'hmac-sha256',
        );
    }

    public static function fromJson(string $json) : self
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return self::fromArray($data);
    }
}
