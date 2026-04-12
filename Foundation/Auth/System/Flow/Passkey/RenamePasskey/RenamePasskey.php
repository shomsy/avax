<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Passkey\RenamePasskey;

use Avax\Auth\System\Capability\Passkey\PasskeyCredential;
use Avax\Auth\System\Capability\Passkey\PasskeyCredentialStoreInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Passkey\PasskeyOperationFailed;

final readonly class RenamePasskey
{
    public function __construct(
        private CurrentAuthentication $currentAuthentication,
        private PasskeyCredentialStoreInterface $credentialStore
    ) {}

    /**
     * @throws PasskeyOperationFailed
     */
    public function execute(RenamePasskeyData $data) : PasskeyCredential
    {
        $user       = $this->currentAuthentication->read()->user();
        $credential = $this->credentialStore->find($data->credentialId);

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

        $this->credentialStore->rename($credential->credentialId, $label);

        return $this->credentialStore->find($credential->credentialId)
            ?? throw PasskeyOperationFailed::notFound();
    }
}
