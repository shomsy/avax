<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\BeginAuthentication;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyAuthenticationChallenge;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyChallengePurpose;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyChallengeRecord;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyChallengeStoreInterface;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredentialStoreInterface;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyRuntimeInterface;
use Avax\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Auth\System\Foundation\Clock;
use DateMalformedStringException;
use Random\RandomException;
use SensitiveParameter;

final readonly class BeginPasskeyAuthentication
{
    private string                          $rpId;
    private Clock                           $clock;
    private AuditLogInterface               $auditLog;
    private PasskeyChallengeStoreInterface  $challengeStore;
    private PasskeyCredentialStoreInterface $credentialStore;
    private PasskeyRuntimeInterface         $runtime;
    private UserSourceInterface             $userSource;

    public function __construct(
        UserSourceInterface                                   $userSource,
        PasskeyRuntimeInterface                               $runtime,
        #[SensitiveParameter] PasskeyCredentialStoreInterface $credentialStore,
        PasskeyChallengeStoreInterface                        $challengeStore,
        AuditLogInterface                                     $auditLog,
        Clock                                                 $clock,
        string                                                $rpId
    )
    {
        $this->userSource      = $userSource;
        $this->runtime         = $runtime;
        $this->credentialStore = $credentialStore;
        $this->challengeStore  = $challengeStore;
        $this->auditLog        = $auditLog;
        $this->clock           = $clock;
        $this->rpId            = $rpId;
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function execute(BeginPasskeyAuthenticationData $data) : PasskeyAuthenticationChallenge
    {
        $challengeId = 'pkauth_' . bin2hex(random_bytes(12));
        $challenge   = bin2hex(random_bytes(32));
        $userId      = null;
        $allowIds    = [];

        if ($data->identifier !== null && $data->identifier !== '') {
            $user = $this->userSource->findByEmail(email: $data->identifier);

            if ($user !== null) {
                $userId   = $user->getId()->value;
                $allowIds = array_map(
                    static fn (#[SensitiveParameter] $credential) => $credential->credentialId,
                    $this->credentialStore->forUser(userId: $userId)
                );
            }
        }

        $this->challengeStore->issue(record: new PasskeyChallengeRecord(
                                                 challengeId: $challengeId,
                                                 challenge  : $challenge,
                                                 purpose    : PasskeyChallengePurpose::AUTHENTICATION,
                                                 expiresAt  : $this->clock->now()->modify(modifier: '+5 minutes'),
                                                 userId     : $userId
                                             ));

        $options = $this->runtime->beginAuthentication(
            rpId              : $this->rpId,
            challenge         : $challenge,
            allowCredentialIds: $allowIds
        );

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.passkey.authentication.started',
                                           occurredAt: $this->clock->now(),
                                           context   : ['challenge_id' => $challengeId, 'user_id' => $userId]
                                       ));

        return new PasskeyAuthenticationChallenge(challengeId: $challengeId, options: $options);
    }
}
