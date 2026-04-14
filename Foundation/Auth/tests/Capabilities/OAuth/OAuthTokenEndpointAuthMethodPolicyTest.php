<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\OAuth;

use Avax\Auth\System\Capability\OAuth\OAuthClientType;
use Avax\Auth\System\Capability\OAuth\OAuthTokenEndpointAuthMethod;
use Avax\Auth\System\Capability\OAuth\OAuthTokenEndpointAuthMethodPolicy;
use PHPUnit\Framework\TestCase;

final class OAuthTokenEndpointAuthMethodPolicyTest extends TestCase
{
    public function testPublicClientsDefaultToNone() : void
    {
        $policy = new OAuthTokenEndpointAuthMethodPolicy();

        $resolved = $policy->resolve(type: OAuthClientType::PUBLIC);

        $this->assertSame(expected: OAuthTokenEndpointAuthMethod::NONE, actual: $resolved);
    }

    public function testConfidentialClientsDefaultToBasicAndPreserveExistingMethodWhenValid() : void
    {
        $policy = new OAuthTokenEndpointAuthMethodPolicy();

        $default = $policy->resolve(type: OAuthClientType::CONFIDENTIAL);
        $preserved = $policy->resolve(
            type: OAuthClientType::CONFIDENTIAL,
            current: OAuthTokenEndpointAuthMethod::CLIENT_SECRET_BASIC
        );

        $this->assertSame(expected: OAuthTokenEndpointAuthMethod::CLIENT_SECRET_BASIC, actual: $default);
        $this->assertSame(expected: OAuthTokenEndpointAuthMethod::CLIENT_SECRET_BASIC, actual: $preserved);
    }

    public function testConfidentialClientsFallbackToBasicWhenExistingMethodIsNone() : void
    {
        $policy = new OAuthTokenEndpointAuthMethodPolicy();

        $resolved = $policy->resolve(
            type: OAuthClientType::CONFIDENTIAL,
            current: OAuthTokenEndpointAuthMethod::NONE
        );

        $this->assertSame(expected: OAuthTokenEndpointAuthMethod::CLIENT_SECRET_BASIC, actual: $resolved);
    }

    public function testPublicClientsRejectConflictingMethod() : void
    {
        $policy = new OAuthTokenEndpointAuthMethodPolicy();

        $this->expectException(\InvalidArgumentException::class);

        $policy->resolve(
            type: OAuthClientType::PUBLIC,
            requested: OAuthTokenEndpointAuthMethod::CLIENT_SECRET_BASIC
        );
    }
}
