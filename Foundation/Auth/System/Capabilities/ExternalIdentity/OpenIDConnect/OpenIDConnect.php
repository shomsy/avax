<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect;

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
use RuntimeException;
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

    public function readProviderMetadata() : OidcProviderMetadata
    {
        return $this->readProviderMetadataOrFail()->execute();
    }

    private function readProviderMetadataOrFail() : ReadOidcProviderMetadata
    {
        return $this->readProviderMetadata ?? throw new RuntimeException(message: 'OIDC provider is not configured.');
    }

    public function readJsonWebKeySet() : OidcJsonWebKeySet
    {
        return $this->readJsonWebKeySetOrFail()->execute();
    }

    private function readJsonWebKeySetOrFail() : ReadOidcJsonWebKeySet
    {
        return $this->readJsonWebKeySet ?? throw new RuntimeException(message: 'OIDC provider is not configured.');
    }

    public function readUserInfo(#[SensitiveParameter] string $accessToken) : OidcUserInfo
    {
        return $this->readUserInfoOrFail()->execute(accessToken: $accessToken);
    }

    private function readUserInfoOrFail() : ReadOidcUserInfo
    {
        return $this->readUserInfo ?? throw new RuntimeException(message: 'OIDC provider is not configured.');
    }

    public function pushAuthorizationRequest(PushAuthorizationRequestData $data) : PushedAuthorizationRequest
    {
        return $this->pushAuthorizationRequestOrFail()->execute(data: $data);
    }

    private function pushAuthorizationRequestOrFail() : PushAuthorizationRequest
    {
        return $this->pushAuthorizationRequest ?? throw new RuntimeException(message: 'OIDC PAR support is not configured.');
    }

    public function logout(LogoutData $data) : LogoutResult
    {
        return $this->logoutOrFail()->execute(data: $data);
    }

    private function logoutOrFail() : Logout
    {
        return $this->logout ?? throw new RuntimeException(message: 'OIDC logout support is not configured.');
    }

    public function buildJarmResponse(BuildJarmResponseData $data) : JarmResponse
    {
        return $this->buildJarmResponseOrFail()->execute(data: $data);
    }

    private function buildJarmResponseOrFail() : BuildJarmResponse
    {
        return $this->buildJarmResponse ?? throw new RuntimeException(message: 'OIDC JARM support is not configured.');
    }
}
