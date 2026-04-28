<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class OidcIdToken
{
    public function __construct(
        #[SensitiveParameter] public string $token,
        public DateTimeImmutable            $expiresAt
    ) {}
}
