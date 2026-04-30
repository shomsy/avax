<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup;

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
        #[SensitiveParameter]
        public BackupCodeSet $backupCodeSet,
        public array         $records,
    ) {}
}
