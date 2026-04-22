<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\MfaChallengeFailed;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Records\MfaMethodRecord;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Stores\MfaStoreInterface;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Foundation\Clock;
use Random\RandomException;
use SensitiveParameter;

/**
 * Replaces MFA backup codes after fresh MFA proof.
 */
final readonly class RegenerateBackupCodes
{
    public function __construct(
        #[SensitiveParameter] private CurrentAuthentication $currentAuthentication,
        private RequireFreshMfa                             $requireFreshMfa,
        private MfaStoreInterface                           $mfaStore,
        #[SensitiveParameter] private GenerateBackupCodes   $generateBackupCodes,
        private AuditLogInterface                           $auditLog,
        private Clock                                       $clock
    )
    {
    }

    /**
     * @return BackupCodeSet
     * @throws Unauthenticated
     * @throws RandomException
     */
    public function execute() : BackupCodeSet
    {
        $context = $this->currentAuthentication->read();
        $user    = $context->user();

        if ($user === null) {
            throw new Unauthenticated();
        }

        $this->requireFreshMfa->execute();
        $userId = new UserId(value: $user->id);
        $method = $this->mfaStore->findMethod(userId: $userId);

        if ($method === null) {
            throw MfaChallengeFailed::notEnabled();
        }

        $generated = $this->generateBackupCodes->execute();
        $this->mfaStore->saveMethod(record: new MfaMethodRecord(
                                                userId              : $method->userId,
                                                method              : $method->method,
                                                secret              : $method->secret,
                                                enabledAt           : $method->enabledAt,
                                                backupCodes         : $generated->records,
                                                lastAcceptedTimeStep: $method->lastAcceptedTimeStep
                                            ));
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.mfa.backup_codes.regenerated',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'user_id' => $user->id,
                                                       ]
                                       ));

        return $generated->backupCodeSet;
    }
}
