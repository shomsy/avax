<?php

declare(strict_types=1);

namespace Avax\Tests\Support\Identity\Credentials\Passkey;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class PasskeyCredential
{
    public function __construct(
        public int $userId,
        #[SensitiveParameter]
        public string $credentialId,
        public string $label,
        public DateTimeImmutable $registeredAt,
        public DateTimeImmutable|null $lastUsedAt = null,
        public DateTimeImmutable|null $revokedAt = null,
    ) {
    }

    public function isRevoked(): bool
    {
        return $this->revokedAt instanceof DateTimeImmutable;
    }
}
