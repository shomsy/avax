<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey;

use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\BeginAuthentication\BeginPasskeyAuthentication;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\BeginAuthentication\BeginPasskeyAuthenticationData;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\BeginRegistration\BeginPasskeyRegistration;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteAuthentication\CompletePasskeyAuthentication;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteAuthentication\CompletePasskeyAuthenticationData;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteRegistration\CompletePasskeyRegistration;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteRegistration\CompletePasskeyRegistrationData;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\ListPasskeys\ListPasskeys;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyAuthenticationChallenge;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyOperationFailed;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyRegistration;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\RenamePasskey\RenamePasskey;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\RenamePasskey\RenamePasskeyData;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\RevokePasskey\RevokePasskey;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredential;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationResult;
use DateMalformedStringException;
use Random\RandomException;
use SensitiveParameter;

final readonly class Passkey
{
    public function __construct(
        private BeginPasskeyRegistration|null      $beginPasskeyRegistration,
        private CompletePasskeyRegistration|null   $completePasskeyRegistration,
        #[SensitiveParameter]
        private BeginPasskeyAuthentication|null    $beginPasskeyAuthentication,
        #[SensitiveParameter]
        private CompletePasskeyAuthentication|null $completePasskeyAuthentication,
        private ListPasskeys|null                  $readPasskeys,
        private RenamePasskey|null                 $renamePasskey,
        #[SensitiveParameter]
        private RevokePasskey|null                 $revokePasskey,
    ) {}

    public function isConfigured() : bool
    {
        return $this->beginPasskeyRegistration !== null
            && $this->completePasskeyRegistration !== null
            && $this->beginPasskeyAuthentication !== null
            && $this->completePasskeyAuthentication !== null
            && $this->readPasskeys !== null
            && $this->renamePasskey !== null
            && $this->revokePasskey !== null;
    }

    /**
     * @throws RandomException
     * @throws Unauthenticated
     * @throws DateMalformedStringException
     */
    public function beginPasskeyRegistration() : PasskeyRegistration
    {
        return $this->beginPasskeyRegistrationOrFail()->execute();
    }

    private function beginPasskeyRegistrationOrFail() : BeginPasskeyRegistration
    {
        return $this->beginPasskeyRegistration ?? throw PasskeyOperationFailed::runtimeNotConfigured();
    }

    public function completePasskeyRegistration(CompletePasskeyRegistrationData $data) : PasskeyCredential
    {
        return $this->completePasskeyRegistrationOrFail()->execute(data: $data);
    }

    private function completePasskeyRegistrationOrFail() : CompletePasskeyRegistration
    {
        return $this->completePasskeyRegistration ?? throw PasskeyOperationFailed::runtimeNotConfigured();
    }

    /**
     * @throws RandomException
     * @throws DateMalformedStringException
     */
    public function beginPasskeyAuthentication(BeginPasskeyAuthenticationData $data) : PasskeyAuthenticationChallenge
    {
        return $this->beginPasskeyAuthenticationOrFail()->execute(data: $data);
    }

    private function beginPasskeyAuthenticationOrFail() : BeginPasskeyAuthentication
    {
        return $this->beginPasskeyAuthentication ?? throw PasskeyOperationFailed::runtimeNotConfigured();
    }

    public function completePasskeyAuthentication(CompletePasskeyAuthenticationData $data) : AuthenticationResult
    {
        return $this->completePasskeyAuthenticationOrFail()->execute(data: $data);
    }

    private function completePasskeyAuthenticationOrFail() : CompletePasskeyAuthentication
    {
        return $this->completePasskeyAuthentication ?? throw PasskeyOperationFailed::runtimeNotConfigured();
    }

    /**
     * @return list<PasskeyCredential>
     */
    public function readPasskeys() : array
    {
        return $this->readPasskeysOrFail()->execute();
    }

    private function readPasskeysOrFail() : ListPasskeys
    {
        return $this->readPasskeys ?? throw PasskeyOperationFailed::runtimeNotConfigured();
    }

    public function renamePasskey(RenamePasskeyData $data) : PasskeyCredential
    {
        return $this->renamePasskeyOrFail()->execute(data: $data);
    }

    private function renamePasskeyOrFail() : RenamePasskey
    {
        return $this->renamePasskey ?? throw PasskeyOperationFailed::runtimeNotConfigured();
    }

    /**
     * @throws Unauthenticated
     */
    public function revokePasskey(#[SensitiveParameter] string $credentialId) : void
    {
        $this->revokePasskeyOrFail()->execute(credentialId: $credentialId);
    }

    private function revokePasskeyOrFail() : RevokePasskey
    {
        return $this->revokePasskey ?? throw PasskeyOperationFailed::runtimeNotConfigured();
    }
}
