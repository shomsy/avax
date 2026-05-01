<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Backup;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Stores\MfaStoreInterface;
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;
use SensitiveParameter;

/**
 * Verifies and consumes one-time MFA backup codes.
 */
final readonly class VerifyBackupCode
{
    public function __construct(
        private MfaStoreInterface $mfaStore,
        #[SensitiveParameter]
        private PasswordHasher $passwordHasher,
        private AuditLogInterface $auditLog,
        private Clock $clock,
    ) {}

    public function execute(UserId $userId, #[SensitiveParameter] string $code): bool
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

            $codes = $method->backupCodes;
            $codes[$index] = $backupCode->markUsed(moment: $this->clock->now());
            $this->mfaStore->saveMethod(
                record: $method->withBackupCodes(backupCodes: array_values(array: $codes)),
            );
            $this->auditLog->record(event: new AuditEvent(
                name      : 'auth.mfa.backup_code.used',
                occurredAt: $this->clock->now(),
                context   : [
                    'user_id' => $userId->value,
                    'backup_code_id' => $backupCode->backupCodeId,
                ],
            ));

            return true;
        }

        return false;
    }
}
