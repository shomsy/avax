<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OAuth;

use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ApproveClientRegistration\ApproveClientRegistration;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ApproveClientRegistration\ApproveClientRegistrationData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\AuthorizeCode\AuthorizeCode;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\AuthorizeCode\AuthorizeCodeData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\DisableClient\DisableClient;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeAuthorizationCode\ExchangeAuthorizationCode;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeAuthorizationCode\ExchangeAuthorizationCodeData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeClientCredentials\ExchangeClientCredentials;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeClientCredentials\ExchangeClientCredentialsData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeRefreshToken\ExchangeRefreshToken;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeRefreshToken\ExchangeRefreshTokenData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\IntrospectToken\IntrospectToken;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\IntrospectToken\IntrospectTokenData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\IntrospectToken\TokenIntrospection;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\OAuthTokenGrant;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ReadClients\ReadClients;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ReadWorkloadIdentities\ReadWorkloadIdentities;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ReadWorkloadIdentities\WorkloadIdentityProfile;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RegisterClient\RegisterClient;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RegisterClient\RegisterClientData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RevokeToken\RevokeToken;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RevokeToken\RevokeTokenData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RotateClientSecret\RotateClientSecret;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\UpdateClient\UpdateClient;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\UpdateClient\UpdateClientData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\IssuedAuthorizationCode;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClient;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\RegisteredOAuthClient;
use DateMalformedStringException;
use RuntimeException;
use SensitiveParameter;

final readonly class OAuth
{
    public function __construct(
        private RegisterClient|null                                  $registerClient,
        private ApproveClientRegistration|null                       $approveClientRegistration,
        private UpdateClient|null                                    $updateClient,
        private DisableClient|null                                   $disableClient,
        #[SensitiveParameter] private RotateClientSecret|null        $rotateClientSecret,
        private ReadClients|null                                     $readClients,
        private ReadWorkloadIdentities|null                          $readWorkloadIdentities,
        #[SensitiveParameter] private AuthorizeCode|null             $authorizeCode,
        #[SensitiveParameter] private ExchangeAuthorizationCode|null $exchangeAuthorizationCode,
        #[SensitiveParameter] private ExchangeClientCredentials|null $exchangeClientCredentials,
        #[SensitiveParameter] private ExchangeRefreshToken|null      $exchangeRefreshToken,
        #[SensitiveParameter] private RevokeToken|null               $revokeToken,
        #[SensitiveParameter] private IntrospectToken|null           $introspectToken
    ) {}

    public function registerClient(RegisterClientData $data) : RegisteredOAuthClient
    {
        return $this->registerClientOrFail()->execute(data: $data);
    }

    private function registerClientOrFail() : RegisterClient
    {
        return $this->registerClient ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    public function approveClientRegistration(ApproveClientRegistrationData $data) : OAuthClient
    {
        return $this->approveClientRegistrationOrFail()->execute(data: $data);
    }

    private function approveClientRegistrationOrFail() : ApproveClientRegistration
    {
        return $this->approveClientRegistration ?? throw new RuntimeException(message: 'OAuth client approval is not configured.');
    }

    public function updateClient(UpdateClientData $data) : OAuthClient
    {
        return $this->updateClientOrFail()->execute(data: $data);
    }

    private function updateClientOrFail() : UpdateClient
    {
        return $this->updateClient ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    public function disableClient(string $clientId) : OAuthClient
    {
        return $this->disableClientOrFail()->execute(clientId: $clientId);
    }

    private function disableClientOrFail() : DisableClient
    {
        return $this->disableClient ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    public function rotateClientSecret(string $clientId) : RegisteredOAuthClient
    {
        return $this->rotateClientSecretOrFail()->execute(clientId: $clientId);
    }

    private function rotateClientSecretOrFail() : RotateClientSecret
    {
        return $this->rotateClientSecret ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
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
        return $this->readClients ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
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
        return $this->readWorkloadIdentities ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    /**
     * @throws DateMalformedStringException
     */
    public function authorizeCode(AuthorizeCodeData $data) : IssuedAuthorizationCode
    {
        return $this->authorizeCodeOrFail()->execute(data: $data);
    }

    private function authorizeCodeOrFail() : AuthorizeCode
    {
        return $this->authorizeCode ?? throw new RuntimeException(message: 'OAuth authorization code flow is not configured.');
    }

    /**
     * @throws DateMalformedStringException
     */
    public function exchangeAuthorizationCode(ExchangeAuthorizationCodeData $data) : OAuthTokenGrant
    {
        return $this->exchangeAuthorizationCodeOrFail()->execute(data: $data);
    }

    private function exchangeAuthorizationCodeOrFail() : ExchangeAuthorizationCode
    {
        return $this->exchangeAuthorizationCode ?? throw new RuntimeException(message: 'OAuth token exchange is not configured.');
    }

    public function exchangeClientCredentials(ExchangeClientCredentialsData $data) : OAuthTokenGrant
    {
        return $this->exchangeClientCredentialsOrFail()->execute(data: $data);
    }

    private function exchangeClientCredentialsOrFail() : ExchangeClientCredentials
    {
        return $this->exchangeClientCredentials ?? throw new RuntimeException(message: 'OAuth client credentials flow is not configured.');
    }

    /**
     * @throws DateMalformedStringException
     */
    public function exchangeRefreshToken(ExchangeRefreshTokenData $data) : OAuthTokenGrant
    {
        return $this->exchangeRefreshTokenOrFail()->execute(data: $data);
    }

    private function exchangeRefreshTokenOrFail() : ExchangeRefreshToken
    {
        return $this->exchangeRefreshToken ?? throw new RuntimeException(message: 'OAuth refresh flow is not configured.');
    }

    public function revokeToken(RevokeTokenData $data) : void
    {
        $this->revokeTokenOrFail()->execute(data: $data);
    }

    private function revokeTokenOrFail() : RevokeToken
    {
        return $this->revokeToken ?? throw new RuntimeException(message: 'OAuth revoke flow is not configured.');
    }

    public function introspectToken(IntrospectTokenData $data) : TokenIntrospection
    {
        return $this->introspectTokenOrFail()->execute(data: $data);
    }

    private function introspectTokenOrFail() : IntrospectToken
    {
        return $this->introspectToken ?? throw new RuntimeException(message: 'OAuth introspection is not configured.');
    }
}
