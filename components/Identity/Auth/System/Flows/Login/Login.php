<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\Login;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;

/**
 * Login - Flow orchestrator for user authentication.
 * 1:1 alignment with refactor.md.
 */
final readonly class Login
{
    public function __construct(
        private FindUserByCredentials $findUserByCredentials,
        private VerifyPassword $verifyPassword,
        private StartAuthenticatedSession $startAuthenticatedSession,
        private Identity $identity,
    ) {
    }

    /**
     * @throws AuthenticationFailed
     */
    public function execute(Credentials $credentials): AuthenticationResult
    {
        $user = $this->findUserByCredentials->execute($credentials->email);

        if (! $user || ! $this->verifyPassword->execute($user, $credentials->password)) {
            throw new AuthenticationFailed('Invalid credentials');
        }

        $issuedAuthentication = $this->identity->issue($user);
        $this->startAuthenticatedSession->execute($issuedAuthentication);

        return new AuthenticationResult($user, $issuedAuthentication);
    }
}
