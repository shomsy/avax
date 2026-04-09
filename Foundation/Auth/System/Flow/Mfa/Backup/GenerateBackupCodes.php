<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Backup;

use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Flow\Mfa\BackupCode;
use Avax\Auth\System\Flow\Mfa\BackupCodeRecord;
use Avax\Auth\System\Flow\Mfa\BackupCodeSet;
use Avax\Auth\System\Foundation\Clock;

/**
 * Generates one-time MFA backup codes and their stored hashes.
 */
final readonly class GenerateBackupCodes
{
    public function __construct(
        private PasswordHasher $passwordHasher,
        private Clock          $clock,
        private int            $count = 10
    ) {}

    public function execute() : GeneratedBackupCodes
    {
        $plainCodes  = [];
        $records     = [];
        $generatedAt = $this->clock->now();

        for ($index = 0; $index < $this->count; $index++) {
            $plain        = strtoupper(bin2hex(random_bytes(4)) . '-' . bin2hex(random_bytes(4)));
            $plainCodes[] = new BackupCode($plain);
            $records[]    = new BackupCodeRecord(
                backupCodeId: bin2hex(random_bytes(16)),
                codeHash    : $this->passwordHasher->hash($plain)
            );
        }

        return new GeneratedBackupCodes(
            backupCodeSet: new BackupCodeSet(
                               codes      : $plainCodes,
                               generatedAt: $generatedAt
                           ),
            records      : $records
        );
    }
}
