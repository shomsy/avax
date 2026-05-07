<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OpenIDConnect\Runtime\JarmResponse;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class JarmResponse
{
    public function __construct(
        #[SensitiveParameter]
        public string            $responseJwt,
        public DateTimeImmutable $expiresAt,
    ) {}
}
