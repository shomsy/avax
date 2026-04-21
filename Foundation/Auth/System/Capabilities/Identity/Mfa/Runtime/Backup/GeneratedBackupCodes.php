<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup;

use SensitiveParameter;

/**
 * Internal pairing of plain-text backup codes and stored hashes.
 *
 * @param list<BackupCodeRecord> $records
 */
final readonly class GeneratedBackupCodes
{
    /** @var list<BackupCodeRecord> */
    public array         $records;
    public BackupCodeSet $backupCodeSet;

    /**
     * @param list<BackupCodeRecord> $records
     */
    public function __construct(
        #[SensitiveParameter] BackupCodeSet $backupCodeSet,
        array                               $records
    )
    {
        $this->backupCodeSet = $backupCodeSet;
        $this->records       = $records;
    }
}
