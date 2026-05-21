<?php

declare(strict_types=1);

namespace Avax\Components\Identity\System\Configuration;

use RuntimeException;
use SensitiveParameter;

final readonly class IdentityConfiguration
{
    public function __construct(
        public string $provider = 'default',
        #[SensitiveParameter]
        public string|null $tokenSecret = null,
    ) {}

    public static function fromEnvironment() : self
    {
        $tokenSecret = $_ENV['TOKEN_SECRET']
            ?? $_SERVER['TOKEN_SECRET']
            ?? getenv('TOKEN_SECRET');

        return new self(
            tokenSecret: is_string($tokenSecret) ? $tokenSecret : null,
        );
    }

    public function requireTokenSecret() : string
    {
        $tokenSecret = trim($this->tokenSecret ?? '');

        if ($tokenSecret === '') {
            throw new RuntimeException(
                'TOKEN_SECRET is required to assemble Identity token capabilities.',
            );
        }

        return $tokenSecret;
    }
}
