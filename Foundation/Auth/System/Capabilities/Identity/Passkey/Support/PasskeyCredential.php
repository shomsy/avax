<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Passkey;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class PasskeyCredential
{
    public DateTimeImmutable|null $revokedAt;
    public DateTimeImmutable|null $lastUsedAt;
    public DateTimeImmutable      $registeredAt;
    public string                 $label;
    public string                 $credentialId;
    public int                    $userId;

    public function __construct(
        int                          $userId,
        #[SensitiveParameter] string $credentialId,
        string                       $label,
        DateTimeImmutable            $registeredAt,
        DateTimeImmutable|null       $lastUsedAt = null,
        DateTimeImmutable|null       $revokedAt = null
    )
    {
        $this->userId       = $userId;
        $this->credentialId = $credentialId;
        $this->label        = $label;
        $this->registeredAt = $registeredAt;
        $this->lastUsedAt   = $lastUsedAt;
        $this->revokedAt    = $revokedAt;
    }

    public function isRevoked() : bool
    {
        return $this->revokedAt !== null;
    }
}
