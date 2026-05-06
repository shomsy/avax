<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Passkey;

use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyCredential;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\BeginAuthentication\BeginPasskeyAuthentication;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\BeginAuthentication\BeginPasskeyAuthenticationData;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\BeginRegistration\BeginPasskeyRegistration;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\CompleteAuthentication\CompletePasskeyAuthentication;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\CompleteAuthentication\CompletePasskeyAuthenticationData;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\CompleteRegistration\CompletePasskeyRegistration;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\CompleteRegistration\CompletePasskeyRegistrationData;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\ListPasskeys\ListPasskeys;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\PasskeyAuthenticationChallenge;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\PasskeyOperationFailed;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\PasskeyRegistration;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\RenamePasskey\RenamePasskey;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\RenamePasskey\RenamePasskeyData;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\RevokePasskey\RevokePasskey;
use DateMalformedStringException;
use Random\RandomException;
use SensitiveParameter;

final readonly class Passkey
{
    public function __construct(
        private ?BeginPasskeyRegistration $beginPasskeyRegistration,
        private ?CompletePasskeyRegistration $completePasskeyRegistration,
        #[SensitiveParameter]
        private ?BeginPasskeyAuthentication $beginPasskeyAuthentication,
        #[SensitiveParameter]
        private ?CompletePasskeyAuthentication $completePasskeyAuthentication,
        private ?ListPasskeys $listPasskeys,
        private ?RenamePasskey $renamePasskey,
        #[SensitiveParameter]
        private ?RevokePasskey $revokePasskey,
    ) {
    }

    public function isConfigured(): bool
    {
        return $this->beginPasskeyRegistration instanceof BeginPasskeyRegistration
            && $this->completePasskeyRegistration instanceof CompletePasskeyRegistration
            && $this->beginPasskeyAuthentication instanceof BeginPasskeyAuthentication
            && $this->completePasskeyAuthentication instanceof CompletePasskeyAuthentication
            && $this->listPasskeys instanceof ListPasskeys
            && $this->renamePasskey instanceof RenamePasskey
            && $this->revokePasskey instanceof RevokePasskey;
    }

    /**
     * @throws RandomException
     * @throws Unauthenticated
     * @throws DateMalformedStringException
     */
    public function beginPasskeyRegistration(): PasskeyRegistration
    {
        return $this->beginPasskeyRegistrationOrFail()->execute();
    }

    private function beginPasskeyRegistrationOrFail(): BeginPasskeyRegistration
    {
        return $this->beginPasskeyRegistration ?? throw PasskeyOperationFailed::runtimeNotConfigured();
    }

    public function completePasskeyRegistration(CompletePasskeyRegistrationData $completePasskeyRegistrationData): PasskeyCredential
    {
        return $this->completePasskeyRegistrationOrFail()->execute(data: $completePasskeyRegistrationData);
    }

    private function completePasskeyRegistrationOrFail(): CompletePasskeyRegistration
    {
        return $this->completePasskeyRegistration ?? throw PasskeyOperationFailed::runtimeNotConfigured();
    }

    /**
     * @throws RandomException
     * @throws DateMalformedStringException
     */
    public function beginPasskeyAuthentication(BeginPasskeyAuthenticationData $beginPasskeyAuthenticationData): PasskeyAuthenticationChallenge
    {
        return $this->beginPasskeyAuthenticationOrFail()->execute(data: $beginPasskeyAuthenticationData);
    }

    private function beginPasskeyAuthenticationOrFail(): BeginPasskeyAuthentication
    {
        return $this->beginPasskeyAuthentication ?? throw PasskeyOperationFailed::runtimeNotConfigured();
    }

    public function completePasskeyAuthentication(CompletePasskeyAuthenticationData $completePasskeyAuthenticationData): AuthenticationResult
    {
        return $this->completePasskeyAuthenticationOrFail()->execute(data: $completePasskeyAuthenticationData);
    }

    private function completePasskeyAuthenticationOrFail(): CompletePasskeyAuthentication
    {
        return $this->completePasskeyAuthentication ?? throw PasskeyOperationFailed::runtimeNotConfigured();
    }

    /**
     * @return list<PasskeyCredential>
     */
    public function readPasskeys(): array
    {
        return $this->readPasskeysOrFail()->execute();
    }

    private function readPasskeysOrFail(): ListPasskeys
    {
        return $this->listPasskeys ?? throw PasskeyOperationFailed::runtimeNotConfigured();
    }

    public function renamePasskey(RenamePasskeyData $renamePasskeyData): PasskeyCredential
    {
        return $this->renamePasskeyOrFail()->execute(data: $renamePasskeyData);
    }

    private function renamePasskeyOrFail(): RenamePasskey
    {
        return $this->renamePasskey ?? throw PasskeyOperationFailed::runtimeNotConfigured();
    }

    /**
     * @throws Unauthenticated
     */
    public function revokePasskey(#[SensitiveParameter] string $credentialId): void
    {
        $this->revokePasskeyOrFail()->execute(credentialId: $credentialId);
    }

    private function revokePasskeyOrFail(): RevokePasskey
    {
        return $this->revokePasskey ?? throw PasskeyOperationFailed::runtimeNotConfigured();
    }
}
