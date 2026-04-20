<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Passkey\RevokePasskey;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Passkey\PasskeyCredentialStoreInterface;
use Avax\Auth\System\Flows\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flows\Diagnostics\AuditEvent;
use Avax\Auth\System\Flows\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flows\Mfa\StepUp\RequireFreshMfa;
use Avax\Auth\System\Flows\Passkey\PasskeyOperationFailed;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class RevokePasskey
{
    private Clock                           $clock;
    private AuditLogInterface               $auditLog;
    private PasskeyCredentialStoreInterface $credentialStore;
    private RequireFreshMfa                 $requireFreshMfa;
    private CurrentAuthentication           $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication           $currentAuthentication,
        RequireFreshMfa                                       $requireFreshMfa,
        #[SensitiveParameter] PasskeyCredentialStoreInterface $credentialStore,
        AuditLogInterface                                     $auditLog,
        Clock                                                 $clock
    )
    {
        $this->currentAuthentication = $currentAuthentication;
        $this->requireFreshMfa       = $requireFreshMfa;
        $this->credentialStore       = $credentialStore;
        $this->auditLog              = $auditLog;
        $this->clock                 = $clock;
    }

    /**
     * @throws PasskeyOperationFailed
     * @throws Unauthenticated
     */
    public function execute(#[SensitiveParameter] string $credentialId) : void
    {
        $user = $this->currentAuthentication->read()->user();

        if ($user === null) {
            throw PasskeyOperationFailed::unauthenticated();
        }

        $credential = $this->credentialStore->find(credentialId: $credentialId);

        if ($credential === null || $credential->userId !== $user->id) {
            throw PasskeyOperationFailed::notFound();
        }

        $this->requireFreshMfa->execute();
        $this->credentialStore->revoke(credentialId: $credentialId, revokedAt: $this->clock->now());
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.passkey.revoked',
                                           occurredAt: $this->clock->now(),
                                           context   : ['user_id' => $user->id, 'credential_id' => $credentialId]
                                       ));
    }
}
