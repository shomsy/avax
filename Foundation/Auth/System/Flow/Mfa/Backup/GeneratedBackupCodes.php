<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Backup;

use Avax\Auth\System\Flow\Mfa\BackupCodeRecord;
use Avax\Auth\System\Flow\Mfa\BackupCodeSet;
use SensitiveParameter;

/**
 * Internal pairing of plain-text backup codes and stored hashes.
 *
 * @param list<BackupCodeRecord> $records
 */
final readonly class GeneratedBackupCodes
{
    /**
     * @param list<BackupCodeRecord> $records
     */
    public function __construct(
        #[SensitiveParameter] public BackupCodeSet $backupCodeSet,
        public array                               $records
    ) {}
}
