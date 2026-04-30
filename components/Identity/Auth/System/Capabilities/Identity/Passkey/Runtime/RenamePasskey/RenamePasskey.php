<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\RenamePasskey;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyOperationFailed;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredential;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredentialStoreInterface;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

final readonly class RenamePasskey
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication           $currentAuthentication,
        #[SensitiveParameter]
        private PasskeyCredentialStoreInterface $credentialStore,
    ) {}

    /**
     * @throws PasskeyOperationFailed
     */
    public function execute(RenamePasskeyData $data) : PasskeyCredential
    {
        $user       = $this->currentAuthentication->read()->user();
        $credential = $this->credentialStore->find(credentialId: $data->credentialId);

        if ($user === null) {
            throw PasskeyOperationFailed::unauthenticated();
        }

        if ($credential === null || $credential->userId !== $user->id) {
            throw PasskeyOperationFailed::notFound();
        }

        $label = trim(string: $data->label);

        if ($label === '') {
            throw PasskeyOperationFailed::invalidLabel();
        }

        $this->credentialStore->rename(credentialId: $credential->credentialId, label: $label);

        return $this->credentialStore->find(credentialId: $credential->credentialId)
            ?? throw PasskeyOperationFailed::notFound();
    }
}
