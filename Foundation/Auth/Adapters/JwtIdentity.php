<?php
/** @noinspection GlobalVariableUsageInspection */

declare(strict_types=1);

namespace Avax\Auth\Adapters;

use Carbon\Carbon;
use DomainException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Avax\Auth\Contracts\CredentialsInterface;
use Avax\Auth\Contracts\IdentityInterface;
use Avax\Auth\Contracts\UserInterface;
use Avax\Auth\Adapters\Identity;
use Avax\Auth\Adapters\UserDataSource;
use Psr\Log\LoggerInterface;
use UnexpectedValueException;

class JwtIdentity extends Identity implements AuthGuardInterface
{

    /**
     * JwtIdentity constructor.
     *
     * @param UserSourceInterface  $userProvider The user provider to retrieve users.
     * @param string               $secret       The JWT secret for encoding and decoding tokens.
     * @param int                  $tokenExpiry  The token expiration time in seconds.
     * @param LoggerInterface|null $logger       Optional logger for tracking authentication issues.
     */
    #[\Override]
    public function __construct(
        UserSourceInterface                   $userProvider,
        private readonly string               $secret,
        private readonly int                  $tokenExpiry = 3600,
        private readonly LoggerInterface|null $logger = null,
    ) {
        parent::__construct(userProvider: $userProvider);
    }

    /**
     * Attempts to authenticate a user based on provided credentials.
     *
     * @param CredentialsInterface $credentials Subject credentials.
     *
     * @return bool True if authentication is successful, otherwise false.
     * @throws \Exception
     * @throws \Exception
     */
    public function attempt(CredentialsInterface $credentials) : bool
    {
        return $this->authenticate(credentials: $credentials) !== null;
    }

    /**
     * Retrieves the currently authenticated user based on the JWT token in the Authorization header.
     *
     * @return UserInterface|null The authenticated user, or null if no valid token is found.
     * @throws \Exception
     * @throws \Exception
     */
    public function user() : UserInterface|null
    {
        $token = $this->getTokenFromHeader();

        if ($token === null || $token === '' || $token === '0') {
            $this->logger?->warning(message: "Authorization token not provided or invalid format.");

            return null;
        }

        try {
            $decoded = JWT::decode(jwt: $token, keyOrKeyArray: new Key(keyMaterial: $this->secret, algorithm: 'HS256'));

            if (isset($decoded->sub) && $this->isTokenValid(decodedToken: $decoded)) {
                return $this->userProvider->retrieveById(identifier: $decoded->sub);
            }

            $this->logger?->warning(message: "Invalid token claims or token expired.");
        } catch (UnexpectedValueException|DomainException $e) {
            $this->logger?->error(message: "JWT decoding failed: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Retrieves the JWT token from the Authorization header.
     *
     * @return string|null The JWT token if available, otherwise null.
     */
    private function getTokenFromHeader() : string|null
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (str_starts_with(haystack: (string) $authHeader, needle: 'Bearer ')) {
            return substr(string: (string) $authHeader, offset: 7);
        }

        return null;
    }

    /**
     * Validates the token claims, specifically expiration.
     *
     * @param object $decodedToken The decoded JWT token.
     *
     * @return bool True if the token is valid, otherwise false.
     */
    private function isTokenValid(object $decodedToken) : bool
    {
        return isset($decodedToken->exp) && $decodedToken->exp >= Carbon::now()->timestamp;
    }

    /**
     * Generates a JWT token for the authenticated user.
     *
     * @param UserInterface $user The user for whom the token is generated.
     *
     * @return string The generated JWT token.
     */
    public function generateToken(UserInterface $user) : string
    {
        $payload = [
            'sub' => $user->getId(),
            'exp' => Carbon::now()->timestamp + $this->tokenExpiry,
            'iat' => Carbon::now()->timestamp,
        ];

        return JWT::encode(payload: $payload, key: $this->secret, alg: 'HS256');
    }

    /**
     * Logs out the current user by invalidating the JWT (no action for stateless JWT).
     */
    public function logout() : void
    {
        // Stateless logout for JWT; clients must discard the token on logout.
    }
}
