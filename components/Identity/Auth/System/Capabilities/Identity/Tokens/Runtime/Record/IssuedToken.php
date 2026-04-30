<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Record;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class IssuedToken
{
    public function __construct(
        #[SensitiveParameter]
        public string            $token,
        #[SensitiveParameter]
        public string            $tokenId,
        public DateTimeImmutable $expiresAt,
    ) {}
}
