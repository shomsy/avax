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
        private ?RegisterFederationConnection $registerFederationConnection,
        private ?ReadFederationConnections $readFederationConnections,
        private ?VerifyFederationDomain $verifyFederationDomain,
        private ?SyncFederationMetadata $syncFederationMetadata,
        private ?CheckFederationConnectionHealth $checkFederationConnectionHealth,
        private ?EvaluateFederationBreakGlassBypass $evaluateFederationBreakGlassBypass,
        private ?DiscoverFederationConnection $discoverFederationConnection,
        private ?StartFederatedLogin $startFederatedLogin,
        private ?CompleteFederatedLogin $completeFederatedLogin,
    ) {
    }

    public function isConfigured(): bool
    {
        return $this->registerFederationConnection instanceof RegisterFederationConnection
            && $this->readFederationConnections instanceof ReadFederationConnections
            && $this->verifyFederationDomain instanceof VerifyFederationDomain
            && $this->syncFederationMetadata instanceof SyncFederationMetadata
            && $this->checkFederationConnectionHealth instanceof CheckFederationConnectionHealth
            && $this->evaluateFederationBreakGlassBypass instanceof EvaluateFederationBreakGlassBypass
            && $this->discoverFederationConnection instanceof DiscoverFederationConnection
            && $this->startFederatedLogin instanceof StartFederatedLogin
            && $this->completeFederatedLogin instanceof CompleteFederatedLogin;
    }

    /**
     * @throws RandomException
     */
    public function registerConnection(RegisterFederationConnectionData $registerFederationConnectionData): FederationConnection
    {
        return $this->registerConnectionOrFail()->execute(data: $registerFederationConnectionData);
    }

    private function registerConnectionOrFail(): RegisterFederationConnection
    {
        return $this->registerFederationConnection ?? throw ExternalIdentityCapabilityUnavailable::sso(operation: 'register_connection');
    }

    /**
     * @return list<FederationConnection>
     */
    public function readConnections(): array
    {
        return $this->readConnectionsOrFail()->execute();
    }

    private function readConnectionsOrFail(): ReadFederationConnections
    {
        return $this->readFederationConnections ?? throw ExternalIdentityCapabilityUnavailable::sso(operation: 'read_connections');
    }

    public function verifyDomain(VerifyFederationDomainData $verifyFederationDomainData): FederationConnection
    {
        return $this->verifyDomainOrFail()->execute(data: $verifyFederationDomainData);
    }

    private function verifyDomainOrFail(): VerifyFederationDomain
    {
        return $this->verifyFederationDomain ?? throw ExternalIdentityCapabilityUnavailable::sso(operation: 'verify_domain');
    }

    /**
     * @throws JsonException
     */
    public function syncMetadata(string $connectionId): FederationConnection
    {
        return $this->syncMetadataOrFail()->execute(connectionId: $connectionId);
    }

    private function syncMetadataOrFail(): SyncFederationMetadata
    {
        return $this->syncFederationMetadata ?? throw ExternalIdentityCapabilityUnavailable::sso(operation: 'sync_metadata');
    }

    public function checkConnectionHealth(string $connectionId): FederationConnectionHealth
    {
        return $this->checkConnectionHealthOrFail()->execute(connectionId: $connectionId);
    }

    private function checkConnectionHealthOrFail(): CheckFederationConnectionHealth
    {
        return $this->checkFederationConnectionHealth ?? throw ExternalIdentityCapabilityUnavailable::sso(operation: 'check_connection_health');
    }

    public function evaluateBreakGlassBypass(string $connectionId): bool
    {
        return $this->evaluateBreakGlassBypassOrFail()->execute(connectionId: $connectionId);
    }

    private function evaluateBreakGlassBypassOrFail(): EvaluateFederationBreakGlassBypass
    {
        return $this->evaluateFederationBreakGlassBypass ?? throw ExternalIdentityCapabilityUnavailable::sso(operation: 'evaluate_break_glass_bypass');
    }

    public function discoverConnection(#[SensitiveParameter] string $email): ?FederationConnection
    {
        return $this->discoverConnectionOrFail()->execute(email: $email);
    }

    private function discoverConnectionOrFail(): DiscoverFederationConnection
    {
        return $this->discoverFederationConnection ?? throw ExternalIdentityCapabilityUnavailable::sso(operation: 'discover_connection');
    }

    public function startFederatedLogin(StartFederatedLoginData $startFederatedLoginData): StartedFederatedLogin
    {
        return $this->startFederatedLoginOrFail()->execute(data: $startFederatedLoginData);
    }

    private function startFederatedLoginOrFail(): StartFederatedLogin
    {
        return $this->startFederatedLogin ?? throw ExternalIdentityCapabilityUnavailable::sso(operation: 'start_federated_login');
    }

    /**
     * @throws RandomException
     */
    public function completeFederatedLogin(CompleteFederatedLoginData $completeFederatedLoginData): AuthenticationResult
    {
        return $this->completeFederatedLoginOrFail()->execute(data: $completeFederatedLoginData);
    }

    private function completeFederatedLoginOrFail(): CompleteFederatedLogin
    {
        return $this->completeFederatedLogin ?? throw ExternalIdentityCapabilityUnavailable::sso(operation: 'complete_federated_login');
    }
}
