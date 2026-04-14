<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\JarmResponse;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class JarmResponse
{
    public function __construct(
        #[SensitiveParameter] public string $responseJwt,
        public DateTimeImmutable $expiresAt
    ) {}
}
