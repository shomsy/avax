<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Flows\OAuth;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RegisterClient\RegisterClient;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RegisterClient\RegisterClientData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\InMemoryOAuthClientRegistry;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClientType;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

final class RegisterClientTest extends TestCase
{
    public function testRegisterClientPropagatesRequestObjectVerificationKey() : void
    {
        $key = openssl_pkey_new(options: [
                                             'private_key_bits' => 2048,
                                             'private_key_type' => OPENSSL_KEYTYPE_RSA,
                                         ]);
        self::assertNotFalse(condition: $key);
        $details = openssl_pkey_get_details(key: $key);
        self::assertIsArray(actual: $details);

        $flow = new RegisterClient(
            clientRegistry: new InMemoryOAuthClientRegistry(passwordHasher: new PasswordHasher()),
            auditLog      : new InMemoryAuditLog(),
            clock         : new Clock()
        );

        $registered = $flow->execute(data: new RegisterClientData(
                                               name                           : 'Flow Registered SPA',
                                               type                           : OAuthClientType::PUBLIC,
                                               redirectUris                   : ['https://spa.example.test/callback'],
                                               allowedScopes                  : ['openid'],
                                               requestObjectSignatureRequired : true,
                                               requestObjectVerificationKeyPem: $details['key']
                                           ));

        self::assertSame(expected: $details['key'], actual: $registered->client->requestObjectVerificationKeyPem);
    }
}
