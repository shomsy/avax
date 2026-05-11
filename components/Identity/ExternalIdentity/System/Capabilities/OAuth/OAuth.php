<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityCapabilityUnavailable;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\IssuedAuthorizationCode;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\RegisteredOAuthClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ApproveClientRegistration\ApproveClientRegistration;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ApproveClientRegistration\ApproveClientRegistrationData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\AuthorizeCode\AuthorizeCode;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\AuthorizeCode\AuthorizeCodeData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\DisableClient\DisableClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeAuthorizationCode\ExchangeAuthorizationCode;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeAuthorizationCode\ExchangeAuthorizationCodeData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeClientCredentials\ExchangeClientCredentials;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeClientCredentials\ExchangeClientCredentialsData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeRefreshToken\ExchangeRefreshToken;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeRefreshToken\ExchangeRefreshTokenData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\IntrospectToken\IntrospectToken;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\IntrospectToken\IntrospectTokenData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\IntrospectToken\TokenIntrospection;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\OAuthTokenGrant;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ReadClients\ReadClients;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ReadWorkloadIdentities\ReadWorkloadIdentities;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ReadWorkloadIdentities\WorkloadIdentityProfile;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\RegisterClient\RegisterClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\RegisterClient\RegisterClientData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\RevokeToken\RevokeToken;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\RevokeToken\RevokeTokenData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\RotateClientSecret\RotateClientSecret;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\UpdateClient\UpdateClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\UpdateClient\UpdateClientData;
use DateMalformedStringException;
use SensitiveParameter;

final readonly class OAuth
{
    public function __construct(
        private RegisterClient|null            $registerClient,
        private ApproveClientRegistration|null $approveClientRegistration,
        private UpdateClient|null              $updateClient,
        private DisableClient|null             $disableClient,
        #[SensitiveParameter]
        private RotateClientSecret|null        $rotateClientSecret,
        private ReadClients|null               $readClients,
        private ReadWorkloadIdentities|null    $readWorkloadIdentities,
        #[SensitiveParameter]
        private AuthorizeCode|null             $authorizeCode,
        #[SensitiveParameter]
        private ExchangeAuthorizationCode|null $exchangeAuthorizationCode,
        #[SensitiveParameter]
        private ExchangeClientCredentials|null $exchangeClientCredentials,
        #[SensitiveParameter]
        private ExchangeRefreshToken|null      $exchangeRefreshToken,
        #[SensitiveParameter]
        private RevokeToken|null               $revokeToken,
        #[SensitiveParameter]
        private IntrospectToken|null           $introspectToken,
    ) {}

    public function isConfigured() : bool
    {
        return $this->registerClient instanceof RegisterClient
            && $this->approveClientRegistration instanceof ApproveClientRegistration
            && $this->updateClient instanceof UpdateClient
            && $this->disableClient instanceof DisableClient
            && $this->rotateClientSecret instanceof RotateClientSecret
            && $this->readClients instanceof ReadClients
            && $this->readWorkloadIdentities instanceof ReadWorkloadIdentities
            && $this->authorizeCode instanceof AuthorizeCode
            && $this->exchangeAuthorizationCode instanceof ExchangeAuthorizationCode
            && $this->exchangeClientCredentials instanceof ExchangeClientCredentials
            && $this->exchangeRefreshToken instanceof ExchangeRefreshToken
            && $this->revokeToken instanceof RevokeToken
            && $this->introspectToken instanceof IntrospectToken;
    }

    public function registerClient(RegisterClientData $registerClientData) : RegisteredOAuthClient
    {
        return $this->registerClientOrFail()->execute(data: $registerClientData);
    }

    private function registerClientOrFail() : RegisterClient
    {
        return $this->registerClient ?? throw ExternalIdentityCapabilityUnavailable::oauth(operation: 'register_client');
    }

    public function approveClientRegistration(ApproveClientRegistrationData $approveClientRegistrationData) : OAuthClient
    {
        return $this->approveClientRegistrationOrFail()->execute(data: $approveClientRegistrationData);
    }

    private function approveClientRegistrationOrFail() : ApproveClientRegistration
    {
        return $this->approveClientRegistration ?? throw ExternalIdentityCapabilityUnavailable::oauth(operation: 'approve_client_registration');
    }

    public function updateClient(UpdateClientData $updateClientData) : OAuthClient
    {
        return $this->updateClientOrFail()->execute(data: $updateClientData);
    }

    private function updateClientOrFail() : UpdateClient
    {
        return $this->updateClient ?? throw ExternalIdentityCapabilityUnavailable::oauth(operation: 'update_client');
    }

    public function disableClient(string $clientId) : OAuthClient
    {
        return $this->disableClientOrFail()->execute(clientId: $clientId);
    }

    private function disableClientOrFail() : DisableClient
    {
        return $this->disableClient ?? throw ExternalIdentityCapabilityUnavailable::oauth(operation: 'disable_client');
    }

    public function rotateClientSecret(string $clientId) : RegisteredOAuthClient
    {
        return $this->rotateClientSecretOrFail()->execute(clientId: $clientId);
    }

    private function rotateClientSecretOrFail() : RotateClientSecret
    {
        return $this->rotateClientSecret ?? throw ExternalIdentityCapabilityUnavailable::oauth(operation: 'rotate_client_secret');
    }

    /**
     * @return list<OAuthClient>
     */
    public function readClients() : array
    {
        return $this->readClientsOrFail()->execute();
    }

    private function readClientsOrFail() : ReadClients
    {
        return $this->readClients ?? throw ExternalIdentityCapabilityUnavailable::oauth(operation: 'read_clients');
    }

    /**
     * @return list<WorkloadIdentityProfile>
     */
    public function readWorkloadIdentities() : array
    {
        return $this->readWorkloadIdentitiesOrFail()->execute();
    }

    private function readWorkloadIdentitiesOrFail() : ReadWorkloadIdentities
    {
        return $this->readWorkloadIdentities ?? throw ExternalIdentityCapabilityUnavailable::oauth(operation: 'read_workload_identities');
    }

    /**
     * @throws DateMalformedStringException
     */
    public function authorizeCode(AuthorizeCodeData $authorizeCodeData) : IssuedAuthorizationCode
    {
        return $this->authorizeCodeOrFail()->execute(data: $authorizeCodeData);
    }

    private function authorizeCodeOrFail() : AuthorizeCode
    {
        return $this->authorizeCode ?? throw ExternalIdentityCapabilityUnavailable::oauth(operation: 'authorize_code');
    }

    /**
     * @throws DateMalformedStringException
     */
    public function exchangeAuthorizationCode(ExchangeAuthorizationCodeData $exchangeAuthorizationCodeData) : OAuthTokenGrant
    {
        return $this->exchangeAuthorizationCodeOrFail()->execute(data: $exchangeAuthorizationCodeData);
    }

    private function exchangeAuthorizationCodeOrFail() : ExchangeAuthorizationCode
    {
        return $this->exchangeAuthorizationCode ?? throw ExternalIdentityCapabilityUnavailable::oauth(operation: 'exchange_authorization_code');
    }

    public function exchangeClientCredentials(ExchangeClientCredentialsData $exchangeClientCredentialsData) : OAuthTokenGrant
    {
        return $this->exchangeClientCredentialsOrFail()->execute(data: $exchangeClientCredentialsData);
    }

    private function exchangeClientCredentialsOrFail() : ExchangeClientCredentials
    {
        return $this->exchangeClientCredentials ?? throw ExternalIdentityCapabilityUnavailable::oauth(operation: 'exchange_client_credentials');
    }

    /**
     * @throws DateMalformedStringException
     */
    public function exchangeRefreshToken(ExchangeRefreshTokenData $exchangeRefreshTokenData) : OAuthTokenGrant
    {
        return $this->exchangeRefreshTokenOrFail()->execute(data: $exchangeRefreshTokenData);
    }

    private function exchangeRefreshTokenOrFail() : ExchangeRefreshToken
    {
        return $this->exchangeRefreshToken ?? throw ExternalIdentityCapabilityUnavailable::oauth(operation: 'exchange_refresh_token');
    }

    public function revokeToken(RevokeTokenData $revokeTokenData) : void
    {
        $this->revokeTokenOrFail()->execute(data: $revokeTokenData);
    }

    private function revokeTokenOrFail() : RevokeToken
    {
        return $this->revokeToken ?? throw ExternalIdentityCapabilityUnavailable::oauth(operation: 'revoke_token');
    }

    public function introspectToken(IntrospectTokenData $introspectTokenData) : TokenIntrospection
    {
        return $this->introspectTokenOrFail()->execute(data: $introspectTokenData);
    }

    private function introspectTokenOrFail() : IntrospectToken
    {
        return $this->introspectToken ?? throw ExternalIdentityCapabilityUnavailable::oauth(operation: 'introspect_token');
    }
}
