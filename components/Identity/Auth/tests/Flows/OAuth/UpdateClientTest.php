<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Flows\OAuth;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\UpdateClient\UpdateClient;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\UpdateClient\UpdateClientData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\InMemoryOAuthClientRegistry;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClientType;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Tests\TestCase;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

final class UpdateClientTest extends TestCase
{
    /**
     * @throws RandomException
     */
    public function testUpdateClientPropagatesRequestObjectVerificationKey() : void
    {
        $key = openssl_pkey_new(options: [
                                             'private_key_bits' => 2048,
                                             'private_key_type' => OPENSSL_KEYTYPE_RSA,
                                         ]);
        self::assertNotFalse(condition: $key);
        $details = openssl_pkey_get_details(key: $key);
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
