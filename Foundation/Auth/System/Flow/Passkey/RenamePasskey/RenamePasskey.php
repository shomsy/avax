<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Passkey\RenamePasskey;

use Avax\Auth\System\Capability\Passkey\PasskeyCredential;
use Avax\Auth\System\Capability\Passkey\PasskeyCredentialStoreInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Passkey\PasskeyOperationFailed;
use SensitiveParameter;

final readonly class RenamePasskey
{
    public function __construct(
        #[SensitiveParameter] private CurrentAuthentication           $currentAuthentication,
        #[SensitiveParameter] private PasskeyCredentialStoreInterface $credentialStore
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

        $label = trim($data->label);

        if ($label === '') {
            throw PasskeyOperationFailed::invalidLabel();
        }

        $this->credentialStore->rename(credentialId: $credential->credentialId, label: $label);

        return $this->credentialStore->find(credentialId: $credential->credentialId)
            ?? throw PasskeyOperationFailed::notFound();
    }
}
