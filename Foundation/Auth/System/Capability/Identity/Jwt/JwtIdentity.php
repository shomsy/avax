<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Identity\Jwt;

use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * JWT identity implementation within the Auth System.
 */
final class JwtIdentity implements JwtIdentityInterface
{
    private User|null $currentUser = null;
    private string|null $currentToken = null;

    public function __construct(
        private UserSourceInterface           $userSource,
        #[\SensitiveParameter] private string $secret,
        private string                        $algorithm = 'HS256',
        private int                           $tokenExpiry = 3600
    ) {}

    public function issue(User $user) : string
    {
        if (! $user->isActive()) {
            throw new \InvalidArgumentException(message: 'Inactive users cannot be authenticated.');
        }

        $payload = [
            'iss' => 'avax-auth-system',
            'sub' => $user->getId()->value,
            'email' => $user->getEmail()->value,
            'iat' => time(),
            'exp' => time() + $this->tokenExpiry,
        ];

        $token = JWT::encode(payload: $payload, key: $this->secret, alg: $this->algorithm);
        $this->currentToken = $token;
        $this->currentUser = $user;

        return $token;
    }

    public function validate(#[\SensitiveParameter] string $token) : User|null
    {
        try {
            $decoded = JWT::decode(jwt: $token, keyOrKeyArray: new Key(keyMaterial: $this->secret, algorithm: $this->algorithm));
            $user = $this->userSource->findById(id: new UserId(value: (int) $decoded->sub));

            if ($user !== null && $user->isActive()) {
                return $user;
            }

            return null;
        } catch (\Exception) {
            return null;
        }
    }

    public function token() : string|null
    {
        return $this->refreshCurrentUser() !== null ? $this->currentToken : null;
    }

    public function getCurrentUser() : User|null
    {
        return $this->refreshCurrentUser();
    }

    public function clear() : void
    {
        $this->currentToken = null;
        $this->currentUser = null;
    }

    public function check() : bool
    {
        return $this->refreshCurrentUser() !== null;
    }

    public function authenticate(#[\SensitiveParameter] string $token) : void
    {
        $this->currentToken = $token;
        $this->currentUser = $this->validate(token: $token);

        if ($this->currentUser === null) {
            $this->currentToken = null;
        }
    }

    private function refreshCurrentUser() : User|null
    {
        if ($this->currentToken === null) {
            $this->currentUser = null;

            return null;
        }

        $this->currentUser = $this->validate(token: $this->currentToken);

        if ($this->currentUser === null) {
            $this->currentToken = null;
        }

        return $this->currentUser;
    }
}
