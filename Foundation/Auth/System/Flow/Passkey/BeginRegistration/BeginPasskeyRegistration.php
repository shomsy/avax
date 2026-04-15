<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Passkey\BeginRegistration;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Passkey\PasskeyChallengePurpose;
use Avax\Auth\System\Capability\Passkey\PasskeyChallengeRecord;
use Avax\Auth\System\Capability\Passkey\PasskeyChallengeStoreInterface;
use Avax\Auth\System\Capability\Passkey\PasskeyCredentialStoreInterface;
use Avax\Auth\System\Capability\Passkey\PasskeyRuntimeInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Mfa\StepUp\RequireFreshMfa;
use Avax\Auth\System\Flow\Passkey\PasskeyOperationFailed;
use Avax\Auth\System\Flow\Passkey\PasskeyRegistration;
use Avax\Auth\System\Foundation\Clock;
use Random\RandomException;
use SensitiveParameter;

final readonly class BeginPasskeyRegistration
{
    private string                          $rpName;
    private string                          $rpId;
    private Clock                           $clock;
    private AuditLogInterface               $auditLog;
    private PasskeyChallengeStoreInterface  $challengeStore;
    private PasskeyCredentialStoreInterface $credentialStore;
    private PasskeyRuntimeInterface         $runtime;
    private RequireFreshMfa                 $requireFreshMfa;
    private CurrentAuthentication           $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication           $currentAuthentication,
        RequireFreshMfa                                       $requireFreshMfa,
        PasskeyRuntimeInterface                               $runtime,
        #[SensitiveParameter] PasskeyCredentialStoreInterface $credentialStore,
        PasskeyChallengeStoreInterface                        $challengeStore,
        AuditLogInterface                                     $auditLog,
        Clock                                                 $clock,
        string                                                $rpId,
        string                                                $rpName
    )
    {
        $this->currentAuthentication = $currentAuthentication;
        $this->requireFreshMfa       = $requireFreshMfa;
        $this->runtime               = $runtime;
        $this->credentialStore       = $credentialStore;
        $this->challengeStore        = $challengeStore;
        $this->auditLog              = $auditLog;
        $this->clock                 = $clock;
        $this->rpId                  = $rpId;
        $this->rpName                = $rpName;
    }

    /**
     * @throws PasskeyOperationFailed
     * @throws \DateMalformedStringException
     * @throws RandomException
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

        $challengeId = 'pkreg_' . bin2hex(random_bytes(12));
        $challenge   = bin2hex(random_bytes(32));
        $excludeIds  = array_map(
            static fn (#[SensitiveParameter] $credential) => $credential->credentialId,
            $this->credentialStore->forUser(userId: $user->id)
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
