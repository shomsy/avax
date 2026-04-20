<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Passkey\ListPasskeys;

use Avax\Auth\System\Capabilities\Passkey\PasskeyCredential;
use Avax\Auth\System\Capabilities\Passkey\PasskeyCredentialStoreInterface;
use Avax\Auth\System\Flows\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flows\Passkey\PasskeyOperationFailed;
use SensitiveParameter;

final readonly class ListPasskeys
{
    private PasskeyCredentialStoreInterface $credentialStore;
    private CurrentAuthentication           $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication           $currentAuthentication,
        #[SensitiveParameter] PasskeyCredentialStoreInterface $credentialStore
    )
    {
        $this->currentAuthentication = $currentAuthentication;
        $this->credentialStore       = $credentialStore;
    }

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

        return $this->credentialStore->forUser(userId: $user->id);
    }
}
