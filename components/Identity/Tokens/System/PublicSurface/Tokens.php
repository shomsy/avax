<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\PublicSurface;

use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Code\AuthorizationCodeStoreInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Code\InMemoryAuthorizationCodeStore;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec\HmacTokenCodec;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec\TokenCodecInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\InMemoryTokenRevocationStore;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\TokenRevocationStoreInterface;
use Avax\Components\Identity\Tokens\System\Flows\AuthorizeToken\AuthorizeTokenRequest;
use Avax\Components\Identity\Tokens\System\Flows\ExchangeToken\ExchangeAuthorizationCode;
use Avax\Components\Identity\Tokens\System\Flows\IntrospectToken\IntrospectToken;
use Avax\Components\Identity\Tokens\System\Flows\RevokeToken\RevokeToken;
use stdClass;

/**
 * Tokens - Main entry point for Identity/Tokens component.
 */
final readonly class Tokens implements TokensInterface
{
    public function __construct(
        private AuthorizeTokenRequest $authorizeTokenRequest,
        private ExchangeAuthorizationCode $exchangeAuthorizationCode,
        private IntrospectToken $introspectToken,
        private RevokeToken $revokeToken,
    ) {
    }

    public static function hmac(string $secret): self
    {
        $inMemoryAuthorizationCodeStore = new InMemoryAuthorizationCodeStore();
        $hmacTokenCodec = new HmacTokenCodec(secret: $secret);
        $inMemoryTokenRevocationStore = new InMemoryTokenRevocationStore();

        return self::fromRuntime(
            authorizationCodeStore: $inMemoryAuthorizationCodeStore,
            tokenCodec            : $hmacTokenCodec,
            tokenRevocationStore  : $inMemoryTokenRevocationStore,
        );
    }

    public static function fromRuntime(
        AuthorizationCodeStoreInterface $authorizationCodeStore,
        TokenCodecInterface $tokenCodec,
        TokenRevocationStoreInterface $tokenRevocationStore,
    ): self {
        return new self(
            authorizeTokenRequest    : new AuthorizeTokenRequest(authorizationCodeStore: $authorizationCodeStore),
            exchangeAuthorizationCode: new ExchangeAuthorizationCode(
                authorizationCodeStore: $authorizationCodeStore,
                tokenCodec            : $tokenCodec,
            ),
            introspectToken          : new IntrospectToken(
                tokenCodec          : $tokenCodec,
                tokenRevocationStore: $tokenRevocationStore,
            ),
            revokeToken              : new RevokeToken(
                tokenCodec          : $tokenCodec,
                tokenRevocationStore: $tokenRevocationStore,
            ),
        );
    }

    public function authorize(array $request): stdClass
    {
        return $this->authorizeTokenRequest->execute(request: $request);
    }

    public function exchangeCode(string $code): stdClass
    {
        return $this->exchangeAuthorizationCode->execute($code);
    }

    public function introspect(string $token): stdClass
    {
        return $this->introspectToken->execute(token: $token);
    }

    public function revoke(string $token): void
    {
        $this->revokeToken->execute(token: $token);
    }
}
