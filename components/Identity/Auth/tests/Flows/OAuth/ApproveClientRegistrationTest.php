<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Flows\OAuth;

use Avax\Components\Identity\Auth\System\Auth;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ApproveClientRegistration\ApproveClientRegistrationData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RegisterClient\RegisterClientData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClientType;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthGrantType;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraintType;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Codec\HmacTokenCodec;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\InMemoryRefreshTokenStore;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\InMemoryTokenRevocationStore;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

final class ApproveClientRegistrationTest extends TestCase
{
    public function testHighRiskOAuthClientRegistrationRequiresApprovalAndCanBeApproved() : void
    {
        $auth = $this->buildAuth();

        $registered = $auth->registerOAuthClient(data: new RegisterClientData(
                                                           name                    : 'Workload API',
                                                           type                    : OAuthClientType::CONFIDENTIAL,
                                                           redirectUris            : ['urn:avax:oauth:workload-api'],
                                                           allowedScopes           : ['workload.read'],
                                                           allowedAudiences        : ['workload-api'],
                                                           allowedGrantTypes       : [OAuthGrantType::CLIENT_CREDENTIALS],
                                                           requiredSenderConstraint: OAuthSenderConstraintType::MTLS,
                                                           workloadIdentity        : true,
                                                           approvalRequired        : true
                                                       ));

        $this->assertTrue(condition: $registered->client->isPendingApproval());
        $this->assertFalse(condition: $registered->client->active);

        $approved = $auth->approveOAuthClientRegistration(data: new ApproveClientRegistrationData(
                                                                    clientId  : $registered->client->clientId,
                                                                    approvedBy: 'approver'
                                                                ));

        $this->assertTrue(condition: $approved->isApproved());
        $this->assertTrue(condition: $approved->active);
        $this->assertSame(expected: 'approver', actual: $approved->approvedBy);
        $this->assertSame(expected: 'approved', actual: $approved->approvalStatus->value);
    }

    private function buildAuth() : Auth
    {
        $userSource    = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();
        $jwtIdentity   = new JwtIdentity(
            userSource       : $userSource,
            codec            : new HmacTokenCodec(secret: 'oauth-approval-secret'),
            clock            : new Clock(),
            revocationStore  : new InMemoryTokenRevocationStore(),
            refreshTokenStore: $refreshTokens
        );

        return Auth::configuration()
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: new Identity(jwtIdentity: $jwtIdentity))
            ->withRefreshTokenStore(refreshTokenStore: $refreshTokens)
            ->ready();
    }
}
