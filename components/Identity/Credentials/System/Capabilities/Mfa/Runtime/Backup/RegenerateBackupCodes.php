<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Backup;

use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Models\MfaChallengeFailed;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Records\MfaMethodRecord;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Stores\MfaStoreInterface;
use Random\RandomException;
use SensitiveParameter;

/**
 * Replaces MFA backup codes after fresh MFA proof.
 */
final readonly class RegenerateBackupCodes
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication $currentAuthentication,
        private RequireFreshMfa $requireFreshMfa,
        private MfaStoreInterface $mfaStore,
        #[SensitiveParameter]
        private GenerateBackupCodes $generateBackupCodes,
        private AuditLogInterface $auditLog,
        private Clock $clock,
    ) {}

    /**
     * @throws Unauthenticated
     * @throws RandomException
     */
    public function execute(): BackupCodeSet
    {
        $authenticationContext = $this->currentAuthentication->read();
        $user                  = $authenticationContext->user();

        if (! $user instanceof AuthenticatedUser) {
            throw new Unauthenticated();
        }

        $this->requireFreshMfa->execute();
        $userId = new UserId(value: $user->id);
        $method = $this->mfaStore->findMethod(userId: $userId);

        if (! $method instanceof MfaMethodRecord) {
            throw MfaChallengeFailed::notEnabled();
        }

        $generatedBackupCodes = $this->generateBackupCodes->execute();
        $this->mfaStore->saveMethod(record: new MfaMethodRecord(
            userId              : $method->userId,
            method              : $method->method,
            secret              : $method->secret,
            enabledAt           : $method->enabledAt,
            backupCodes         : $generatedBackupCodes->records,
            lastAcceptedTimeStep: $method->lastAcceptedTimeStep,
        ));
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.mfa.backup_codes.regenerated',
            occurredAt: $this->clock->now(),
            context   : [
                'user_id' => $user->id,
            ],
        ));

        return $generatedBackupCodes->backupCodeSet;
    }
}
