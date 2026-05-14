<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\RevokePasskey;

use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyCredential;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyCredentialStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\PasskeyOperationFailed;
use SensitiveParameter;

final readonly class RevokePasskey
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication           $currentAuthentication,
        private RequireFreshMfa                 $requireFreshMfa,
        #[SensitiveParameter]
        private PasskeyCredentialStoreInterface $passkeyCredentialStore,
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

        if (! $user instanceof AuthenticatedUser) {
            throw PasskeyOperationFailed::unauthenticated();
        }

        $credential = $this->passkeyCredentialStore->find(credentialId: $credentialId);

        if (! $credential instanceof PasskeyCredential || $credential->userId !== $user->id) {
            throw PasskeyOperationFailed::notFound();
        }

        $this->requireFreshMfa->execute();
        $this->passkeyCredentialStore->revoke(credentialId: $credentialId, revokedAt: $this->clock->now());
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.passkey.revoked',
                                           occurredAt: $this->clock->now(),
                                           context   : ['user_id' => $user->id, 'credential_id' => $credentialId],
                                       ));
    }
}
