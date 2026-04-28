<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * Stored one-time backup code hash.
 */
final readonly class BackupCodeRecord
{
    public function __construct(
        #[SensitiveParameter] public string $backupCodeId,
        #[SensitiveParameter] public string $codeHash,
        public DateTimeImmutable|null       $usedAt = null
    ) {}

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
