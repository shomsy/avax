<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration\Assembly;

use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\AttemptThrottle;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Limit\LimitMfaAttempts;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Stores\MfaStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Totp\TotpInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\MfaChallengeStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyChallengeStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyCredentialStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyRuntimeInterface;
use SensitiveParameter;

/**
 * Configuration sub-builder for credential-related dependencies.
 *
 * Owns: MFA store, TOTP, challenge store, attempt limits, throttles,
 * passkey runtime, stores, and relying party configuration.
 */
final class BuildCredentialGraph
{
    private MfaStoreInterface|null $mfaStore = null;
    private MfaChallengeStoreInterface|null $mfaChallengeStore = null;
    private TotpInterface|null $totp = null;
    private LimitMfaAttempts|null $limitMfaAttempts = null;
    private AttemptThrottle|null $passwordResetThrottle = null;
    private AttemptThrottle|null $mfaRecoveryThrottle = null;
    private PasskeyRuntimeInterface|null $passkeyRuntime = null;
    private PasskeyCredentialStoreInterface|null $passkeyCredentialStore = null;
    private PasskeyChallengeStoreInterface|null $passkeyChallengeStore = null;
    private string $mfaIssuer = 'Avax Auth';
    private string $passkeyRpId = 'localhost';
    private string $passkeyRpName = 'Avax Auth';

    public function withMfaStore(MfaStoreInterface $mfaStore) : self
    {
        $this->mfaStore = $mfaStore;

        return $this;
    }

    public function withMfaChallengeStore(MfaChallengeStoreInterface $mfaChallengeStore) : self
    {
        $this->mfaChallengeStore = $mfaChallengeStore;

        return $this;
    }

    public function usingTotp(TotpInterface $totp) : self
    {
        $this->totp = $totp;

        return $this;
    }

    public function withMfaAttemptLimit(LimitMfaAttempts $limitMfaAttempts) : self
    {
        $this->limitMfaAttempts = $limitMfaAttempts;

        return $this;
    }

    public function withPasswordResetThrottle(#[SensitiveParameter] AttemptThrottle $attemptThrottle) : self
    {
        $this->passwordResetThrottle = $attemptThrottle;

        return $this;
    }

    public function withMfaRecoveryThrottle(AttemptThrottle $attemptThrottle) : self
    {
        $this->mfaRecoveryThrottle = $attemptThrottle;

        return $this;
    }

    public function withPasskeyRuntime(PasskeyRuntimeInterface $passkeyRuntime) : self
    {
        $this->passkeyRuntime = $passkeyRuntime;

        return $this;
    }

    public function withPasskeyCredentialStore(#[SensitiveParameter] PasskeyCredentialStoreInterface $passkeyCredentialStore) : self
    {
        $this->passkeyCredentialStore = $passkeyCredentialStore;

        return $this;
    }

    public function withPasskeyChallengeStore(PasskeyChallengeStoreInterface $passkeyChallengeStore) : self
    {
        $this->passkeyChallengeStore = $passkeyChallengeStore;

        return $this;
    }

    public function withPasskeyRelyingParty(string $rpId, string $rpName) : self
    {
        $this->passkeyRpId   = $rpId;
        $this->passkeyRpName = $rpName;

        return $this;
    }

    public function withMfaIssuer(string $mfaIssuer) : self
    {
        $this->mfaIssuer = $mfaIssuer;

        return $this;
    }

    /**
     * @return array{
     *     mfaStore: MfaStoreInterface|null,
     *     mfaChallengeStore: MfaChallengeStoreInterface|null,
     *     totp: TotpInterface|null,
     *     limitMfaAttempts: LimitMfaAttempts|null,
     *     passwordResetThrottle: AttemptThrottle|null,
     *     mfaRecoveryThrottle: AttemptThrottle|null,
     *     passkeyRuntime: PasskeyRuntimeInterface|null,
     *     passkeyCredentialStore: PasskeyCredentialStoreInterface|null,
     *     passkeyChallengeStore: PasskeyChallengeStoreInterface|null,
     *     mfaIssuer: string,
     *     passkeyRpId: string,
     *     passkeyRpName: string,
     * }
     */
    public function build() : array
    {
        return [
            'mfaStore'                 => $this->mfaStore,
            'mfaChallengeStore'        => $this->mfaChallengeStore,
            'totp'                     => $this->totp,
            'limitMfaAttempts'         => $this->limitMfaAttempts,
            'passwordResetThrottle'    => $this->passwordResetThrottle,
            'mfaRecoveryThrottle'      => $this->mfaRecoveryThrottle,
            'passkeyRuntime'           => $this->passkeyRuntime,
            'passkeyCredentialStore'   => $this->passkeyCredentialStore,
            'passkeyChallengeStore'    => $this->passkeyChallengeStore,
            'mfaIssuer'                => $this->mfaIssuer,
            'passkeyRpId'              => $this->passkeyRpId,
            'passkeyRpName'            => $this->passkeyRpName,
        ];
    }
}
