<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Passkey\ListPasskeys;

use Avax\Auth\System\Capability\Passkey\PasskeyCredential;
use Avax\Auth\System\Capability\Passkey\PasskeyCredentialStoreInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Passkey\PasskeyOperationFailed;

final readonly class ListPasskeys
{
    public function __construct(
        private CurrentAuthentication $currentAuthentication,
        private PasskeyCredentialStoreInterface $credentialStore
    ) {}

    /**
     * @return list<PasskeyCredential>
     * @throws PasskeyOperationFailed
     */
    public function execute() : array
    {
        $user = $this->currentAuthentication->read()->user();

        if ($user === null) {
            throw PasskeyOperationFailed::unauthenticated();
        }

        return $this->credentialStore->forUser($user->id);
    }
}
