<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup;

use Avax\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Foundation\Clock;
use Random\RandomException;
use SensitiveParameter;

/**
 * Generates one-time MFA backup codes and their stored hashes.
 */
final readonly class GenerateBackupCodes
{
    public function __construct(
        #[SensitiveParameter] private PasswordHasher $passwordHasher,
        private Clock                                $clock,
        private int                                  $count = 10
    )
    {
    }

    /**
     * @throws RandomException
     */
    public function execute() : GeneratedBackupCodes
    {
        $plainCodes  = [];
        $records     = [];
        $generatedAt = $this->clock->now();

        for ($index = 0; $index < $this->count; $index++) {
            $plain        = strtoupper(bin2hex(random_bytes(4)) . '-' . bin2hex(random_bytes(4)));
            $plainCodes[] = new BackupCode(value: $plain);
            $records[]    = new BackupCodeRecord(
                backupCodeId: bin2hex(random_bytes(16)),
                codeHash    : $this->passwordHasher->hash(password: $plain)
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
