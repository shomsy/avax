<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\OAuth;

use Avax\Auth\System\Capability\OAuth\InMemoryOAuthClientRegistry;
use Avax\Auth\System\Capability\OAuth\OAuthClientType;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\OAuth\UpdateClient\UpdateClient;
use Avax\Auth\System\Flow\OAuth\UpdateClient\UpdateClientData;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

final class UpdateClientTest extends TestCase
{
    /**
     * @throws RandomException
     */
    public function testUpdateClientPropagatesRequestObjectVerificationKey() : void
    {
        $key = openssl_pkey_new([
                                    'private_key_bits' => 2048,
                                    'private_key_type' => OPENSSL_KEYTYPE_RSA,
                                ]);
        self::assertNotFalse(condition: $key);
        $details = openssl_pkey_get_details($key);
        self::assertIsArray(actual: $details);

        $registry   = new InMemoryOAuthClientRegistry(passwordHasher: new PasswordHasher());
        $registered = $registry->register(
            name         : 'Mutable SPA',
            type         : OAuthClientType::PUBLIC,
            redirectUris : ['https://spa.example.test/callback'],
            allowedScopes: ['openid']
        );

        $flow = new UpdateClient(
            clientRegistry: $registry,
            auditLog      : new InMemoryAuditLog(),
            clock         : new Clock()
        );

        $updated = $flow->execute(data: new UpdateClientData(
                                            clientId                       : $registered->client->clientId,
                                            name                           : 'Mutable SPA',
                                            type                           : OAuthClientType::PUBLIC,
                                            redirectUris                   : ['https://spa.example.test/callback'],
                                            allowedScopes                  : ['openid'],
                                            requestObjectSignatureRequired : true,
                                            requestObjectVerificationKeyPem: $details['key']
                                        ));

        self::assertSame(expected: $details['key'], actual: $updated->requestObjectVerificationKeyPem);
    }
}
