<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\OAuth;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\OAuth\OAuthClientType;
use Avax\Auth\System\Capability\OAuth\OAuthGrantType;
use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraintType;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\OAuth\ApproveClientRegistration\ApproveClientRegistrationData;
use Avax\Auth\System\Flow\OAuth\RegisterClient\RegisterClientData;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flow\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Foundation\Clock;
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
