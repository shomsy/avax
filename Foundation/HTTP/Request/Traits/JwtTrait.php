<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Traits;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;

/**
 * JwtTrait provides methods for handling JWT (JSON Web Token) generation and decoding.
 * The class that uses this trait must implement the `getHeaderLine` method.
 *
 * This trait handles:
 * - Setting up JWT secret and algorithm.
 * - Generating JWT with provided payload and expiration.
 * - Extracting JWT from HTTP Authorization header.
 * - Decoding the JWT to retrieve authenticated user details.
 */
trait JwtTrait
{
    /**
     * Prefix used for Authorization header.
     */
    private const string JWT_PREFIX = 'Bearer ';

    /**
     * Secret key used for encoding and decoding JWT.
     */
    private string $jwtSecret;

    /**
     * Algorithm used for encoding and decoding JWT. Defaults to 'HS256'.
     */
    private string $jwtAlgorithm = 'HS256';

    /**
     * Sets the JWT secret.
     *
     * @param string $secret The secret key for JWT.
     */
    public function setJwtSecret(string $secret) : void
    {
        $this->jwtSecret = $secret;
    }

    /**
     * Generates a JWT for a given payload and expiration time.
     *
     * The issued time (iat) and expiration time (exp) are added to the payload.
     *
     * @param array $payload    The data to be encoded in the JWT.
     * @param int   $expiration The expiration time of the token in seconds.
     *
     * @return string The generated JWT.
     */
    public function generateJwtToken(array $payload, int $expiration = 3600) : string
    {
        $issuedAt       = Carbon::now()->timestamp;
        $payload['iat'] = $issuedAt;
        $payload['exp'] = $issuedAt + $expiration;

        return JWT::encode(
            payload: $payload,
            key    : $this->jwtSecret,
            alg    : $this->jwtAlgorithm,
        );
    }

    /**
     * Retrieves and decodes JWT from the Authorization header to get the authenticated user.
     *
     * @return object|null Decoded JWT payload if token is valid, otherwise null.
     */
    public function getAuthenticatedUser() : object|null
    {
        $token = $this->extractBearerToken();

        return $token ? $this->decodeJwt(token: $token) : null;
    }

    /**
     * Extracts the Bearer token from the Authorization header.
     *
     * @return string|null The JWT if present, otherwise null.
     * @throws RuntimeException If the `getHeaderLine` method is not defined in the class using this trait.
     */
    private function extractBearerToken() : string|null
    {
        if (! $this->hasMethod(methodName: 'getHeaderLine')) {
            throw new RuntimeException(
                message: 'The getHeaderLine method must be defined in the class using JwtTrait.',
            );
        }

        $authHeader = $this->getHeaderLine(name: 'Authorization');

        // Ensure the Authorization header starts with "Bearer ".
        return str_starts_with(haystack: (string) $authHeader, needle: self::JWT_PREFIX) ?
            substr(string: (string) $authHeader, offset: strlen(string: self::JWT_PREFIX)) : null;
    }

    /**
     * Checks if a method exists in the calling class.
     *
     * @param string $methodName The name of the method to check for.
     *
     * @return bool True if the method exists, false otherwise.
     */
    private function hasMethod(string $methodName) : bool
    {
        return method_exists(object_or_class: $this, method: $methodName);
    }

    /**
     * Decodes a JWT to its payload.
     *
     * @param string $token The JWT to decode.
     *
     * @return object|null The decoded payload if the JWT is valid, null otherwise.
     * @throws RuntimeException If the token is malformed or invalid.
     */
    public function decodeJwt(string $token) : object|null
    {
        return JWT::decode(
            jwt          : $token,
            keyOrKeyArray: $this->composeKey(),
        );
    }

    /**
     * Composes the key for decoding the JWT based on the secret and algorithm.
     *
     * @return Key The key used for JWT decoding.
     */
    private function composeKey() : Key
    {
        return new Key(
            keyMaterial: $this->jwtSecret,
            algorithm  : $this->jwtAlgorithm,
        );
    }
}
