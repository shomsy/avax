<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Characterization;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Federation\FederationProvider;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Federation\CompleteFederatedLogin\CompleteFederatedLoginData;
use Avax\Auth\System\Flow\Federation\RegisterConnection\RegisterFederationConnectionData;
use Avax\Auth\System\Flow\Federation\StartFederatedLogin\StartFederatedLoginData;
use Avax\Auth\System\Flow\Federation\VerifyDomain\VerifyFederationDomainData;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flow\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Foundation\Clock;
use Avax\Auth\Tests\Support\FakeFederationRuntime;
use PHPUnit\Framework\TestCase;

final class LegacySingleSignOnBehaviorTest extends TestCase
{
    public function testLegacyFederatedLoginBehaviorRemainsStable() : void
    {
        $userSource    = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();
        $auth          = Auth::configuration()
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: new Identity(jwtIdentity: new JwtIdentity(
                userSource       : $userSource,
                codec            : new HmacTokenCodec(secret: 'legacy-sso-secret'),
                clock            : new Clock(),
                revocationStore  : new InMemoryTokenRevocationStore(),
                refreshTokenStore: $refreshTokens
            )))
            ->withRefreshTokenStore(refreshTokenStore: $refreshTokens)
            ->withAuditLog(auditLog: new InMemoryAuditLog())
            ->withFederationRuntime(federationRuntime: new FakeFederationRuntime())
            ->ready();

        $connection = $auth->registerFederationConnection(data: new RegisterFederationConnectionData(
            tenantSlug : 'acme',
            name       : 'Legacy SSO',
            provider   : FederationProvider::OIDC,
            domain     : 'acme.test'
        ));
        $auth->verifyFederationDomain(data: new VerifyFederationDomainData(
            connectionId     : $connection->connectionId,
            verificationToken: $connection->domainVerificationToken ?? ''
        ));

        $started = $auth->startFederatedLogin(data: new StartFederatedLoginData(
            connectionId: $connection->connectionId,
            redirectUri : 'https://app.example.test/callback',
            state       : 'legacy-sso'
        ));
        $result  = $auth->completeFederatedLogin(data: new CompleteFederatedLoginData(
            connectionId: $connection->connectionId,
            payload     : [
                'subject' => 'legacy-user',
                'email'   => 'legacy-sso@acme.test',
            ]
        ));

        $this->assertStringContainsString(needle: $connection->connectionId, haystack: $started->redirectUrl);
        $this->assertTrue(condition: $result->isAuthenticated());
        $this->assertSame(expected: 'legacy-sso@acme.test', actual: $result->user()?->email);
    }
}
