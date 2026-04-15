<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Oidc;

use Avax\Auth\System\Capability\OAuth\InMemoryOAuthClientRegistry;
use Avax\Auth\System\Capability\OAuth\OAuthClientType;
use Avax\Auth\System\Capability\Oidc\InMemoryOidcRequestObjectStore;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Flow\OAuth\OAuthAuthorizationFailed;
use Avax\Auth\System\Flow\Oidc\ValidateRequestObject\ValidateRequestObject;
use Avax\Auth\System\Flow\Oidc\ValidateRequestObject\ValidateRequestObjectData;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ValidateRequestObjectTest extends TestCase
{
    public function testValidateRequestObjectNormalizesClaims() : void
    {
        $store = new InMemoryOidcRequestObjectStore();
        $store->store(
            requestUri: 'urn:ietf:params:oauth:request_uri:abc',
            claims    : [
                            'client_id'             => 'client-1',
                            'redirect_uri'          => 'https://rp.example.test/callback',
                            'scope'                 => 'profile email profile',
                            'state'                 => ' state-1 ',
                            'nonce'                 => ' nonce-1 ',
                            'code_challenge'        => 'challenge-1',
                            'code_challenge_method' => 'S256',
                        ],
            expiresAt : (new DateTimeImmutable())->modify(modifier: '+5 minutes')
        );
        $validator = new ValidateRequestObject(requestObjectStore: $store);

        $validated = $validator->execute(data: new ValidateRequestObjectData(
                                                   requestUri: 'urn:ietf:params:oauth:request_uri:abc'
                                               ));

        $this->assertSame(expected: 'client-1', actual: $validated->clientId);
        $this->assertSame(expected: 'https://rp.example.test/callback', actual: $validated->redirectUri);
        $this->assertSame(expected: ['email', 'profile'], actual: $validated->scopes);
        $this->assertSame(expected: 'state-1', actual: $validated->state);
        $this->assertSame(expected: 'nonce-1', actual: $validated->nonce);
    }

    public function testValidateRequestObjectRejectsMissingClaims() : void
    {
        $store = new InMemoryOidcRequestObjectStore();
        $store->store(
            requestUri: 'urn:ietf:params:oauth:request_uri:missing',
            claims    : [
                            'redirect_uri' => 'https://rp.example.test/callback',
                        ],
            expiresAt : (new DateTimeImmutable())->modify(modifier: '+5 minutes')
        );
        $validator = new ValidateRequestObject(requestObjectStore: $store);

        $this->expectException(OAuthAuthorizationFailed::class);
        $this->expectExceptionMessage('OIDC request object is invalid.');

        $validator->execute(data: new ValidateRequestObjectData(
                                      requestUri: 'urn:ietf:params:oauth:request_uri:missing'
                                  ));
    }

    public function testValidateRequestObjectRejectsUnsignedObjectWhenClientRequiresSignature() : void
    {
        $store    = new InMemoryOidcRequestObjectStore();
        $registry = new InMemoryOAuthClientRegistry(passwordHasher: new PasswordHasher());
        $client   = $registry->register(
            name                          : 'Signed JAR Client',
            type                          : OAuthClientType::CONFIDENTIAL,
            redirectUris                  : ['https://rp.example.test/callback'],
            allowedScopes                 : ['openid'],
            requestObjectSignatureRequired: true
        );
        $store->store(
            requestUri: 'urn:ietf:params:oauth:request_uri:unsigned',
            claims    : [
                            'client_id'    => $client->client->clientId,
                            'redirect_uri' => 'https://rp.example.test/callback',
                            'scope'        => 'openid',
                        ],
            expiresAt : (new DateTimeImmutable())->modify(modifier: '+5 minutes')
        );
        $validator = new ValidateRequestObject(requestObjectStore: $store, clientRegistry: $registry);

        $this->expectException(OAuthAuthorizationFailed::class);
        $this->expectExceptionMessage('OIDC request object is invalid.');

        $validator->execute(data: new ValidateRequestObjectData(
                                      requestUri: 'urn:ietf:params:oauth:request_uri:unsigned'
                                  ));
    }
}
