<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Backup;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * Stored one-time backup code hash.
 */
final readonly class BackupCodeRecord
{
    public function __construct(
        #[SensitiveParameter]
        public string             $backupCodeId,
        #[SensitiveParameter]
        public string             $codeHash,
        public ?DateTimeImmutable $usedAt = null,
    ) {}

    public function isUsed() : bool
    {
        return $this->usedAt instanceof DateTimeImmutable;
    }

    public function markUsed(DateTimeImmutable $moment) : self
    {
        return new self(
            backupCodeId: $this->backupCodeId,
            codeHash    : $this->codeHash,
            usedAt      : $moment,
        );
    }
}
