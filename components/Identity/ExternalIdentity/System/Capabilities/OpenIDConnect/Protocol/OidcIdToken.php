<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class OidcIdToken
{
    public function __construct(
        #[SensitiveParameter]
        public string            $token,
        public DateTimeImmutable $expiresAt,
    ) {}
}
