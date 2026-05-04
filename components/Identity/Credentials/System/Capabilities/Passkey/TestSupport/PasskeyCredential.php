<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Passkey\TestSupport;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class PasskeyCredential
{
    public function __construct(
        public int                $userId,
        #[SensitiveParameter]
        public string             $credentialId,
        public string             $label,
        public DateTimeImmutable  $registeredAt,
        public ?DateTimeImmutable $lastUsedAt = null,
        public ?DateTimeImmutable $revokedAt = null,
    )
    {
    }

    public function isRevoked(): bool
    {
        return $this->revokedAt instanceof DateTimeImmutable;
    }
}
