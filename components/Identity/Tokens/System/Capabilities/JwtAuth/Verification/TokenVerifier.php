<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\Verification;

use Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\Signing\JwtSigner;
use Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\Tokens\AccessToken;
use Avax\Components\Identity\Tokens\System\Foundation\Time\Clock;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;

final readonly class TokenVerifier
{
    public function __construct(
        private JwtSigner $jwtSigner,
        private Clock     $clock,
    ) {}

    public function verify(string $token) : AccessToken
    {
        $payload = $this->verifyPayload($token);

        return AccessToken::fromPayload($payload);
    }

    /**
     * @throws RuntimeException
     */
    public function verifyPayload(string $token) : array
    {
        try {
            $previousTimestamp = JWT::$timestamp;
            JWT::$timestamp    = $this->clock->now();

            try {
                $decoded = JWT::decode(
                    $token,
                    new Key($this->jwtSigner->getSecret(), $this->jwtSigner->getAlgo()),
                );
            } finally {
                JWT::$timestamp = $previousTimestamp;
            }

            $payload = (array) $decoded;

            if (($payload['exp'] ?? 0) < $this->clock->now()) {
                throw new RuntimeException('Token has expired');
            }

            return $payload;
        } catch (Exception $exception) {
            throw new RuntimeException('Invalid token: ' . $exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
