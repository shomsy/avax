<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\OAuth;

use Avax\Auth\System\Capability\OAuth\InMemoryOAuthClientRegistry;
use Avax\Auth\System\Capability\OAuth\OAuthGrantType;
use Avax\Auth\System\Capability\OAuth\OAuthClientType;
use Avax\Auth\System\Capability\OAuth\OAuthTokenEndpointAuthMethod;
use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraintType;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class InMemoryOAuthClientRegistryTest extends TestCase
{
    public function testConfidentialClientRegistrationReturnsOneTimeSecret() : void
    {
        $registry = new InMemoryOAuthClientRegistry(passwordHasher: new PasswordHasher());

        $registered = $registry->register(
            name         : 'Backoffice',
            type         : OAuthClientType::CONFIDENTIAL,
            redirectUris : ['https://app.example.test/callback'],
            allowedScopes: ['profile', 'email']
        );

        $this->assertSame(expected: OAuthClientType::CONFIDENTIAL, actual: $registered->client->type);
        $this->assertNotNull(actual: $registered->plainTextSecret);
        $this->assertTrue(condition: $registry->verifySecret(clientId: $registered->client->clientId, plainTextSecret: $registered->plainTextSecret));
    }

    public function testPublicClientDoesNotRequireSecret() : void
    {
        $registry = new InMemoryOAuthClientRegistry(passwordHasher: new PasswordHasher());

        $registered = $registry->register(
            name         : 'SPA',
            type         : OAuthClientType::PUBLIC,
            redirectUris : ['https://spa.example.test/callback'],
            allowedScopes: ['profile']
        );

        $this->assertSame(expected: OAuthClientType::PUBLIC, actual: $registered->client->type);
        $this->assertNull(actual: $registered->plainTextSecret);
        $this->assertTrue(condition: $registry->verifySecret(clientId: $registered->client->clientId, plainTextSecret: null));
        $this->assertSame(expected: OAuthTokenEndpointAuthMethod::NONE, actual: $registered->client->tokenEndpointAuthMethod);
    }

    public function testConfidentialClientDefaultsToClientSecretBasicTokenEndpointAuthMethod() : void
    {
        $registry = new InMemoryOAuthClientRegistry(passwordHasher: new PasswordHasher());

        $registered = $registry->register(
            name         : 'Backoffice',
            type         : OAuthClientType::CONFIDENTIAL,
            redirectUris : ['https://app.example.test/callback'],
            allowedScopes: ['profile', 'email']
        );

        $this->assertSame(expected: OAuthTokenEndpointAuthMethod::CLIENT_SECRET_BASIC, actual: $registered->client->tokenEndpointAuthMethod);
    }

    public function testClientRegistrationPersistsGrantTypesAndSenderConstraint() : void
    {
        $registry = new InMemoryOAuthClientRegistry(passwordHasher: new PasswordHasher());

        $registered = $registry->register(
            name                     : 'Partner API',
            type                     : OAuthClientType::CONFIDENTIAL,
            redirectUris             : ['https://api.example.test/callback'],
            allowedScopes            : ['profile'],
            allowedAudiences         : ['partner-api'],
            allowedGrantTypes        : [OAuthGrantType::AUTHORIZATION_CODE, OAuthGrantType::REFRESH_TOKEN],
            requiredSenderConstraint : OAuthSenderConstraintType::DPOP,
            phishingResistantRequired: true,
            requestObjectSignatureRequired: true,
            frontChannelLogoutSupported: true,
            backChannelLogoutSupported: false
        );

        $this->assertTrue(condition: $registered->client->allowsGrantType(grantType: OAuthGrantType::AUTHORIZATION_CODE));
        $this->assertSame(expected: OAuthSenderConstraintType::DPOP, actual: $registered->client->requiredSenderConstraint);
        $this->assertTrue(condition: $registered->client->phishingResistantRequired);
        $this->assertTrue(condition: $registered->client->requestObjectSignatureRequired);
        $this->assertTrue(condition: $registered->client->frontChannelLogoutSupported);
        $this->assertFalse(condition: $registered->client->backChannelLogoutSupported);
        $this->assertTrue(condition: $registered->client->allowsAudience(audience: 'partner-api'));
    }

    public function testWorkloadIdentityRequiresSenderConstraint() : void
    {
        $registry = new InMemoryOAuthClientRegistry(passwordHasher: new PasswordHasher());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Workload identity clients require sender-constrained tokens.');

        $registry->register(
            name            : 'Machine API',
            type            : OAuthClientType::CONFIDENTIAL,
            redirectUris    : ['https://api.example.test/callback'],
            allowedScopes   : ['jobs.run'],
            allowedAudiences: ['jobs-api'],
            workloadIdentity: true
        );
    }

    public function testWorkloadIdentityRequiresAudienceBoundaries() : void
    {
        $registry = new InMemoryOAuthClientRegistry(passwordHasher: new PasswordHasher());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Workload identity clients require at least one allowed audience.');

        $registry->register(
            name                    : 'Machine API',
            type                    : OAuthClientType::CONFIDENTIAL,
            redirectUris            : ['https://api.example.test/callback'],
            allowedScopes           : ['jobs.run'],
            allowedGrantTypes       : [OAuthGrantType::CLIENT_CREDENTIALS],
            requiredSenderConstraint: OAuthSenderConstraintType::MTLS,
            workloadIdentity        : true
        );
    }

    public function testPublicClientRejectsNonNoneTokenEndpointAuthMethod() : void
    {
        $registry = new InMemoryOAuthClientRegistry(passwordHasher: new PasswordHasher());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Public clients must use token endpoint auth method "none".');

        $registry->register(
            name                     : 'SPA',
            type                     : OAuthClientType::PUBLIC,
            redirectUris             : ['https://spa.example.test/callback'],
            allowedScopes            : ['profile'],
            tokenEndpointAuthMethod   : OAuthTokenEndpointAuthMethod::CLIENT_SECRET_BASIC
        );
    }
}
