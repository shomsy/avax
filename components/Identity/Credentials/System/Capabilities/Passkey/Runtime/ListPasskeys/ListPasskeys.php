<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\ListPasskeys;

use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyCredential;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyCredentialStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\PasskeyOperationFailed;
use SensitiveParameter;

final readonly class ListPasskeys
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication           $currentAuthentication,
        #[SensitiveParameter]
        private PasskeyCredentialStoreInterface $passkeyCredentialStore,
    ) {}

    /**
     * @return list<PasskeyCredential>
     *
     * @throws PasskeyOperationFailed
     */
    public function execute() : array
    {
        $user = $this->currentAuthentication->read()->user();

        if (! $user instanceof AuthenticatedUser) {
            throw PasskeyOperationFailed::unauthenticated();
        }

        return $this->passkeyCredentialStore->forUser(userId: $user->id);
    }
}
