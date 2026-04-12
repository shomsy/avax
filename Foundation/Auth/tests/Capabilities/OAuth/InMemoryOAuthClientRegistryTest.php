<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\OAuth;

use Avax\Auth\System\Capability\OAuth\InMemoryOAuthClientRegistry;
use Avax\Auth\System\Capability\OAuth\OAuthGrantType;
use Avax\Auth\System\Capability\OAuth\OAuthClientType;
use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraintType;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class InMemoryOAuthClientRegistryTest extends TestCase
{
    public function testConfidentialClientRegistrationReturnsOneTimeSecret() : void
    {
        $registry = new InMemoryOAuthClientRegistry(new PasswordHasher());

        $registered = $registry->register(
            name         : 'Backoffice',
            type         : OAuthClientType::CONFIDENTIAL,
            redirectUris : ['https://app.example.test/callback'],
            allowedScopes: ['profile', 'email']
        );

        $this->assertSame(OAuthClientType::CONFIDENTIAL, $registered->client->type);
        $this->assertNotNull($registered->plainTextSecret);
        $this->assertTrue($registry->verifySecret($registered->client->clientId, $registered->plainTextSecret));
    }

    public function testPublicClientDoesNotRequireSecret() : void
    {
        $registry = new InMemoryOAuthClientRegistry(new PasswordHasher());

        $registered = $registry->register(
            name         : 'SPA',
            type         : OAuthClientType::PUBLIC,
            redirectUris : ['https://spa.example.test/callback'],
            allowedScopes: ['profile']
        );

        $this->assertSame(OAuthClientType::PUBLIC, $registered->client->type);
        $this->assertNull($registered->plainTextSecret);
        $this->assertTrue($registry->verifySecret($registered->client->clientId, null));
    }

    public function testClientRegistrationPersistsGrantTypesAndSenderConstraint() : void
    {
        $registry = new InMemoryOAuthClientRegistry(new PasswordHasher());

        $registered = $registry->register(
            name                     : 'Partner API',
            type                     : OAuthClientType::CONFIDENTIAL,
            redirectUris             : ['https://api.example.test/callback'],
            allowedScopes            : ['profile'],
            allowedGrantTypes        : [OAuthGrantType::AUTHORIZATION_CODE, OAuthGrantType::REFRESH_TOKEN],
            requiredSenderConstraint : OAuthSenderConstraintType::DPOP,
            phishingResistantRequired: true
        );

        $this->assertTrue($registered->client->allowsGrantType(OAuthGrantType::AUTHORIZATION_CODE));
        $this->assertSame(OAuthSenderConstraintType::DPOP, $registered->client->requiredSenderConstraint);
        $this->assertTrue($registered->client->phishingResistantRequired);
    }

    public function testWorkloadIdentityRequiresSenderConstraint() : void
    {
        $registry = new InMemoryOAuthClientRegistry(new PasswordHasher());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Workload identity clients require sender-constrained tokens.');

        $registry->register(
            name            : 'Machine API',
            type            : OAuthClientType::CONFIDENTIAL,
            redirectUris    : ['https://api.example.test/callback'],
            allowedScopes   : ['jobs.run'],
            workloadIdentity: true
        );
    }
}
