<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\BeginRegistration;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyOperationFailed;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyRegistration;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyChallengePurpose;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyChallengeRecord;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyChallengeStoreInterface;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredentialStoreInterface;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyRuntimeInterface;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Foundation\Clock;
use DateMalformedStringException;
use Random\RandomException;
use SensitiveParameter;

final readonly class BeginPasskeyRegistration
{
    public function __construct(
        #[SensitiveParameter] private CurrentAuthentication           $currentAuthentication,
        private RequireFreshMfa                                       $requireFreshMfa,
        private PasskeyRuntimeInterface                               $runtime,
        #[SensitiveParameter] private PasskeyCredentialStoreInterface $credentialStore,
        private PasskeyChallengeStoreInterface                        $challengeStore,
        private AuditLogInterface                                     $auditLog,
        private Clock                                                 $clock,
        private string                                                $rpId,
        private string                                                $rpName
    )
    {
    }

    /**
     * @throws PasskeyOperationFailed
     * @throws DateMalformedStringException
     * @throws RandomException
     * @throws Unauthenticated
     */
    public function execute() : PasskeyRegistration
    {
        $user = $this->currentAuthentication->read()->user();

        if ($user === null) {
            throw PasskeyOperationFailed::unauthenticated();
        }

        $this->requireFreshMfa->execute();

        $challengeId = 'pkreg_' . bin2hex(string: random_bytes(length: 12));
        $challenge   = bin2hex(string: random_bytes(length: 32));
        $excludeIds  = array_map(
            callback: static fn (#[SensitiveParameter] $credential) => $credential->credentialId,
            array   : $this->credentialStore->forUser(userId: $user->id)
        );

        $this->challengeStore->issue(record: new PasskeyChallengeRecord(
                                                 challengeId: $challengeId,
                                                 challenge  : $challenge,
                                                 purpose    : PasskeyChallengePurpose::REGISTRATION,
                                                 expiresAt  : $this->clock->now()->modify(modifier: '+5 minutes'),
                                                 userId     : $user->id
                                             ));

        $options = $this->runtime->beginRegistration(
            rpId                : $this->rpId,
            rpName              : $this->rpName,
            userId              : $user->id,
            userName            : $user->username,
            displayName         : $user->email,
            challenge           : $challenge,
            excludeCredentialIds: $excludeIds
        );

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.passkey.registration.started',
                                           occurredAt: $this->clock->now(),
                                           context   : ['user_id' => $user->id, 'challenge_id' => $challengeId]
                                       ));

        return new PasskeyRegistration(challengeId: $challengeId, options: $options);
    }
}
