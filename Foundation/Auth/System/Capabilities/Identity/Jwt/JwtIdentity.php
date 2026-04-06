<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Jwt;

use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Capabilities\User\UserId;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * JWT identity implementation within the Auth System.
 */
final class JwtIdentity implements JwtIdentityInterface
{
    private User|null $currentUser = null;

    public function __construct(
        private UserSourceInterface           $userSource,
        #[\SensitiveParameter] private string $secret,
        private string                        $algorithm = 'HS256',
        private int                           $tokenExpiry = 3600
    ) {}

    public function issue(User $user) : string
    {
        $payload = [
            'iss' => 'avax-auth-system',
            'sub' => $user->getId()->value,
            'email' => $user->getEmail()->value,
            'iat' => time(),
            'exp' => time() + $this->tokenExpiry,
        ];

        $token = JWT::encode($payload, $this->secret, $this->algorithm);
        $this->currentUser = $user;

        return $token;
    }

    public function validate(#[\SensitiveParameter] string $token) : User|null
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            return $this->userSource->findById(id: new UserId((int) $decoded->sub));
        } catch (\Exception) {
            return null;
        }
    }

    public function getCurrentUser() : User|null
    {
        return $this->currentUser;
    }

    public function clear() : void
    {
        $this->currentUser = null;
    }

    public function check() : bool
    {
        return $this->currentUser !== null;
    }

    public function authenticate(#[\SensitiveParameter] string $token) : void
    {
        $this->currentUser = $this->validate(token: $token);
    }
}
