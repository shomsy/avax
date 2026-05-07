<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\System\Capabilities\Tokens\Runtime\Record;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class IssuedRefreshToken
{
    public function __construct(
        #[SensitiveParameter]
        public string             $token,
        #[SensitiveParameter]
        public string             $tokenId,
        public DateTimeImmutable  $expiresAt,
        public string             $familyId,
        public ?DateTimeImmutable $mfaVerifiedAt = null,
        public bool               $phishingResistant = false,
    ) {}
}
