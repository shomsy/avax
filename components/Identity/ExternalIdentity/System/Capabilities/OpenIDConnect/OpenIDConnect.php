<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityCapabilityUnavailable;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcJsonWebKeySet;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcProviderMetadata;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\JarmResponse\BuildJarmResponse;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\JarmResponse\BuildJarmResponseData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\JarmResponse\JarmResponse;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\Logout\Logout;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\Logout\LogoutData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\Logout\LogoutResult;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\PushAuthorizationRequest\PushAuthorizationRequest;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\PushAuthorizationRequest\PushAuthorizationRequestData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\PushAuthorizationRequest\PushedAuthorizationRequest;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\ReadJsonWebKeySet\ReadOidcJsonWebKeySet;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\ReadProviderMetadata\ReadOidcProviderMetadata;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\ReadUserInfo\OidcUserInfo;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\ReadUserInfo\ReadOidcUserInfo;
use DateMalformedStringException;
use Random\RandomException;
use SensitiveParameter;

final readonly class OpenIDConnect
{
    public function __construct(
        private ?ReadOidcProviderMetadata $readOidcProviderMetadata,
        private ?ReadOidcJsonWebKeySet    $readOidcJsonWebKeySet,
        private ?ReadOidcUserInfo         $readOidcUserInfo,
        private ?PushAuthorizationRequest $pushAuthorizationRequest,
        private ?Logout $logout,
        private ?BuildJarmResponse $buildJarmResponse,
    ) {}

    public function isConfigured(): bool
    {
        return $this->readOidcProviderMetadata instanceof ReadOidcProviderMetadata
            && $this->readOidcJsonWebKeySet instanceof ReadOidcJsonWebKeySet
            && $this->readOidcUserInfo instanceof ReadOidcUserInfo;
    }

    public function supportsPushedAuthorizationRequests(): bool
    {
        return $this->pushAuthorizationRequest instanceof PushAuthorizationRequest;
    }

    public function supportsLogout(): bool
    {
        return $this->logout instanceof Logout;
    }

    public function supportsJarmResponse(): bool
    {
        return $this->buildJarmResponse instanceof BuildJarmResponse;
    }

    public function readProviderMetadata(): OidcProviderMetadata
    {
        return $this->readProviderMetadataOrFail()->execute();
    }

    private function readProviderMetadataOrFail(): ReadOidcProviderMetadata
    {
        return $this->readOidcProviderMetadata ?? throw ExternalIdentityCapabilityUnavailable::oidc(operation: 'read_provider_metadata');
    }

    public function readJsonWebKeySet(): OidcJsonWebKeySet
    {
        return $this->readJsonWebKeySetOrFail()->execute();
    }

    private function readJsonWebKeySetOrFail(): ReadOidcJsonWebKeySet
    {
        return $this->readOidcJsonWebKeySet ?? throw ExternalIdentityCapabilityUnavailable::oidc(operation: 'read_json_web_key_set');
    }

    public function readUserInfo(#[SensitiveParameter] string $accessToken): OidcUserInfo
    {
        return $this->readUserInfoOrFail()->execute(accessToken: $accessToken);
    }

    private function readUserInfoOrFail(): ReadOidcUserInfo
    {
        return $this->readOidcUserInfo ?? throw ExternalIdentityCapabilityUnavailable::oidc(operation: 'read_user_info');
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function pushAuthorizationRequest(PushAuthorizationRequestData $pushAuthorizationRequestData) : PushedAuthorizationRequest
    {
        return $this->pushAuthorizationRequestOrFail()->execute(data: $pushAuthorizationRequestData);
    }

    private function pushAuthorizationRequestOrFail(): PushAuthorizationRequest
    {
        return $this->pushAuthorizationRequest ?? throw ExternalIdentityCapabilityUnavailable::oidc(operation: 'push_authorization_request');
    }

    public function logout(LogoutData $logoutData) : LogoutResult
    {
        return $this->logoutOrFail()->execute(data: $logoutData);
    }

    private function logoutOrFail(): Logout
    {
        return $this->logout ?? throw ExternalIdentityCapabilityUnavailable::oidc(operation: 'logout');
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function buildJarmResponse(BuildJarmResponseData $buildJarmResponseData) : JarmResponse
    {
        return $this->buildJarmResponseOrFail()->execute(data: $buildJarmResponseData);
    }

    private function buildJarmResponseOrFail(): BuildJarmResponse
    {
        return $this->buildJarmResponse ?? throw ExternalIdentityCapabilityUnavailable::oidc(operation: 'build_jarm_response');
    }
}
