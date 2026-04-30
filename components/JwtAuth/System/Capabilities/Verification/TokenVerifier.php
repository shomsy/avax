<?php

declare(strict_types=1);

namespace Avax\Components\JwtAuth\System\Capabilities\Verification;

use Avax\Components\JwtAuth\System\Capabilities\Tokens\AccessToken;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;

final class TokenVerifier
{
    private JwtSigner $signer;

    public function __construct(JwtSigner $signer)
    {
        $this->signer = $signer;
    }

    public function verify(string $token) : AccessToken
    {
        $payload = $this->verifyPayload($token);

        return AccessToken::fromPayload($payload);
    }

    public function verifyPayload(string $token) : array
    {
        try {
            $decoded = JWT::decode(
                $token,
                new Key($this->signer->getSecret(), $this->signer->getAlgo()),
            );

            $payload = (array) $decoded;

            if (($payload['exp'] ?? 0) < time()) {
                throw new RuntimeException('Token has expired');
            }

            return $payload;
        } catch (Exception $e) {
            throw new RuntimeException('Invalid token: ' . $e->getMessage());
        }
    }
}