<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Oidc;

use Avax\Auth\System\Capability\OAuth\InMemoryOAuthClientRegistry;
use Avax\Auth\System\Capability\OAuth\OAuthClient;
use Avax\Auth\System\Capability\OAuth\OAuthClientType;
use Avax\Auth\System\Capability\Oidc\InMemoryOidcRequestObjectStore;
use Avax\Auth\System\Capability\Oidc\OidcIdToken;
use Avax\Auth\System\Capability\Oidc\OidcJsonWebKeySet;
use Avax\Auth\System\Capability\Oidc\OidcProviderInterface;
use Avax\Auth\System\Capability\Oidc\OidcProviderMetadata;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Flow\Diagnostics\NullAuditLog;
use Avax\Auth\System\Flow\OAuth\OAuthAuthorizationFailed;
use Avax\Auth\System\Flow\Oidc\PushAuthorizationRequest\PushAuthorizationRequest;
use Avax\Auth\System\Flow\Oidc\PushAuthorizationRequest\PushAuthorizationRequestData;
use Avax\Auth\System\Foundation\Clock;
use BadMethodCallException;
use DateMalformedStringException;
use DateTimeImmutable;
use JsonException;
use PHPUnit\Framework\TestCase;
use Random\RandomException;
use SensitiveParameter;

final class PushAuthorizationRequestTest extends TestCase
{
    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     * @throws JsonException
     */
    public function testPushAuthorizationRequestAcceptsRsaSignedRequestObjectForPublicClient() : void
    {
        $key = openssl_pkey_new([
                                    'private_key_bits' => 2048,
                                    'private_key_type' => OPENSSL_KEYTYPE_RSA,
                                ]);
        self::assertNotFalse(condition: $key);
        openssl_pkey_export($key, $privateKeyPem);
        $publicKeyPem = (string) openssl_pkey_get_details($key)['key'];

        $registry   = new InMemoryOAuthClientRegistry(passwordHasher: new PasswordHasher());
        $registered = $registry->register(
            name                          : 'Public JAR Client',
            type                          : OAuthClientType::PUBLIC,
            redirectUris                  : ['https://rp.example.test/callback'],
            allowedScopes                 : ['openid', 'profile'],
            requestObjectSignatureRequired: true
        );
        $registry->replace(client: new OAuthClient(
                                       clientId                       : $registered->client->clientId,
                                       name                           : $registered->client->name,
                                       type                           : $registered->client->type,
                                       redirectUris                   : $registered->client->redirectUris,
                                       allowedScopes                  : $registered->client->allowedScopes,
                                       tenantSlug                     : $registered->client->tenantSlug,
                                       allowedAudiences               : $registered->client->allowedAudiences,
                                       allowedGrantTypes              : $registered->client->allowedGrantTypes,
                                       audienceScopeBoundaries        : $registered->client->audienceScopeBoundaries,
                                       tokenEndpointAuthMethod        : $registered->client->tokenEndpointAuthMethod,
                                       requiredSenderConstraint       : $registered->client->requiredSenderConstraint,
                                       workloadIdentity               : $registered->client->workloadIdentity,
                                       phishingResistantRequired      : $registered->client->phishingResistantRequired,
                                       requestObjectSignatureRequired : $registered->client->requestObjectSignatureRequired,
                                       frontChannelLogoutSupported    : $registered->client->frontChannelLogoutSupported,
                                       backChannelLogoutSupported     : $registered->client->backChannelLogoutSupported,
                                       approvalStatus                 : $registered->client->approvalStatus,
                                       approvedAt                     : $registered->client->approvedAt,
                                       approvedBy                     : $registered->client->approvedBy,
                                       active                         : $registered->client->active,
                                       secretHash                     : $registered->client->secretHash,
                                       requestObjectVerificationKeyPem: $publicKeyPem
                                   ));

        $flow = new PushAuthorizationRequest(
            requestObjectStore: new InMemoryOidcRequestObjectStore(),
            auditLog          : new NullAuditLog(),
            clock             : new Clock(),
            clientRegistry    : $registry
        );

        $result = $flow->execute(data: new PushAuthorizationRequestData(
                                           clientId        : $registered->client->clientId,
                                           redirectUri     : 'https://rp.example.test/callback',
                                           requestObjectJwt: $this->signRs256Jwt(
                                                                 claims       : [
                                                                                    'client_id'    => $registered->client->clientId,
                                                                                    'redirect_uri' => 'https://rp.example.test/callback',
                                                                                    'scope'        => 'openid profile',
                                                                                    'nonce'        => 'nonce-rs256',
                                                                                    'exp'          => time() + 300,
                                                                                ],
                                                                 privateKeyPem: $privateKeyPem
                                                             )
                                       ));

        self::assertNotEmpty(actual: $result->requestUri);
        self::assertSame(expected: $registered->client->clientId, actual: $result->clientId);
        self::assertSame(expected: ['openid', 'profile'], actual: $result->scopes);
    }

    /**
     * @param array<string, mixed> $claims
     * @param string               $privateKeyPem
     *
     * @return string
     * @throws JsonException
     */
    private function signRs256Jwt(array $claims, #[SensitiveParameter] string $privateKeyPem) : string
    {
        $header       = $this->base64UrlEncode(value: json_encode(['typ' => 'JWT', 'alg' => 'RS256'], JSON_THROW_ON_ERROR));
        $payload      = $this->base64UrlEncode(value: json_encode($claims, JSON_THROW_ON_ERROR));
        $signingInput = $header . '.' . $payload;
        $signature    = '';

        $signed = openssl_sign($signingInput, $signature, $privateKeyPem, OPENSSL_ALGO_SHA256);
        self::assertTrue(condition: $signed);

        return $signingInput . '.' . $this->base64UrlEncode(value: $signature);
    }

    private function base64UrlEncode(string $value) : string
    {
        return $value
                |> base64_encode(...)
                |> (static fn ($x) => strtr($x, '+/', '-_'))
                |> (static fn ($x) => rtrim($x, '='));
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     * @throws JsonException
     */
    public function testPushAuthorizationRequestRejectsSignedRequestObjectWhenIssuerDoesNotMatchClient() : void
    {
        $key = openssl_pkey_new([
                                    'private_key_bits' => 2048,
                                    'private_key_type' => OPENSSL_KEYTYPE_RSA,
                                ]);
        self::assertNotFalse(condition: $key);
        openssl_pkey_export($key, $privateKeyPem);
        $publicKeyPem = (string) openssl_pkey_get_details($key)['key'];

        $registry   = new InMemoryOAuthClientRegistry(passwordHasher: new PasswordHasher());
        $registered = $registry->register(
            name                           : 'Public JAR Client',
            type                           : OAuthClientType::PUBLIC,
            redirectUris                   : ['https://rp.example.test/callback'],
            allowedScopes                  : ['openid'],
            requestObjectSignatureRequired : true,
            requestObjectVerificationKeyPem: $publicKeyPem
        );

        $flow = new PushAuthorizationRequest(
            requestObjectStore: new InMemoryOidcRequestObjectStore(),
            auditLog          : new NullAuditLog(),
            clock             : new Clock(),
            clientRegistry    : $registry,
            oidcProvider      : $this->oidcProvider()
        );

        $this->expectException(OAuthAuthorizationFailed::class);
        $this->expectExceptionMessage('OIDC request object is invalid.');

        $flow->execute(data: new PushAuthorizationRequestData(
                                 clientId        : $registered->client->clientId,
                                 redirectUri     : 'https://rp.example.test/callback',
                                 requestObjectJwt: $this->signRs256Jwt(
                                                       claims       : [
                                                                          'iss'          => 'other-client',
                                                                          'aud'          => 'https://auth.example.test',
                                                                          'client_id'    => $registered->client->clientId,
                                                                          'redirect_uri' => 'https://rp.example.test/callback',
                                                                          'scope'        => 'openid',
                                                                          'nonce'        => 'nonce-rs256',
                                                                          'exp'          => time() + 300,
                                                                      ],
                                                       privateKeyPem: $privateKeyPem
                                                   )
                             ));
    }

    private function oidcProvider() : OidcProviderInterface
    {
        return new class('https://auth.example.test') implements OidcProviderInterface {
            private readonly string $issuer;

            public function __construct(
                string $issuer
            )
            {
                $this->issuer = $issuer;
            }

            public function issueIdToken(
                User                              $user,
                string                            $clientId,
                array                             $scopes,
                string|null                       $nonce = null,
                DateTimeImmutable|null            $authenticatedAt = null,
                #[SensitiveParameter] string|null $sessionId = null,
                bool                              $phishingResistant = false
            ) : OidcIdToken
            {
                throw new BadMethodCallException(message: 'Not required for this test.');
            }

            public function issueJwt(array $claims) : string
            {
                throw new BadMethodCallException(message: 'Not required for this test.');
            }

            public function readProviderMetadata() : OidcProviderMetadata
            {
                return new OidcProviderMetadata(
                    issuer                                        : $this->issuer,
                    authorizationEndpoint                         : $this->issuer . '/authorize',
                    tokenEndpoint                                 : $this->issuer . '/token',
                    registrationEndpoint                          : $this->issuer . '/oidc/register',
                    userInfoEndpoint                              : $this->issuer . '/userinfo',
                    endSessionEndpoint                            : $this->issuer . '/logout',
                    pushedAuthorizationRequestEndpoint            : $this->issuer . '/par',
                    jsonWebKeySetUri                              : $this->issuer . '/jwks',
                    scopesSupported                               : ['openid'],
                    responseTypesSupported                        : ['code'],
                    grantTypesSupported                           : ['authorization_code'],
                    subjectTypesSupported                         : ['public'],
                    idTokenSigningAlgValuesSupported              : ['RS256'],
                    codeChallengeMethodsSupported                 : ['S256'],
                    frontChannelLogoutSupported                   : true,
                    backChannelLogoutSupported                    : true,
                    backChannelLogoutSessionSupported             : true,
                    requestObjectSigningAlgValuesSupported        : ['RS256'],
                    authorizationResponseSigningAlgValuesSupported: ['RS256']
                );
            }

            public function readJsonWebKeySet() : OidcJsonWebKeySet
            {
                throw new BadMethodCallException(message: 'Not required for this test.');
            }

            public function subjectIdentifier(User $user, string $clientId) : string
            {
                throw new BadMethodCallException(message: 'Not required for this test.');
            }

            public function resolveIdToken(#[SensitiveParameter] string $idToken) : array|null
            {
                throw new BadMethodCallException(message: 'Not required for this test.');
            }

            public function resolveJwt(#[SensitiveParameter] string $jwt) : array|null
            {
                throw new BadMethodCallException(message: 'Not required for this test.');
            }
        };
    }
}
