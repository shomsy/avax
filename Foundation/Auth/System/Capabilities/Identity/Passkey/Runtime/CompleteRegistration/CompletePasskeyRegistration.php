<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteRegistration;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyOperationFailed;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyChallengeStoreInterface;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredential;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredentialStoreInterface;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyRuntimeInterface;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class CompletePasskeyRegistration
{
    public function __construct(
        #[SensitiveParameter] private CurrentAuthentication           $currentAuthentication,
        private PasskeyRuntimeInterface                               $runtime,
        #[SensitiveParameter] private PasskeyCredentialStoreInterface $credentialStore,
        private PasskeyChallengeStoreInterface                        $challengeStore,
        private AuditLogInterface                                     $auditLog,
        private Clock                                                 $clock,
        private string                                                $rpId
    )
    {
    }

    /**
     * @throws PasskeyOperationFailed
     */
    public function execute(CompletePasskeyRegistrationData $data) : PasskeyCredential
    {
        $user = $this->currentAuthentication->read()->user();

        if ($user === null) {
            throw PasskeyOperationFailed::unauthenticated();
        }

        $challenge = $this->challengeStore->find(challengeId: $data->challengeId);

        if ($challenge === null || $challenge->userId !== $user->id) {
            throw PasskeyOperationFailed::notFound();
        }

        if ($challenge->wasUsed()) {
            throw PasskeyOperationFailed::alreadyUsed();
        }

        if ($challenge->isExpiredAt(moment: $this->clock->now())) {
            $this->challengeStore->forget(challengeId: $data->challengeId);
            throw PasskeyOperationFailed::expired();
        }

        $resolved = $this->runtime->completeRegistration(
            rpId     : $this->rpId,
            challenge: $challenge->challenge,
            response : $data->response
        );

        $credential = new PasskeyCredential(
            userId      : $user->id,
            credentialId: $resolved->credentialId,
            label       : $resolved->label,
            registeredAt: $this->clock->now()
        );

        $this->credentialStore->save(credential: $credential);
        $this->challengeStore->markUsed(challengeId: $data->challengeId, usedAt: $this->clock->now());
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.passkey.registered',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'user_id'       => $user->id,
                                                           'credential_id' => $credential->credentialId,
                                                           'ip_address'    => $data->ipAddress,
                                                           'user_agent'    => $data->userAgent,
                                                       ]
                                       ));

        return $credential;
    }
}
