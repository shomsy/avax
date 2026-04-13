<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Passkey;

use DateTimeImmutable;

final readonly class PasskeyCredential
{
    public function __construct(
        public int                           $userId,
        #[\SensitiveParameter] public string $credentialId,
        public string                        $label,
        public DateTimeImmutable             $registeredAt,
        public DateTimeImmutable|null        $lastUsedAt = null,
        public DateTimeImmutable|null        $revokedAt = null
    ) {}

    public function isRevoked() : bool
    {
        return $this->revokedAt !== null;
    }
}
