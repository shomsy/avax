<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect;

use Avax\Auth\System\Capabilities\ExternalIdentity\ExternalIdentityCapabilityUnavailable;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\JarmResponse\BuildJarmResponse;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\JarmResponse\BuildJarmResponseData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\JarmResponse\JarmResponse;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout\Logout;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout\LogoutData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout\LogoutResult;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\PushAuthorizationRequest\PushAuthorizationRequest;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\PushAuthorizationRequest\PushAuthorizationRequestData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\PushAuthorizationRequest\PushedAuthorizationRequest;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\ReadJsonWebKeySet\ReadOidcJsonWebKeySet;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\ReadProviderMetadata\ReadOidcProviderMetadata;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\ReadUserInfo\OidcUserInfo;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\ReadUserInfo\ReadOidcUserInfo;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcJsonWebKeySet;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcProviderMetadata;
use DateMalformedStringException;
use Random\RandomException;
use SensitiveParameter;

final readonly class OpenIDConnect
{
    public function __construct(
        private ReadOidcProviderMetadata|null $readProviderMetadata,
        private ReadOidcJsonWebKeySet|null    $readJsonWebKeySet,
        private ReadOidcUserInfo|null         $readUserInfo,
        private PushAuthorizationRequest|null $pushAuthorizationRequest,
        private Logout|null                   $logout,
        private BuildJarmResponse|null        $buildJarmResponse
    ) {}

    public function isConfigured() : bool
    {
        return $this->readProviderMetadata !== null
            && $this->readJsonWebKeySet !== null
            && $this->readUserInfo !== null;
    }

    public function supportsPushedAuthorizationRequests() : bool
    {
        return $this->pushAuthorizationRequest !== null;
    }

    public function supportsLogout() : bool
    {
        return $this->logout !== null;
    }

    public function supportsJarmResponse() : bool
    {
        return $this->buildJarmResponse !== null;
    }

    public function readProviderMetadata() : OidcProviderMetadata
    {
        return $this->readProviderMetadataOrFail()->execute();
    }

    private function readProviderMetadataOrFail() : ReadOidcProviderMetadata
    {
        return $this->readProviderMetadata ?? throw ExternalIdentityCapabilityUnavailable::oidc(operation: 'read_provider_metadata');
    }

    public function readJsonWebKeySet() : OidcJsonWebKeySet
    {
        return $this->readJsonWebKeySetOrFail()->execute();
    }

    private function readJsonWebKeySetOrFail() : ReadOidcJsonWebKeySet
    {
        return $this->readJsonWebKeySet ?? throw ExternalIdentityCapabilityUnavailable::oidc(operation: 'read_json_web_key_set');
    }

    public function readUserInfo(#[SensitiveParameter] string $accessToken) : OidcUserInfo
    {
        return $this->readUserInfoOrFail()->execute(accessToken: $accessToken);
    }

    private function readUserInfoOrFail() : ReadOidcUserInfo
    {
        return $this->readUserInfo ?? throw ExternalIdentityCapabilityUnavailable::oidc(operation: 'read_user_info');
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function pushAuthorizationRequest(PushAuthorizationRequestData $data) : PushedAuthorizationRequest
    {
        return $this->pushAuthorizationRequestOrFail()->execute(data: $data);
    }

    private function pushAuthorizationRequestOrFail() : PushAuthorizationRequest
    {
        return $this->pushAuthorizationRequest ?? throw ExternalIdentityCapabilityUnavailable::oidc(operation: 'push_authorization_request');
    }

    public function logout(LogoutData $data) : LogoutResult
    {
        return $this->logoutOrFail()->execute(data: $data);
    }

    private function logoutOrFail() : Logout
    {
        return $this->logout ?? throw ExternalIdentityCapabilityUnavailable::oidc(operation: 'logout');
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function buildJarmResponse(BuildJarmResponseData $data) : JarmResponse
    {
        return $this->buildJarmResponseOrFail()->execute(data: $data);
    }

    private function buildJarmResponseOrFail() : BuildJarmResponse
    {
        return $this->buildJarmResponse ?? throw ExternalIdentityCapabilityUnavailable::oidc(operation: 'build_jarm_response');
    }
}
