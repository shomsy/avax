<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\BeginAuthentication;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\PasskeyAuthenticationChallenge;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Support\PasskeyChallengePurpose;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Support\PasskeyChallengeRecord;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Support\PasskeyChallengeStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Support\PasskeyCredentialStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Support\PasskeyRuntimeInterface;
use DateMalformedStringException;
use Random\RandomException;
use SensitiveParameter;

final readonly class BeginPasskeyAuthentication
{
    public function __construct(
        private UserSourceInterface $userSource,
        private PasskeyRuntimeInterface $runtime,
        #[SensitiveParameter]
        private PasskeyCredentialStoreInterface $credentialStore,
        private PasskeyChallengeStoreInterface $challengeStore,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        private string $rpId,
    ) {}

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function execute(BeginPasskeyAuthenticationData $data): PasskeyAuthenticationChallenge
    {
        $challengeId = 'pkauth_' . bin2hex(string: random_bytes(length: 12));
        $challenge   = bin2hex(string: random_bytes(length: 32));
        $userId      = null;
        $allowIds    = [];

        if ($data->identifier !== null && $data->identifier !== '') {
            $user = $this->userSource->findByEmail(email: $data->identifier);

            if ($user !== null) {
                $userId = $user->getId()->value;
                $allowIds = array_map(
                    callback: static fn (#[SensitiveParameter] $credential) => $credential->credentialId,
                    array   : $this->credentialStore->forUser(userId: $userId),
                );
            }
        }

        $this->challengeStore->issue(record: new PasskeyChallengeRecord(
            challengeId: $challengeId,
            challenge  : $challenge,
            purpose    : PasskeyChallengePurpose::AUTHENTICATION,
            expiresAt  : $this->clock->now()->modify(modifier: '+5 minutes'),
            userId     : $userId,
        ));

        $options = $this->runtime->beginAuthentication(
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
