<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Tokens;

use Avax\Components\Identity\Foundation\Failures\TokenRejected;
use Avax\Components\Identity\Foundation\Values\SignedToken;
use Avax\Components\Identity\Foundation\Values\TokenSecret;

final readonly class HmacTokenCodec implements SignToken, VerifyToken
{
    public function __construct(private TokenSecret $secret) {}

    public function sign(TokenClaims $claims): SignedToken
    {
        $header = $this->encode(['typ' => 'AVAX', 'alg' => 'HS256']);
        $payload = $this->encode($claims->toPayload());
        $signature = $this->signature($header.'.'.$payload);

        return SignedToken::fromString($header.'.'.$payload.'.'.$signature);
    }

    public function verify(SignedToken $token): TokenClaims
    {
        $parts = explode('.', $token->toString());
        if (count($parts) !== 3) {
            throw TokenRejected::because('Token shape is invalid.');
        }

        [$header, $payload, $signature] = $parts;
        $expected = $this->signature($header.'.'.$payload);
        if (! hash_equals($expected, $signature)) {
            throw TokenRejected::because('Token signature is invalid.');
        }

        $decoded = $this->decode($payload);
        if (! is_array($decoded)) {
            throw TokenRejected::because('Token payload is invalid.');
        }

        return TokenClaims::fromPayload($decoded);
    }

    /** @param array<string, mixed> $payload */
    private function encode(array $payload): string
    {
        $json = json_encode($payload, JSON_THROW_ON_ERROR);

        return rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
    }

    /** @return mixed */
    private function decode(string $payload): mixed
    {
        $base64 = strtr($payload, '-_', '+/');
        $base64 .= str_repeat('=', (4 - strlen($base64) % 4) % 4);
        $json = base64_decode($base64, true);
        if ($json === false) {
            throw TokenRejected::because('Token base64 payload is invalid.');
        }

        return json_decode($json, true, flags: JSON_THROW_ON_ERROR);
    }

    private function signature(string $message): string
    {
        return rtrim(strtr(base64_encode(hash_hmac('sha256', $message, $this->secret->exposeForSigningOnly(), true)), '+/', '-_'), '=');
    }
}
