<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * Stored one-time backup code hash.
 */
final readonly class BackupCodeRecord
{
    public DateTimeImmutable|null $usedAt;
    public string                 $codeHash;
    public string                 $backupCodeId;

    public function __construct(
        #[SensitiveParameter] string $backupCodeId,
        #[SensitiveParameter] string $codeHash,
        DateTimeImmutable|null       $usedAt = null
    )
    {
        $this->backupCodeId = $backupCodeId;
        $this->codeHash     = $codeHash;
        $this->usedAt       = $usedAt;
    }

    public function isUsed() : bool
    {
        return $this->usedAt !== null;
    }

    public function markUsed(DateTimeImmutable $moment) : self
    {
        return new self(
            backupCodeId: $this->backupCodeId,
            codeHash    : $this->codeHash,
            usedAt      : $moment
        );
    }
}
