<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Federation;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\Federation\FederationProvider;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Federation\CompleteFederatedLogin\CompleteFederatedLoginData;
use Avax\Auth\System\Flow\Federation\RegisterConnection\RegisterFederationConnectionData;
use Avax\Auth\System\Flow\Federation\StartFederatedLogin\StartFederatedLoginData;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flow\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Foundation\Clock;
use Avax\Auth\Tests\Support\FakeFederationRuntime;
use PHPUnit\Framework\TestCase;

final class FederationFlowTest extends TestCase
{
    public function testFederationConnectionDiscoveryAndLoginProvisioning() : void
    {
        $auditLog      = new InMemoryAuditLog();
        $userSource    = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();
        $auth          = Auth::configuration()
            ->forUser($userSource)
            ->withIdentity(new Identity(jwtIdentity: new JwtIdentity(
                userSource       : $userSource,
                codec            : new HmacTokenCodec('federation-secret'),
                clock            : new Clock(),
                revocationStore  : new InMemoryTokenRevocationStore(),
                refreshTokenStore: $refreshTokens
            )))
            ->withRefreshTokenStore($refreshTokens)
            ->withAuditLog($auditLog)
            ->withFederationRuntime(new FakeFederationRuntime())
            ->ready();

        $connection = $auth->registerFederationConnection(new RegisterFederationConnectionData(
            tenantSlug  : 'acme',
            name        : 'Acme OIDC',
            provider    : FederationProvider::OIDC,
            domain      : 'acme.test',
            groupRoleMap: ['admins' => ['admin']]
        ));

        $this->assertSame($connection->connectionId, $auth->discoverFederationConnection('user@acme.test')?->connectionId);

        $started = $auth->startFederatedLogin(new StartFederatedLoginData(
            connectionId: $connection->connectionId,
            redirectUri : 'https://app.example.test/callback',
            state       : 'state-1'
        ));
        $this->assertStringContainsString($connection->connectionId, $started->redirectUrl);

        $result = $auth->completeFederatedLogin(new CompleteFederatedLoginData(
            connectionId: $connection->connectionId,
            payload     : [
                'subject' => 'external-123',
                'email' => 'admin@acme.test',
                'display_name' => 'Acme Admin',
                'groups' => ['admins'],
            ],
            ipAddress   : '127.0.0.1',
            userAgent   : 'PHPUnit'
        ));

        $this->assertTrue($result->isAuthenticated());
        $this->assertSame('admin@acme.test', $result->user()?->email);
        $this->assertNotEmpty($auth->readRiskSignals($result->user()?->id));
    }
}
