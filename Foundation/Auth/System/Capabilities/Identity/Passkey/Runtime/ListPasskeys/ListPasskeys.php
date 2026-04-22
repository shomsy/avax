<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\ListPasskeys;

use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyOperationFailed;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredential;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredentialStoreInterface;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

final readonly class ListPasskeys
{
    public function __construct(
        #[SensitiveParameter] private CurrentAuthentication           $currentAuthentication,
        #[SensitiveParameter] private PasskeyCredentialStoreInterface $credentialStore
    )
    {
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
