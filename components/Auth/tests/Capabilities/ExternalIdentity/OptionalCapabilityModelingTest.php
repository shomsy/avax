<?php

declare(strict_types=1);

namespace components\Auth\Tests\Capabilities\ExternalIdentity;

use components\Auth\System\Capabilities\ExternalIdentity\ExternalIdentityCapabilityUnavailable;
use components\Auth\System\Capabilities\ExternalIdentity\OAuth\OAuth;
use components\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\OpenIDConnect;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\SingleSignOn;
use components\Tests\TestCase;
use PHPUnit\Framework\TestCase;

final class OptionalCapabilityModelingTest extends TestCase
{
    public function testOAuthReportsUnavailableSurfaceExplicitly() : void
    {
        $oauth = new OAuth(
            registerClient           : null,
            approveClientRegistration: null,
            updateClient             : null,
            disableClient            : null,
            rotateClientSecret       : null,
            readClients              : null,
            readWorkloadIdentities   : null,
            authorizeCode            : null,
            exchangeAuthorizationCode: null,
            exchangeClientCredentials: null,
            exchangeRefreshToken     : null,
            revokeToken              : null,
            introspectToken          : null
        );

        $this->assertFalse(condition: $oauth->isConfigured());

        $this->expectException(ExternalIdentityCapabilityUnavailable::class);
        $this->expectExceptionMessage('OAuth operation [read_clients] is not configured.');

        $oauth->readClients();
    }

    public function testOidcExposesFeatureReadinessSeparatelyFromAvailabilityFailures() : void
    {
        $oidc = new OpenIDConnect(
            readProviderMetadata    : null,
            readJsonWebKeySet       : null,
            readUserInfo            : null,
            pushAuthorizationRequest: null,
            logout                  : null,
            buildJarmResponse       : null
        );

        $this->assertFalse(condition: $oidc->isConfigured());
        $this->assertFalse(condition: $oidc->supportsPushedAuthorizationRequests());
        $this->assertFalse(condition: $oidc->supportsLogout());
        $this->assertFalse(condition: $oidc->supportsJarmResponse());

        $this->expectException(ExternalIdentityCapabilityUnavailable::class);
        $this->expectExceptionMessage('OpenID Connect operation [read_provider_metadata] is not configured.');

        $oidc->readProviderMetadata();
    }

    public function testFederationReportsUnavailableSurfaceExplicitly() : void
    {
        $singleSignOn = new SingleSignOn(
            registerConnection      : null,
            readConnections         : null,
            verifyDomain            : null,
            syncMetadata            : null,
            checkConnectionHealth   : null,
            evaluateBreakGlassBypass: null,
            discoverConnection      : null,
            startFederatedLogin     : null,
            completeFederatedLogin  : null
        );

        $this->assertFalse(condition: $singleSignOn->isConfigured());

        $this->expectException(ExternalIdentityCapabilityUnavailable::class);
        $this->expectExceptionMessage('Federation operation [read_connections] is not configured.');

        $singleSignOn->readConnections();
    }
}
