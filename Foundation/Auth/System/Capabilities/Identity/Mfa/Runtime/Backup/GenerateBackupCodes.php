<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Mfa\Backup;

use Avax\Auth\System\Capabilities\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Flows\Mfa\BackupCode;
use Avax\Auth\System\Flows\Mfa\BackupCodeRecord;
use Avax\Auth\System\Flows\Mfa\BackupCodeSet;
use Avax\Auth\System\Foundation\Clock;
use Random\RandomException;
use SensitiveParameter;

/**
 * Generates one-time MFA backup codes and their stored hashes.
 */
final readonly class GenerateBackupCodes
{
    private int            $count;
    private Clock          $clock;
    private PasswordHasher $passwordHasher;

    public function __construct(
        #[SensitiveParameter] PasswordHasher $passwordHasher,
        Clock                                $clock,
        int                                  $count = 10
    )
    {
        $this->passwordHasher = $passwordHasher;
        $this->clock          = $clock;
        $this->count          = $count;
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
