<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\JarmResponse;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class JarmResponse
{
    public DateTimeImmutable $expiresAt;
    public string            $responseJwt;

    public function __construct(
        #[SensitiveParameter] string $responseJwt,
        DateTimeImmutable            $expiresAt
    )
    {
        $this->responseJwt = $responseJwt;
        $this->expiresAt   = $expiresAt;
    }
}
