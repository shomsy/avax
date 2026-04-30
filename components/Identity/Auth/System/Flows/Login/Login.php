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
        private FindUserByCredentials     $findUser,
        private VerifyPassword            $verifyPassword,
        private StartAuthenticatedSession $startSession,
        private Identity                  $identity,
    ) {}

    /**
     * @throws AuthenticationFailed
     */
    public function execute(Credentials $credentials) : AuthenticationResult
    {
        $user = $this->findUser->execute($credentials->email);

        if (! $user || ! $this->verifyPassword->execute($user, $credentials->password)) {
            throw new AuthenticationFailed('Invalid credentials');
        }

        $issued = $this->identity->issue($user);
        $this->startSession->execute($issued);

        return new AuthenticationResult($user, $issued);
    }
}
