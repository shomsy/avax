<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
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
use Avax\Components\Identity\Tokens\System\PublicSurface\Tokens;

/**
 * TokensServiceProvider — registers tokens component dependencies.
 */
final class TokensServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Token codec — HMAC-based token encoding
        // REQUIRES TOKEN_SECRET environment variable at deployment time.
        $container->singleton(TokenCodecInterface::class, static function () : TokenCodecInterface {
            $secret = $_ENV['TOKEN_SECRET'] ?? throw new \RuntimeException(
                'TOKEN_SECRET environment variable is required but not set. '
                . 'Generate a secure random string and set it in your .env or deployment configuration.',
            );

            return new HmacTokenCodec(secret: $secret);
        });

        // Authorization code store — in-memory
        $container->singleton(AuthorizationCodeStoreInterface::class, static fn () : AuthorizationCodeStoreInterface => new InMemoryAuthorizationCodeStore());

        // Token revocation store — in-memory
        $container->singleton(TokenRevocationStoreInterface::class, static fn () : TokenRevocationStoreInterface => new InMemoryTokenRevocationStore());

        // Token flows
        $container->singleton(AuthorizeTokenRequest::class, static fn (ContainerInterface $c) : AuthorizeTokenRequest => new AuthorizeTokenRequest(
            authorizationCodeStore: $c->get(AuthorizationCodeStoreInterface::class),
        ));

        $container->singleton(ExchangeAuthorizationCode::class, static fn (ContainerInterface $c) : ExchangeAuthorizationCode => new ExchangeAuthorizationCode(
            authorizationCodeStore: $c->get(AuthorizationCodeStoreInterface::class),
            tokenCodec            : $c->get(TokenCodecInterface::class),
        ));

        $container->singleton(IntrospectToken::class, static fn (ContainerInterface $c) : IntrospectToken => new IntrospectToken(
            tokenCodec          : $c->get(TokenCodecInterface::class),
            tokenRevocationStore: $c->get(TokenRevocationStoreInterface::class),
        ));

        $container->singleton(RevokeToken::class, static fn (ContainerInterface $c) : RevokeToken => new RevokeToken(
            tokenCodec          : $c->get(TokenCodecInterface::class),
            tokenRevocationStore: $c->get(TokenRevocationStoreInterface::class),
        ));

        // Tokens public surface
        $container->singleton(Tokens::class, static fn (ContainerInterface $c) : Tokens => new Tokens(
            authorizeTokenRequest    : $c->get(AuthorizeTokenRequest::class),
            exchangeAuthorizationCode: $c->get(ExchangeAuthorizationCode::class),
            introspectToken          : $c->get(IntrospectToken::class),
            revokeToken              : $c->get(RevokeToken::class),
        ));
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed
    }
}
