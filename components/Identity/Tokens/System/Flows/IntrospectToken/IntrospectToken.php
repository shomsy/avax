<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Flows\IntrospectToken;

use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec\TokenCodecInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\TokenRevocationStoreInterface;
use DateTimeImmutable;
use SensitiveParameter;

final readonly class IntrospectToken
{
    public function __construct(
        private TokenCodecInterface           $tokenCodec,
        private TokenRevocationStoreInterface $tokenRevocationStore,
    ) {}

    public function execute(#[SensitiveParameter] string $token) : object
    {
        $claims = $this->tokenCodec->decode(token: $token);
        $now    = new DateTimeImmutable();

        if ($claims === null) {
            return (object) ['active' => false];
        }

        $expiresAt = $claims['exp'] ?? null;
        $tokenId   = $claims['jti'] ?? null;

        if (! is_int(value: $expiresAt) || $expiresAt <= $now->getTimestamp()) {
            return (object) ['active' => false];
        }

        if (is_string(value: $tokenId) && $this->tokenRevocationStore->isRevoked(tokenId: $tokenId, moment: $now)) {
            return (object) ['active' => false];
        }

        return (object) [
            'active'     => true,
            'sub'        => $claims['sub'] ?? null,
            'client_id'  => $claims['client_id'] ?? null,
            'scope'      => implode(separator: ' ', array: $this->scopes(claims: $claims)),
            'token_type' => $claims['type'] ?? null,
            'exp'        => $expiresAt,
            'iat'        => $claims['iat'] ?? null,
            'jti'        => $tokenId,
        ];
    }

    /**
     * @param array<string, mixed> $claims
     *
     * @return list<string>
     */
    private function scopes(array $claims) : array
    {
        $scopes = $claims['scopes'] ?? [];

        if (! is_array(value: $scopes)) {
            return [];
        }

        return array_values(array: array_filter(
                                       array   : $scopes,
                                       callback: static fn (mixed $scope) : bool => is_string(value: $scope),
                                   ));
    }
}
