<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\RevokePasskey;

use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyOperationFailed;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredentialStoreInterface;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class RevokePasskey
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication           $currentAuthentication,
        private RequireFreshMfa                 $requireFreshMfa,
        #[SensitiveParameter]
        private PasskeyCredentialStoreInterface $credentialStore,
        private AuditLogInterface               $auditLog,
        private Clock                           $clock,
    ) {}

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
                                           context   : ['user_id' => $user->id, 'credential_id' => $credentialId],
                                       ));
    }
}
