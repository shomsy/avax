<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Passkey\BeginAuthentication;

use Avax\Auth\System\Capability\Passkey\PasskeyChallengePurpose;
use Avax\Auth\System\Capability\Passkey\PasskeyChallengeRecord;
use Avax\Auth\System\Capability\Passkey\PasskeyChallengeStoreInterface;
use Avax\Auth\System\Capability\Passkey\PasskeyCredentialStoreInterface;
use Avax\Auth\System\Capability\Passkey\PasskeyRuntimeInterface;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;

final readonly class BeginPasskeyAuthentication
{
    public function __construct(
        private UserSourceInterface $userSource,
        private PasskeyRuntimeInterface $runtime,
        private PasskeyCredentialStoreInterface $credentialStore,
        private PasskeyChallengeStoreInterface $challengeStore,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        private string $rpId
    ) {}

    public function execute(BeginPasskeyAuthenticationData $data) : \Avax\Auth\System\Flow\Passkey\PasskeyAuthenticationChallenge
    {
        $challengeId = 'pkauth_' . bin2hex(random_bytes(12));
        $challenge   = bin2hex(random_bytes(32));
        $userId      = null;
        $allowIds    = [];

        if ($data->identifier !== null && $data->identifier !== '') {
            $user = $this->userSource->findByEmail($data->identifier);

            if ($user !== null) {
                $userId   = $user->getId()->value;
                $allowIds = array_map(
                    static fn ($credential) => $credential->credentialId,
                    $this->credentialStore->forUser($userId)
                );
            }
        }

        $this->challengeStore->issue(new PasskeyChallengeRecord(
            challengeId: $challengeId,
            challenge  : $challenge,
            purpose    : PasskeyChallengePurpose::AUTHENTICATION,
            expiresAt  : $this->clock->now()->modify('+5 minutes'),
            userId     : $userId
        ));

        $options = $this->runtime->beginAuthentication(
            rpId             : $this->rpId,
            challenge        : $challenge,
            allowCredentialIds: $allowIds
        );

        $this->auditLog->record(new AuditEvent(
            name      : 'auth.passkey.authentication.started',
            occurredAt: $this->clock->now(),
            context   : ['challenge_id' => $challengeId, 'user_id' => $userId]
        ));

        return new \Avax\Auth\System\Flow\Passkey\PasskeyAuthenticationChallenge($challengeId, $options);
    }
}
