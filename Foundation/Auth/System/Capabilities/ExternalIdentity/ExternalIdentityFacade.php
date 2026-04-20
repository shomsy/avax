<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity;

use Avax\Auth\System\Capabilities\Federation\FederationConnection;
use Avax\Auth\System\Capabilities\Federation\FederationConnectionHealth;
use Avax\Auth\System\Capabilities\Federation\StartedFederatedLogin;
use Avax\Auth\System\Capabilities\OAuth\IssuedAuthorizationCode;
use Avax\Auth\System\Capabilities\OAuth\OAuthClient;
use Avax\Auth\System\Capabilities\OAuth\RegisteredOAuthClient;
use Avax\Auth\System\Capabilities\Oidc\OidcJsonWebKeySet;
use Avax\Auth\System\Capabilities\Oidc\OidcProviderMetadata;
use Avax\Auth\System\Flows\Federation\CheckHealth\CheckFederationConnectionHealth;
use Avax\Auth\System\Flows\Federation\CompleteFederatedLogin\CompleteFederatedLogin;
use Avax\Auth\System\Flows\Federation\CompleteFederatedLogin\CompleteFederatedLoginData;
use Avax\Auth\System\Flows\Federation\DiscoverConnection\DiscoverFederationConnection;
use Avax\Auth\System\Flows\Federation\EvaluateBreakGlass\EvaluateFederationBreakGlassBypass;
use Avax\Auth\System\Flows\Federation\ReadConnections\ReadFederationConnections;
use Avax\Auth\System\Flows\Federation\RegisterConnection\RegisterFederationConnection;
use Avax\Auth\System\Flows\Federation\RegisterConnection\RegisterFederationConnectionData;
use Avax\Auth\System\Flows\Federation\StartFederatedLogin\StartFederatedLogin;
use Avax\Auth\System\Flows\Federation\StartFederatedLogin\StartFederatedLoginData;
use Avax\Auth\System\Flows\Federation\SyncMetadata\SyncFederationMetadata;
use Avax\Auth\System\Flows\Federation\VerifyDomain\VerifyFederationDomain;
use Avax\Auth\System\Flows\Federation\VerifyDomain\VerifyFederationDomainData;
use Avax\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Auth\System\Flows\OAuth\ApproveClientRegistration\ApproveClientRegistration;
use Avax\Auth\System\Flows\OAuth\ApproveClientRegistration\ApproveClientRegistrationData;
use Avax\Auth\System\Flows\OAuth\AuthorizeCode\AuthorizeCode;
use Avax\Auth\System\Flows\OAuth\AuthorizeCode\AuthorizeCodeData;
use Avax\Auth\System\Flows\OAuth\DisableClient\DisableClient;
use Avax\Auth\System\Flows\OAuth\ExchangeAuthorizationCode\ExchangeAuthorizationCode;
use Avax\Auth\System\Flows\OAuth\ExchangeAuthorizationCode\ExchangeAuthorizationCodeData;
use Avax\Auth\System\Flows\OAuth\ExchangeClientCredentials\ExchangeClientCredentials;
use Avax\Auth\System\Flows\OAuth\ExchangeClientCredentials\ExchangeClientCredentialsData;
use Avax\Auth\System\Flows\OAuth\ExchangeRefreshToken\ExchangeRefreshToken;
use Avax\Auth\System\Flows\OAuth\ExchangeRefreshToken\ExchangeRefreshTokenData;
use Avax\Auth\System\Flows\OAuth\IntrospectToken\IntrospectToken;
use Avax\Auth\System\Flows\OAuth\IntrospectToken\IntrospectTokenData;
use Avax\Auth\System\Flows\OAuth\IntrospectToken\TokenIntrospection;
use Avax\Auth\System\Flows\OAuth\OAuthTokenGrant;
use Avax\Auth\System\Flows\OAuth\ReadClients\ReadClients;
use Avax\Auth\System\Flows\OAuth\ReadWorkloadIdentities\ReadWorkloadIdentities;
use Avax\Auth\System\Flows\OAuth\ReadWorkloadIdentities\WorkloadIdentityProfile;
use Avax\Auth\System\Flows\OAuth\RegisterClient\RegisterClient;
use Avax\Auth\System\Flows\OAuth\RegisterClient\RegisterClientData;
use Avax\Auth\System\Flows\OAuth\RevokeToken\RevokeToken;
use Avax\Auth\System\Flows\OAuth\RevokeToken\RevokeTokenData;
use Avax\Auth\System\Flows\OAuth\RotateClientSecret\RotateClientSecret;
use Avax\Auth\System\Flows\OAuth\UpdateClient\UpdateClient;
use Avax\Auth\System\Flows\OAuth\UpdateClient\UpdateClientData;
use Avax\Auth\System\Flows\Oidc\JarmResponse\BuildJarmResponse;
use Avax\Auth\System\Flows\Oidc\JarmResponse\BuildJarmResponseData;
use Avax\Auth\System\Flows\Oidc\JarmResponse\JarmResponse;
use Avax\Auth\System\Flows\Oidc\Logout\Logout;
use Avax\Auth\System\Flows\Oidc\Logout\LogoutData;
use Avax\Auth\System\Flows\Oidc\Logout\LogoutResult;
use Avax\Auth\System\Flows\Oidc\PushAuthorizationRequest\PushAuthorizationRequest;
use Avax\Auth\System\Flows\Oidc\PushAuthorizationRequest\PushAuthorizationRequestData;
use Avax\Auth\System\Flows\Oidc\PushAuthorizationRequest\PushedAuthorizationRequest;
use Avax\Auth\System\Flows\Oidc\ReadJsonWebKeySet\ReadOidcJsonWebKeySet;
use Avax\Auth\System\Flows\Oidc\ReadProviderMetadata\ReadOidcProviderMetadata;
use Avax\Auth\System\Flows\Oidc\ReadUserInfo\OidcUserInfo;
use Avax\Auth\System\Flows\Oidc\ReadUserInfo\ReadOidcUserInfo;
use DateMalformedStringException;
use JsonException;
use RuntimeException;
use SensitiveParameter;

final readonly class ExternalIdentityFacade
{
    public function __construct(
        private RegisterClient|null                                   $registerOAuthClient,
        private ApproveClientRegistration|null                        $approveOAuthClientRegistration,
        private UpdateClient|null                                     $updateOAuthClient,
        private DisableClient|null                                    $disableOAuthClient,
        #[\SensitiveParameter] private RotateClientSecret|null        $rotateOAuthClientSecret,
        private ReadClients|null                                      $readOAuthClients,
        private ReadWorkloadIdentities|null                           $readWorkloadIdentities,
        private ReadOidcProviderMetadata|null                         $readOidcProviderMetadata,
        private ReadOidcJsonWebKeySet|null                            $readOidcJsonWebKeySet,
        private ReadOidcUserInfo|null                                 $readOidcUserInfo,
        private PushAuthorizationRequest|null                         $pushOidcAuthorizationRequest,
        private Logout|null                                           $oidcLogout,
        private BuildJarmResponse|null                                $buildOidcJarmResponse,
        #[\SensitiveParameter] private AuthorizeCode|null             $authorizeOAuthCode,
        #[\SensitiveParameter] private ExchangeAuthorizationCode|null $exchangeOAuthCode,
        #[\SensitiveParameter] private ExchangeClientCredentials|null $exchangeOAuthClientCredentials,
        #[\SensitiveParameter] private ExchangeRefreshToken|null      $exchangeOAuthRefreshToken,
        #[\SensitiveParameter] private RevokeToken|null               $revokeOAuthToken,
        #[\SensitiveParameter] private IntrospectToken|null           $introspectOAuthToken,
        private RegisterFederationConnection|null                     $registerFederationConnection,
        private ReadFederationConnections|null                        $readFederationConnections,
        private VerifyFederationDomain|null                           $verifyFederationDomain,
        private SyncFederationMetadata|null                           $syncFederationMetadata,
        private CheckFederationConnectionHealth|null                  $checkFederationConnectionHealth,
        private EvaluateFederationBreakGlassBypass|null               $evaluateFederationBreakGlassBypass,
        private DiscoverFederationConnection|null                     $discoverFederationConnection,
        private StartFederatedLogin|null                              $startFederatedLogin,
        private CompleteFederatedLogin|null                           $completeFederatedLogin
    ) {}

    public function registerOAuthClient(RegisterClientData $data) : RegisteredOAuthClient
    {
        return $this->registerOAuthClientOrFail()->execute(data: $data);
    }

    public function approveOAuthClientRegistration(ApproveClientRegistrationData $data) : OAuthClient
    {
        return $this->approveOAuthClientRegistrationOrFail()->execute(data: $data);
    }

    public function updateOAuthClient(UpdateClientData $data) : OAuthClient
    {
        return $this->updateOAuthClientOrFail()->execute(data: $data);
    }

    public function disableOAuthClient(string $clientId) : OAuthClient
    {
        return $this->disableOAuthClientOrFail()->execute(clientId: $clientId);
    }

    public function rotateOAuthClientSecret(string $clientId) : RegisteredOAuthClient
    {
        return $this->rotateOAuthClientSecretOrFail()->execute(clientId: $clientId);
    }

    /**
     * @return list<OAuthClient>
     */
    public function readOAuthClients() : array
    {
        return $this->readOAuthClientsOrFail()->execute();
    }

    /**
     * @return list<WorkloadIdentityProfile>
     */
    public function readWorkloadIdentities() : array
    {
        return $this->readWorkloadIdentitiesOrFail()->execute();
    }

    public function readOidcProviderMetadata() : OidcProviderMetadata
    {
        return $this->readOidcProviderMetadataOrFail()->execute();
    }

    public function readOidcJsonWebKeySet() : OidcJsonWebKeySet
    {
        return $this->readOidcJsonWebKeySetOrFail()->execute();
    }

    public function readOidcUserInfo(#[SensitiveParameter] string $accessToken) : OidcUserInfo
    {
        return $this->readOidcUserInfoOrFail()->execute(accessToken: $accessToken);
    }

    public function pushOidcAuthorizationRequest(PushAuthorizationRequestData $data) : PushedAuthorizationRequest
    {
        return $this->pushOidcAuthorizationRequestOrFail()->execute(data: $data);
    }

    public function oidcLogout(LogoutData $data) : LogoutResult
    {
        return $this->oidcLogoutOrFail()->execute(data: $data);
    }

    public function buildOidcJarmResponse(BuildJarmResponseData $data) : JarmResponse
    {
        return $this->buildOidcJarmResponseOrFail()->execute(data: $data);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function authorizeOAuthCode(AuthorizeCodeData $data) : IssuedAuthorizationCode
    {
        return $this->authorizeOAuthCodeOrFail()->execute(data: $data);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function exchangeOAuthCode(ExchangeAuthorizationCodeData $data) : OAuthTokenGrant
    {
        return $this->exchangeOAuthCodeOrFail()->execute(data: $data);
    }

    public function exchangeOAuthClientCredentials(ExchangeClientCredentialsData $data) : OAuthTokenGrant
    {
        return $this->exchangeOAuthClientCredentialsOrFail()->execute(data: $data);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function exchangeOAuthRefreshToken(ExchangeRefreshTokenData $data) : OAuthTokenGrant
    {
        return $this->exchangeOAuthRefreshTokenOrFail()->execute(data: $data);
    }

    public function revokeOAuthToken(RevokeTokenData $data) : void
    {
        $this->revokeOAuthTokenOrFail()->execute(data: $data);
    }

    public function introspectOAuthToken(IntrospectTokenData $data) : TokenIntrospection
    {
        return $this->introspectOAuthTokenOrFail()->execute(data: $data);
    }

    public function registerFederationConnection(RegisterFederationConnectionData $data) : FederationConnection
    {
        return $this->registerFederationConnectionOrFail()->execute(data: $data);
    }

    /**
     * @return list<FederationConnection>
     */
    public function readFederationConnections() : array
    {
        return $this->readFederationConnectionsOrFail()->execute();
    }

    public function verifyFederationDomain(VerifyFederationDomainData $data) : FederationConnection
    {
        return $this->verifyFederationDomainOrFail()->execute(data: $data);
    }

    /**
     * @throws JsonException
     */
    public function syncFederationMetadata(string $connectionId) : FederationConnection
    {
        return $this->syncFederationMetadataOrFail()->execute(connectionId: $connectionId);
    }

    public function checkFederationConnectionHealth(string $connectionId) : FederationConnectionHealth
    {
        return $this->checkFederationConnectionHealthOrFail()->execute(connectionId: $connectionId);
    }

    public function evaluateFederationBreakGlassBypass(string $connectionId) : bool
    {
        return $this->evaluateFederationBreakGlassBypassOrFail()->execute(connectionId: $connectionId);
    }

    public function discoverFederationConnection(#[SensitiveParameter] string $email) : FederationConnection|null
    {
        return $this->discoverFederationConnectionOrFail()->execute(email: $email);
    }

    public function startFederatedLogin(StartFederatedLoginData $data) : StartedFederatedLogin
    {
        return $this->startFederatedLoginOrFail()->execute(data: $data);
    }

    public function completeFederatedLogin(CompleteFederatedLoginData $data) : AuthenticationResult
    {
        return $this->completeFederatedLoginOrFail()->execute(data: $data);
    }

    private function registerOAuthClientOrFail() : RegisterClient
    {
        return $this->registerOAuthClient ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    private function approveOAuthClientRegistrationOrFail() : ApproveClientRegistration
    {
        return $this->approveOAuthClientRegistration ?? throw new RuntimeException(message: 'OAuth client approval is not configured.');
    }

    private function updateOAuthClientOrFail() : UpdateClient
    {
        return $this->updateOAuthClient ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    private function disableOAuthClientOrFail() : DisableClient
    {
        return $this->disableOAuthClient ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    private function rotateOAuthClientSecretOrFail() : RotateClientSecret
    {
        return $this->rotateOAuthClientSecret ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    private function readOAuthClientsOrFail() : ReadClients
    {
        return $this->readOAuthClients ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    private function readWorkloadIdentitiesOrFail() : ReadWorkloadIdentities
    {
        return $this->readWorkloadIdentities ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    private function readOidcProviderMetadataOrFail() : ReadOidcProviderMetadata
    {
        return $this->readOidcProviderMetadata ?? throw new RuntimeException(message: 'OIDC provider is not configured.');
    }

    private function readOidcJsonWebKeySetOrFail() : ReadOidcJsonWebKeySet
    {
        return $this->readOidcJsonWebKeySet ?? throw new RuntimeException(message: 'OIDC provider is not configured.');
    }

    private function readOidcUserInfoOrFail() : ReadOidcUserInfo
    {
        return $this->readOidcUserInfo ?? throw new RuntimeException(message: 'OIDC provider is not configured.');
    }

    private function pushOidcAuthorizationRequestOrFail() : PushAuthorizationRequest
    {
        return $this->pushOidcAuthorizationRequest ?? throw new RuntimeException(message: 'OIDC PAR support is not configured.');
    }

    private function oidcLogoutOrFail() : Logout
    {
        return $this->oidcLogout ?? throw new RuntimeException(message: 'OIDC logout support is not configured.');
    }

    private function buildOidcJarmResponseOrFail() : BuildJarmResponse
    {
        return $this->buildOidcJarmResponse ?? throw new RuntimeException(message: 'OIDC JARM support is not configured.');
    }

    private function authorizeOAuthCodeOrFail() : AuthorizeCode
    {
        return $this->authorizeOAuthCode ?? throw new RuntimeException(message: 'OAuth authorization code flow is not configured.');
    }

    private function exchangeOAuthCodeOrFail() : ExchangeAuthorizationCode
    {
        return $this->exchangeOAuthCode ?? throw new RuntimeException(message: 'OAuth token exchange is not configured.');
    }

    private function exchangeOAuthClientCredentialsOrFail() : ExchangeClientCredentials
    {
        return $this->exchangeOAuthClientCredentials ?? throw new RuntimeException(message: 'OAuth client credentials flow is not configured.');
    }

    private function exchangeOAuthRefreshTokenOrFail() : ExchangeRefreshToken
    {
        return $this->exchangeOAuthRefreshToken ?? throw new RuntimeException(message: 'OAuth refresh flow is not configured.');
    }

    private function revokeOAuthTokenOrFail() : RevokeToken
    {
        return $this->revokeOAuthToken ?? throw new RuntimeException(message: 'OAuth revoke flow is not configured.');
    }

    private function introspectOAuthTokenOrFail() : IntrospectToken
    {
        return $this->introspectOAuthToken ?? throw new RuntimeException(message: 'OAuth introspection is not configured.');
    }

    private function registerFederationConnectionOrFail() : RegisterFederationConnection
    {
        return $this->registerFederationConnection ?? throw new RuntimeException(message: 'Federation runtime is not configured.');
    }

    private function readFederationConnectionsOrFail() : ReadFederationConnections
    {
        return $this->readFederationConnections ?? throw new RuntimeException(message: 'Federation runtime is not configured.');
    }

    private function verifyFederationDomainOrFail() : VerifyFederationDomain
    {
        return $this->verifyFederationDomain ?? throw new RuntimeException(message: 'Federation runtime is not configured.');
    }

    private function syncFederationMetadataOrFail() : SyncFederationMetadata
    {
        return $this->syncFederationMetadata ?? throw new RuntimeException(message: 'Federation metadata runtime is not configured.');
    }

    private function checkFederationConnectionHealthOrFail() : CheckFederationConnectionHealth
    {
        return $this->checkFederationConnectionHealth ?? throw new RuntimeException(message: 'Federation health checks are not configured.');
    }

    private function evaluateFederationBreakGlassBypassOrFail() : EvaluateFederationBreakGlassBypass
    {
        return $this->evaluateFederationBreakGlassBypass ?? throw new RuntimeException(message: 'Federation runtime is not configured.');
    }

    private function discoverFederationConnectionOrFail() : DiscoverFederationConnection
    {
        return $this->discoverFederationConnection ?? throw new RuntimeException(message: 'Federation runtime is not configured.');
    }

    private function startFederatedLoginOrFail() : StartFederatedLogin
    {
        return $this->startFederatedLogin ?? throw new RuntimeException(message: 'Federation runtime is not configured.');
    }

    private function completeFederatedLoginOrFail() : CompleteFederatedLogin
    {
        return $this->completeFederatedLogin ?? throw new RuntimeException(message: 'Federation runtime is not configured.');
    }
}
