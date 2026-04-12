<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Passkey\CompleteRegistration;

use Avax\Auth\System\Capability\Passkey\PasskeyChallengeStoreInterface;
use Avax\Auth\System\Capability\Passkey\PasskeyCredential;
use Avax\Auth\System\Capability\Passkey\PasskeyCredentialStoreInterface;
use Avax\Auth\System\Capability\Passkey\PasskeyRuntimeInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Passkey\PasskeyOperationFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class CompletePasskeyRegistration
{
    public function __construct(
        private CurrentAuthentication $currentAuthentication,
        private PasskeyRuntimeInterface $runtime,
        private PasskeyCredentialStoreInterface $credentialStore,
        private PasskeyChallengeStoreInterface $challengeStore,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        private string $rpId
    ) {}

    /**
     * @throws PasskeyOperationFailed
     */
    public function execute(CompletePasskeyRegistrationData $data) : PasskeyCredential
    {
        $user = $this->currentAuthentication->read()->user();

        if ($user === null) {
            throw PasskeyOperationFailed::unauthenticated();
        }

        $challenge = $this->challengeStore->find($data->challengeId);

        if ($challenge === null || $challenge->userId !== $user->id) {
            throw PasskeyOperationFailed::notFound();
        }

        if ($challenge->wasUsed()) {
            throw PasskeyOperationFailed::alreadyUsed();
        }

        if ($challenge->isExpiredAt($this->clock->now())) {
            $this->challengeStore->forget($data->challengeId);
            throw PasskeyOperationFailed::expired();
        }

        $resolved = $this->runtime->completeRegistration(
            rpId      : $this->rpId,
            challenge : $challenge->challenge,
            response  : $data->response
        );

        $credential = new PasskeyCredential(
            userId      : $user->id,
            credentialId: $resolved->credentialId,
            label       : $resolved->label,
            registeredAt: $this->clock->now()
        );

        $this->credentialStore->save($credential);
        $this->challengeStore->markUsed($data->challengeId, $this->clock->now());
        $this->auditLog->record(new AuditEvent(
            name      : 'auth.passkey.registered',
            occurredAt: $this->clock->now(),
            context   : [
                'user_id' => $user->id,
                'credential_id' => $credential->credentialId,
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ]
        ));

        return $credential;
    }
}
