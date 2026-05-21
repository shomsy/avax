<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Configuration\Assembly;

use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Code\AuthorizationCodeStoreInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Code\InMemoryAuthorizationCodeStore;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec\HmacTokenCodec;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec\TokenCodecInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\InMemoryTokenRevocationStore;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\TokenRevocationStoreInterface;
use Avax\Components\Identity\Tokens\System\Flows\AuthorizeToken\AuthorizeTokenRequest;
use Avax\Components\Identity\Tokens\System\Flows\ExchangeToken\ExchangeAuthorizationCode;
use Avax\Components\Identity\Tokens\System\Flows\IssueToken\IssueToken;
use Avax\Components\Identity\Tokens\System\Flows\IntrospectToken\IntrospectToken;
use Avax\Components\Identity\Tokens\System\Flows\RevokeToken\RevokeToken;
use Avax\Components\Identity\Tokens\System\PublicSurface\Tokens;

/**
 * Assembles the Tokens object graph from configured stores and codecs.
 *
 * Configuration/Assembly owns construction. PublicSurface must not assemble.
 */
final class TokensGraph
{
    /**
     * Build Tokens with HMAC-based codec and InMemory stores.
     * Provided as a convenience for testing and simple setups.
     */
    public static function hmac(string $secret) : Tokens
    {
        return self::fromRuntime(
            authorizationCodeStore: new InMemoryAuthorizationCodeStore(),
            tokenCodec           : new HmacTokenCodec(secret: $secret),
            tokenRevocationStore : new InMemoryTokenRevocationStore(),
        );
    }

    /**
     * Build Tokens from explicit runtime dependencies.
     */
    public static function fromRuntime(
        AuthorizationCodeStoreInterface $authorizationCodeStore,
        TokenCodecInterface             $tokenCodec,
        TokenRevocationStoreInterface   $tokenRevocationStore,
    ) : Tokens {
        return new Tokens(
            authorizeTokenRequest    : new AuthorizeTokenRequest(authorizationCodeStore: $authorizationCodeStore),
            exchangeAuthorizationCode: new ExchangeAuthorizationCode(
                                           authorizationCodeStore: $authorizationCodeStore,
                                           tokenCodec            : $tokenCodec,
                                       ),
            introspectToken          : new IntrospectToken(
                                           tokenCodec          : $tokenCodec,
                                           tokenRevocationStore: $tokenRevocationStore,
                                       ),
            issueToken               : new IssueToken(tokenCodec: $tokenCodec),
            revokeToken              : new RevokeToken(
                                           tokenCodec          : $tokenCodec,
                                           tokenRevocationStore: $tokenRevocationStore,
                                       ),
        );
    }
}
