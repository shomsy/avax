<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\JarmResponse;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class JarmResponse
{
    public function __construct(
        #[SensitiveParameter] public string $responseJwt,
        public DateTimeImmutable            $expiresAt
    ) {}
}
