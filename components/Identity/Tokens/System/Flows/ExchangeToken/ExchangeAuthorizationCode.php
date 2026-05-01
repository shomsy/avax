<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Flows\ExchangeToken;

use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Code\AuthorizationCodeStoreInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec\TokenCodecInterface;
use DateInterval;
use DateTimeImmutable;
use RuntimeException;
use SensitiveParameter;

/**
 * ExchangeAuthorizationCode - OAuth2 flow to exchange code for tokens.
 */
final readonly class ExchangeAuthorizationCode
{
    public function __construct(
        private AuthorizationCodeStoreInterface $authorizationCodeStore,
        private TokenCodecInterface             $tokenCodec,
        private DateInterval                    $accessTokenTtl = new DateInterval(duration: 'PT15M'),
        private DateInterval                    $refreshTokenTtl = new DateInterval(duration: 'P30D'),
    ) {}

    public function execute(#[SensitiveParameter] string $code) : object
    {
        $now    = new DateTimeImmutable();
        $record = $this->authorizationCodeStore->consume(code: $code, moment: $now);

        if ($record === null) {
            throw new RuntimeException(message: 'Authorization code is invalid or expired.');
        }

        $accessTokenId    = bin2hex(string: random_bytes(length: 16));
        $refreshTokenId   = bin2hex(string: random_bytes(length: 16));
        $accessExpiresAt  = $now->add(interval: $this->accessTokenTtl);
        $refreshExpiresAt = $now->add(interval: $this->refreshTokenTtl);
        $commonClaims     = [
            'sub'       => $record->subject,
            'client_id' => $record->clientId,
            'scopes'    => $record->scopes,
            'iat'       => $now->getTimestamp(),
        ];

        $accessToken = $this->tokenCodec->encode(claims: $commonClaims + [
                                                           'jti'  => $accessTokenId,
                                                           'exp'  => $accessExpiresAt->getTimestamp(),
                                                           'type' => 'access',
                                                       ]);

        $refreshToken = $this->tokenCodec->encode(claims: $commonClaims + [
                                                            'jti'  => $refreshTokenId,
                                                            'exp'  => $refreshExpiresAt->getTimestamp(),
                                                            'type' => 'refresh',
                                                        ]);

        return (object) [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => $accessExpiresAt->getTimestamp() - $now->getTimestamp(),
            'expires_at'    => $accessExpiresAt,
            'scope'         => implode(separator: ' ', array: $record->scopes),
        ];
    }
}
