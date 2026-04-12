<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\OAuth;

use Avax\Auth\System\Capability\OAuth\InMemoryOAuthClientRegistry;
use Avax\Auth\System\Capability\OAuth\OAuthClientType;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
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
}
