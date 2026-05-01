<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\RenamePasskey;

use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\PasskeyOperationFailed;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Support\PasskeyCredential;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Support\PasskeyCredentialStoreInterface;
use SensitiveParameter;

final readonly class RenamePasskey
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication $currentAuthentication,
        #[SensitiveParameter]
        private PasskeyCredentialStoreInterface $passkeyCredentialStore,
    ) {}

    /**
     * @throws PasskeyOperationFailed
     */
    public function execute(RenamePasskeyData $renamePasskeyData) : PasskeyCredential
    {
        $user = $this->currentAuthentication->read()->user();
        $credential = $this->passkeyCredentialStore->find(credentialId: $renamePasskeyData->credentialId);

        if (! $user instanceof AuthenticatedUser) {
            throw PasskeyOperationFailed::unauthenticated();
        }

        if (! $credential instanceof PasskeyCredential || $credential->userId !== $user->id) {
            throw PasskeyOperationFailed::notFound();
        }

        $label = trim(string: $renamePasskeyData->label);

        if ($label === '') {
            throw PasskeyOperationFailed::invalidLabel();
        }

        $this->passkeyCredentialStore->rename(credentialId: $credential->credentialId, label: $label);

        return $this->passkeyCredentialStore->find(credentialId: $credential->credentialId)
            ?? throw PasskeyOperationFailed::notFound();
    }
}
