<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Passkey\RevokePasskey;

use Avax\Auth\System\Capability\Passkey\PasskeyCredentialStoreInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Mfa\StepUp\RequireFreshMfa;
use Avax\Auth\System\Flow\Passkey\PasskeyOperationFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class RevokePasskey
{
    public function __construct(
        private CurrentAuthentication $currentAuthentication,
        private RequireFreshMfa $requireFreshMfa,
        private PasskeyCredentialStoreInterface $credentialStore,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * @throws PasskeyOperationFailed
     */
    public function execute(string $credentialId) : void
    {
        $user = $this->currentAuthentication->read()->user();

        if ($user === null) {
            throw PasskeyOperationFailed::unauthenticated();
        }

        $credential = $this->credentialStore->find($credentialId);

        if ($credential === null || $credential->userId !== $user->id) {
            throw PasskeyOperationFailed::notFound();
        }

        $this->requireFreshMfa->execute();
        $this->credentialStore->revoke($credentialId, $this->clock->now());
        $this->auditLog->record(new AuditEvent(
            name      : 'auth.passkey.revoked',
            occurredAt: $this->clock->now(),
            context   : ['user_id' => $user->id, 'credential_id' => $credentialId]
        ));
    }
}
