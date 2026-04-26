<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup;

use components\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use components\Auth\System\Foundation\Clock;
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
    ) {}

    /**
     * @throws RandomException
     */
    public function execute() : GeneratedBackupCodes
    {
        $plainCodes  = [];
        $records     = [];
        $generatedAt = $this->clock->now();

        for ($index = 0; $index < $this->count; $index++) {
            $plain        = strtoupper(string: bin2hex(string: random_bytes(length: 4)) . '-' . bin2hex(string: random_bytes(length: 4)));
            $plainCodes[] = new BackupCode(value: $plain);
            $records[]    = new BackupCodeRecord(
                backupCodeId: bin2hex(string: random_bytes(length: 16)),
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
