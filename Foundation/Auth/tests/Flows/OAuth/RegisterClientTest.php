<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\OAuth;

use Avax\Auth\System\Capability\OAuth\InMemoryOAuthClientRegistry;
use Avax\Auth\System\Capability\OAuth\OAuthClientType;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\OAuth\RegisterClient\RegisterClient;
use Avax\Auth\System\Flow\OAuth\RegisterClient\RegisterClientData;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

final class RegisterClientTest extends TestCase
{
    public function testRegisterClientPropagatesRequestObjectVerificationKey() : void
    {
        $key = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        self::assertNotFalse(condition: $key);
        $details = openssl_pkey_get_details($key);
        self::assertIsArray(actual: $details);

        $flow = new RegisterClient(
            clientRegistry: new InMemoryOAuthClientRegistry(passwordHasher: new PasswordHasher()),
            auditLog: new InMemoryAuditLog(),
            clock: new Clock()
        );

        $registered = $flow->execute(data: new RegisterClientData(
            name: 'Flow Registered SPA',
            type: OAuthClientType::PUBLIC,
            redirectUris: ['https://spa.example.test/callback'],
            allowedScopes: ['openid'],
            requestObjectSignatureRequired: true,
            requestObjectVerificationKeyPem: $details['key']
        ));

        self::assertSame(expected: $details['key'], actual: $registered->client->requestObjectVerificationKeyPem);
    }
}
