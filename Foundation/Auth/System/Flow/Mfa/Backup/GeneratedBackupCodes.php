<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Backup;

use Avax\Auth\System\Flow\Mfa\BackupCodeRecord;
use Avax\Auth\System\Flow\Mfa\BackupCodeSet;

/**
 * Internal pairing of plain-text backup codes and stored hashes.
 *
 * @param list<BackupCodeRecord> $records
 */
final readonly class GeneratedBackupCodes
{
    public function __construct(
        public BackupCodeSet $backupCodeSet,
        public array         $records
    ) {}
}
