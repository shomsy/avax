<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\Runtime\CompleteRegistration;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyChallengeRecord;
use Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyChallengeStoreInterface;
use Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyCredential;
use Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyCredentialStoreInterface;
use Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyRuntimeInterface;
use Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\Runtime\PasskeyOperationFailed;
use SensitiveParameter;

final readonly class CompletePasskeyRegistration
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication           $currentAuthentication,
        private PasskeyRuntimeInterface         $passkeyRuntime,
        #[SensitiveParameter]
        private PasskeyCredentialStoreInterface $passkeyCredentialStore,
        private PasskeyChallengeStoreInterface  $passkeyChallengeStore,
        private AuditLogInterface               $auditLog,
        private Clock                           $clock,
        private string                          $rpId,
    ) {}

    /**
     * @throws PasskeyOperationFailed
     */
    public function execute(CompletePasskeyRegistrationData $completePasskeyRegistrationData) : PasskeyCredential
    {
        $user = $this->currentAuthentication->read()->user();

        if (! $user instanceof AuthenticatedUser) {
            throw PasskeyOperationFailed::unauthenticated();
        }

        $challenge = $this->passkeyChallengeStore->find(challengeId: $completePasskeyRegistrationData->challengeId);

        if (! $challenge instanceof PasskeyChallengeRecord || $challenge->userId !== $user->id) {
            throw PasskeyOperationFailed::notFound();
        }

        if ($challenge->wasUsed()) {
            throw PasskeyOperationFailed::alreadyUsed();
        }

        if ($challenge->isExpiredAt(moment: $this->clock->now())) {
            $this->passkeyChallengeStore->forget(challengeId: $completePasskeyRegistrationData->challengeId);

            throw PasskeyOperationFailed::expired();
        }

        $resolvedPasskeyCredential = $this->passkeyRuntime->completeRegistration(
            rpId     : $this->rpId,
            challenge: $challenge->challenge,
            response : $completePasskeyRegistrationData->response,
        );

        $passkeyCredential = new PasskeyCredential(
            userId      : $user->id,
            credentialId: $resolvedPasskeyCredential->credentialId,
            label       : $resolvedPasskeyCredential->label,
            registeredAt: $this->clock->now(),
        );

        $this->passkeyCredentialStore->save(credential: $passkeyCredential);
        $this->passkeyChallengeStore->markUsed(challengeId: $completePasskeyRegistrationData->challengeId, usedAt: $this->clock->now());
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.passkey.registered',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'user_id'       => $user->id,
                                                           'credential_id' => $passkeyCredential->credentialId,
                                                           'ip_address'    => $completePasskeyRegistrationData->ipAddress,
                                                           'user_agent'    => $completePasskeyRegistrationData->userAgent,
                                                       ],
                                       ));

        return $passkeyCredential;
    }
}
