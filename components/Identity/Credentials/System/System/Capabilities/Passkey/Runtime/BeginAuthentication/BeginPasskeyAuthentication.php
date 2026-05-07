<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\Runtime\BeginAuthentication;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyChallengePurpose;
use Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyChallengeRecord;
use Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyChallengeStoreInterface;
use Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyCredential;
use Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyCredentialStoreInterface;
use Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyRuntimeInterface;
use Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\Runtime\PasskeyAuthenticationChallenge;
use DateMalformedStringException;
use Random\RandomException;
use SensitiveParameter;

final readonly class BeginPasskeyAuthentication
{
    public function __construct(
        private UserSourceInterface             $userSource,
        private PasskeyRuntimeInterface         $passkeyRuntime,
        #[SensitiveParameter]
        private PasskeyCredentialStoreInterface $passkeyCredentialStore,
        private PasskeyChallengeStoreInterface  $passkeyChallengeStore,
        private AuditLogInterface               $auditLog,
        private Clock                           $clock,
        private string                          $rpId,
    ) {}

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function execute(BeginPasskeyAuthenticationData $beginPasskeyAuthenticationData) : PasskeyAuthenticationChallenge
    {
        $challengeId = 'pkauth_' . bin2hex(string: random_bytes(length: 12));
        $challenge   = bin2hex(string: random_bytes(length: 32));
        $userId      = null;
        $allowIds    = [];

        if ($beginPasskeyAuthenticationData->identifier !== null && $beginPasskeyAuthenticationData->identifier !== '') {
            $user = $this->userSource->findByEmail(email: $beginPasskeyAuthenticationData->identifier);

            if ($user instanceof User) {
                $userId   = $user->getId()->value;
                $allowIds = array_map(
                    callback: static fn (#[SensitiveParameter] PasskeyCredential $passkeyCredential) : string => $passkeyCredential->credentialId,
                    array   : $this->passkeyCredentialStore->forUser(userId: $userId),
                );
            }
        }

        $this->passkeyChallengeStore->issue(record: new PasskeyChallengeRecord(
                                                        challengeId: $challengeId,
                                                        challenge  : $challenge,
                                                        purpose    : PasskeyChallengePurpose::AUTHENTICATION,
                                                        expiresAt  : $this->clock->now()->modify(modifier: '+5 minutes'),
                                                        userId     : $userId,
                                                    ));

        $options = $this->passkeyRuntime->beginAuthentication(
            rpId              : $this->rpId,
            challenge         : $challenge,
            allowCredentialIds: $allowIds,
        );

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.passkey.authentication.started',
                                           occurredAt: $this->clock->now(),
                                           context   : ['challenge_id' => $challengeId, 'user_id' => $userId],
                                       ));

        return new PasskeyAuthenticationChallenge(challengeId: $challengeId, options: $options);
    }
}
