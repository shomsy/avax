<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn;

use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityCapabilityUnavailable;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnection;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnectionHealth;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\StartedFederatedLogin;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\CheckHealth\CheckFederationConnectionHealth;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\CompleteFederatedLogin\CompleteFederatedLogin;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\CompleteFederatedLogin\CompleteFederatedLoginData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\DiscoverConnection\DiscoverFederationConnection;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\EvaluateBreakGlass\EvaluateFederationBreakGlassBypass;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\ReadConnections\ReadFederationConnections;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\RegisterConnection\RegisterFederationConnection;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\RegisterConnection\RegisterFederationConnectionData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\StartFederatedLogin\StartFederatedLogin;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\StartFederatedLogin\StartFederatedLoginData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\SyncMetadata\SyncFederationMetadata;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\VerifyDomain\VerifyFederationDomain;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\VerifyDomain\VerifyFederationDomainData;
use JsonException;
use Random\RandomException;
use SensitiveParameter;

final readonly class SingleSignOn
{
    public function __construct(
        private RegisterFederationConnection|null    $registerConnection,
        private ReadFederationConnections|null       $readConnections,
        private VerifyFederationDomain|null          $verifyDomain,
        private SyncFederationMetadata|null          $syncMetadata,
        private CheckFederationConnectionHealth|null $checkConnectionHealth,
        private EvaluateFederationBreakGlassBypass|null $evaluateBreakGlassBypass,
        private DiscoverFederationConnection|null    $discoverConnection,
        private StartFederatedLogin|null             $startFederatedLogin,
        private CompleteFederatedLogin|null          $completeFederatedLogin,
    ) {}

    public function isConfigured() : bool
    {
        return $this->registerConnection !== null
            && $this->readConnections !== null
            && $this->verifyDomain !== null
            && $this->syncMetadata !== null
            && $this->checkConnectionHealth !== null
            && $this->evaluateBreakGlassBypass !== null
            && $this->discoverConnection !== null
            && $this->startFederatedLogin !== null
            && $this->completeFederatedLogin !== null;
    }

    /**
     * @throws RandomException
     */
    public function registerConnection(RegisterFederationConnectionData $data) : FederationConnection
    {
        return $this->registerConnectionOrFail()->execute(data: $data);
    }

    private function registerConnectionOrFail() : RegisterFederationConnection
    {
        return $this->registerConnection ?? throw ExternalIdentityCapabilityUnavailable::sso(operation: 'register_connection');
    }

    /**
     * @return list<FederationConnection>
     */
    public function readConnections() : array
    {
        return $this->readConnectionsOrFail()->execute();
    }

    private function readConnectionsOrFail() : ReadFederationConnections
    {
        return $this->readConnections ?? throw ExternalIdentityCapabilityUnavailable::sso(operation: 'read_connections');
    }

    public function verifyDomain(VerifyFederationDomainData $data) : FederationConnection
    {
        return $this->verifyDomainOrFail()->execute(data: $data);
    }

    private function verifyDomainOrFail() : VerifyFederationDomain
    {
        return $this->verifyDomain ?? throw ExternalIdentityCapabilityUnavailable::sso(operation: 'verify_domain');
    }

    /**
     * @throws JsonException
     */
    public function syncMetadata(string $connectionId) : FederationConnection
    {
        return $this->syncMetadataOrFail()->execute(connectionId: $connectionId);
    }

    private function syncMetadataOrFail() : SyncFederationMetadata
    {
        return $this->syncMetadata ?? throw ExternalIdentityCapabilityUnavailable::sso(operation: 'sync_metadata');
    }

    public function checkConnectionHealth(string $connectionId) : FederationConnectionHealth
    {
        return $this->checkConnectionHealthOrFail()->execute(connectionId: $connectionId);
    }

    private function checkConnectionHealthOrFail() : CheckFederationConnectionHealth
    {
        return $this->checkConnectionHealth ?? throw ExternalIdentityCapabilityUnavailable::sso(operation: 'check_connection_health');
    }

    public function evaluateBreakGlassBypass(string $connectionId) : bool
    {
        return $this->evaluateBreakGlassBypassOrFail()->execute(connectionId: $connectionId);
    }

    private function evaluateBreakGlassBypassOrFail() : EvaluateFederationBreakGlassBypass
    {
        return $this->evaluateBreakGlassBypass ?? throw ExternalIdentityCapabilityUnavailable::sso(operation: 'evaluate_break_glass_bypass');
    }

    public function discoverConnection(#[SensitiveParameter] string $email) : FederationConnection|null
    {
        return $this->discoverConnectionOrFail()->execute(email: $email);
    }

    private function discoverConnectionOrFail() : DiscoverFederationConnection
    {
        return $this->discoverConnection ?? throw ExternalIdentityCapabilityUnavailable::sso(operation: 'discover_connection');
    }

    public function startFederatedLogin(StartFederatedLoginData $data) : StartedFederatedLogin
    {
        return $this->startFederatedLoginOrFail()->execute(data: $data);
    }

    private function startFederatedLoginOrFail() : StartFederatedLogin
    {
        return $this->startFederatedLogin ?? throw ExternalIdentityCapabilityUnavailable::sso(operation: 'start_federated_login');
    }

    /**
     * @throws RandomException
     */
    public function completeFederatedLogin(CompleteFederatedLoginData $data) : AuthenticationResult
    {
        return $this->completeFederatedLoginOrFail()->execute(data: $data);
    }

    private function completeFederatedLoginOrFail() : CompleteFederatedLogin
    {
        return $this->completeFederatedLogin ?? throw ExternalIdentityCapabilityUnavailable::sso(operation: 'complete_federated_login');
    }
}
