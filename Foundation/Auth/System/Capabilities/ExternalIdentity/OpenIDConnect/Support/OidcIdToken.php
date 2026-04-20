<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class OidcIdToken
{
    public DateTimeImmutable $expiresAt;
    public string            $token;

    public function __construct(
        #[SensitiveParameter] string $token,
        DateTimeImmutable            $expiresAt
    )
    {
        $this->token     = $token;
        $this->expiresAt = $expiresAt;
    }
}
