<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup;

use Avax\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaStoreInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Verifies and consumes one-time MFA backup codes.
 */
final readonly class VerifyBackupCode
{
    private Clock             $clock;
    private AuditLogInterface $auditLog;
    private PasswordHasher    $passwordHasher;
    private MfaStoreInterface $mfaStore;

    public function __construct(
        MfaStoreInterface                     $mfaStore,
        #[\SensitiveParameter] PasswordHasher $passwordHasher,
        AuditLogInterface                     $auditLog,
        Clock                                 $clock
    )
    {
        $this->mfaStore       = $mfaStore;
        $this->passwordHasher = $passwordHasher;
        $this->auditLog       = $auditLog;
        $this->clock          = $clock;
    }

    public function execute(UserId $userId, #[SensitiveParameter] string $code) : bool
    {
        $method = $this->mfaStore->findMethod(userId: $userId);

        if ($method === null) {
            return false;
        }

        foreach ($method->backupCodes as $index => $backupCode) {
            if ($backupCode->isUsed()) {
                continue;
            }

            if (! $this->passwordHasher->verify(password: $code, hash: $backupCode->codeHash)) {
                continue;
            }

            $codes         = $method->backupCodes;
            $codes[$index] = $backupCode->markUsed(moment: $this->clock->now());
            $this->mfaStore->saveMethod(
                record: $method->withBackupCodes(backupCodes: array_values($codes))
            );
            $this->auditLog->record(event: new AuditEvent(
                                               name      : 'auth.mfa.backup_code.used',
                                               occurredAt: $this->clock->now(),
                                               context   : [
                                                               'user_id'        => $userId->value,
                                                               'backup_code_id' => $backupCode->backupCodeId,
                                                           ]
                                           ));

            return true;
        }

        return false;
    }
}
