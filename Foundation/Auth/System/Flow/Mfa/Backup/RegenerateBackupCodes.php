<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Backup;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Mfa\BackupCodeSet;
use Avax\Auth\System\Flow\Mfa\MfaChallengeFailed;
use Avax\Auth\System\Flow\Mfa\MfaMethodRecord;
use Avax\Auth\System\Flow\Mfa\MfaStoreInterface;
use Avax\Auth\System\Flow\Mfa\StepUp\RequireFreshMfa;
use Avax\Auth\System\Foundation\Clock;

/**
 * Replaces MFA backup codes after fresh MFA proof.
 */
final readonly class RegenerateBackupCodes
{
    public function __construct(
        private CurrentAuthentication $currentAuthentication,
        private RequireFreshMfa       $requireFreshMfa,
        private MfaStoreInterface     $mfaStore,
        private GenerateBackupCodes   $generateBackupCodes,
        private AuditLogInterface     $auditLog,
        private Clock                 $clock
    ) {}

    /**
     * @throws Unauthenticated
     * @throws \Avax\Auth\System\Flow\Mfa\FreshMfaRequired
     * @throws MfaChallengeFailed
     */
    public function execute() : BackupCodeSet
    {
        $context = $this->currentAuthentication->read();
        $user    = $context->user();

        if ($user === null) {
            throw new Unauthenticated();
        }

        $this->requireFreshMfa->execute();
        $userId = new UserId($user->id);
        $method = $this->mfaStore->findMethod($userId);

        if ($method === null) {
            throw MfaChallengeFailed::notEnabled();
        }

        $generated = $this->generateBackupCodes->execute();
        $this->mfaStore->saveMethod(new MfaMethodRecord(
                                        userId              : $method->userId,
                                        method              : $method->method,
                                        secret              : $method->secret,
                                        enabledAt           : $method->enabledAt,
                                        backupCodes         : $generated->records,
                                        lastAcceptedTimeStep: $method->lastAcceptedTimeStep
                                    ));
        $this->auditLog->record(new AuditEvent(
                                    name      : 'auth.mfa.backup_codes.regenerated',
                                    occurredAt: $this->clock->now(),
                                    context   : [
                                                    'user_id' => $user->id,
                                                ]
                                ));

        return $generated->backupCodeSet;
    }
}
