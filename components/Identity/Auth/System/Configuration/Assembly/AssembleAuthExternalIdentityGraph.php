<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration\Assembly;

use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\DeterministicRiskEngine;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Diagnostics;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Explainability\AuthIssueExplainer;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\ProvisionableUserSourceInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\IdentitySync;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleOrchestrator;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Provisioning\Provisioning;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\DeprovisionUser\DeprovisionUser;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\ReactivateUser\ReactivateUser;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\SuspendUser\SuspendUser;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\SCIM;
use Avax\Components\Identity\Auth\System\Configuration\Readiness\AuthCapabilityReadiness;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Auth\System\Foundation\Exceptions\ConfigurationException;
use Avax\Components\Identity\Auth\System\Foundation\IdGeneratorInterface;
use Avax\Components\Identity\Auth\System\Foundation\IdGenerator;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentity;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\AuthorizationCodeStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClientRegistryInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\OAuth;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ApproveClientRegistration\ApproveClientRegistration;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\AuthorizeCode\AuthorizeCode;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\DisableClient\DisableClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeAuthorizationCode\ExchangeAuthorizationCode;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeClientCredentials\ExchangeClientCredentials;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeRefreshToken\ExchangeRefreshToken;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\IntrospectToken\IntrospectToken;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ReadClients\ReadClients;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ReadWorkloadIdentities\ReadWorkloadIdentities;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\RegisterClient\RegisterClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\RevokeToken\RevokeToken;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\RotateClientSecret\RotateClientSecret;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\UpdateClient\UpdateClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\OpenIDConnect;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcProviderInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcRequestObjectStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\BackChannelLogout\BackChannelLogout;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\FrontChannelLogout\FrontChannelLogout;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\JarmResponse\BuildJarmResponse;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\Logout\Logout as OidcLogout;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\PushAuthorizationRequest\PushAuthorizationRequest;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\ReadJsonWebKeySet\ReadOidcJsonWebKeySet;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\ReadProviderMetadata\ReadOidcProviderMetadata;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\ReadUserInfo\ReadOidcUserInfo;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\ValidateRequestObject\ValidateRequestObject;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederatedIdentityLinkStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnectionStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationHealthCheckInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationMetadataRuntimeInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationRuntimeInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\GroupRoleMappingValidator;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\CheckHealth\CheckFederationConnectionHealth;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\CompleteFederatedLogin\CompleteFederatedLogin;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\DiscoverConnection\DiscoverFederationConnection;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\EvaluateBreakGlass\EvaluateFederationBreakGlassBypass;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\ReadConnections\ReadFederationConnections;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\RegisterConnection\RegisterFederationConnection;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\StartFederatedLogin\StartFederatedLogin;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\SyncMetadata\SyncFederationMetadata;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\VerifyDomain\VerifyFederationDomain;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\SingleSignOn;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\AdminElevationStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\RequireAdminElevation\RequireAdminElevation;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;

/**
 * Assembles the OAuth/OIDC/Federation/provisioning/diagnostics object graph.
 *
 * Responsibility: Constructs external identity capabilities (OAuth, OpenID Connect,
 * Federation/S SO, Provisioning, IdentitySync, Diagnostics) from resolved dependencies
 * and the already-built identity graph. All objects are assembled at boot time for
 * validation and future reachability when the Auth public surface grows.
 *
 * This class owns no public DSL behavior and performs no runtime execution.
 */
final class AssembleAuthExternalIdentityGraph
{
    public function __construct(
        // Phase 4-only dependencies (required first)
        private OAuthClientRegistryInterface $oauthClientRegistry,
        private AuthorizationCodeStoreInterface $authorizationCodeStore,
        private FederationConnectionStoreInterface $federationConnectionStore,
        private FederatedIdentityLinkStoreInterface $federatedIdentityLinkStore,

        // Shared dependencies (Phase 1-3, required)
        private UserSourceInterface $userSource,
        private IdentityInterface $identity,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        private PasswordHasher $passwordHasher,
        private IdGeneratorInterface $idGenerator,
        private SessionRegistryInterface $sessionRegistry,
        private RefreshTokenStoreInterface $refreshTokenStore,
        private DeterministicRiskEngine $deterministicRiskEngine,

        // From Phase 3 unpacked graph (required)
        private CurrentAuthentication $currentAuthentication,
        private ProjectAuthenticatedUser $projectAuthenticatedUser,
        private AuthCapabilityReadiness $authCapabilityReadiness,
        private SCIM $scim,
        private RequireAdminElevation $requireAdminElevation,

        // Optional parameters (nullable, must come last)
        private OidcProviderInterface|null $oidcProvider = null,
        private OidcRequestObjectStoreInterface|null $oidcRequestObjectStore = null,
        private FederationRuntimeInterface|null $federationRuntime = null,
        private FederationMetadataRuntimeInterface|null $federationMetadataRuntime = null,
        private FederationHealthCheckInterface|null $federationHealthCheck = null,
        private ProvisionableUserSourceInterface|null $provisionableUserSource = null,
        private LifecycleOrchestrator|null $lifecycle = null,
        private AdminElevationStoreInterface|null $adminElevationStore = null,
    ) {
    }

    /**
     * Assemble the external identity object graph.
     *
     * Constructs OAuth, OpenID Connect, Federation, Provisioning, IdentitySync,
     * and Diagnostics capabilities. Objects are assembled for boot-time validation
     * and future reachability. Currently not exposed through the Auth public surface.
     */
    public function assemble(): void
    {
        // OAuth client operations
        $registerClient            = new RegisterClient(auditLog: $this->auditLog, clock: $this->clock, oAuthClientRegistry: $this->oauthClientRegistry);
        $approveClientRegistration = new ApproveClientRegistration(auditLog: $this->auditLog, clock: $this->clock, oAuthClientRegistry: $this->oauthClientRegistry);
        $updateClient              = new UpdateClient(auditLog: $this->auditLog, clock: $this->clock, oAuthClientRegistry: $this->oauthClientRegistry);
        $disableClient             = new DisableClient(auditLog: $this->auditLog, clock: $this->clock, oAuthClientRegistry: $this->oauthClientRegistry);
        $rotateClientSecret        = new RotateClientSecret(auditLog: $this->auditLog, clock: $this->clock, oAuthClientRegistry: $this->oauthClientRegistry);
        $readClients               = new ReadClients(oAuthClientRegistry: $this->oauthClientRegistry);
        $readWorkloadIdentities    = new ReadWorkloadIdentities(oAuthClientRegistry: $this->oauthClientRegistry);

        // OIDC operations
        $readOidcProviderMetadata  = $this->oidcProvider instanceof OidcProviderInterface ? new ReadOidcProviderMetadata(oidcProvider: $this->oidcProvider) : null;
        $readOidcJsonWebKeySet     = $this->oidcProvider instanceof OidcProviderInterface ? new ReadOidcJsonWebKeySet(oidcProvider: $this->oidcProvider) : null;
        $jwtIdentity               = $this->identity->jwtIdentity();
        $readOidcUserInfo          = $jwtIdentity instanceof JwtIdentityInterface && $this->oidcProvider instanceof OidcProviderInterface ? new ReadOidcUserInfo(jwtIdentity: $jwtIdentity, oidcProvider: $this->oidcProvider) : null;
        $oidcRequestObjectStore    = $this->oidcProvider instanceof OidcProviderInterface ? ($this->oidcRequestObjectStore ?? throw ConfigurationException::missingDependency('OidcRequestObjectStore', 'withOidcRequestObjectStore() or AuthServiceProvider')) : null;
        $pushOidcAuthorizationRequest = $oidcRequestObjectStore instanceof OidcRequestObjectStoreInterface ? new PushAuthorizationRequest(oidcRequestObjectStore: $oidcRequestObjectStore, auditLog: $this->auditLog, clock: $this->clock, oAuthClientRegistry: $this->oauthClientRegistry, oidcProvider: $this->oidcProvider) : null;
        $validateRequestObject     = $oidcRequestObjectStore instanceof OidcRequestObjectStoreInterface ? new ValidateRequestObject(oidcRequestObjectStore: $oidcRequestObjectStore, oAuthClientRegistry: $this->oauthClientRegistry) : null;
        $oidcLogout                = $this->oidcProvider instanceof OidcProviderInterface ? new OidcLogout(
            frontChannelLogout: new FrontChannelLogout(currentAuthentication: $this->currentAuthentication, identity: $this->identity, auditLog: $this->auditLog, clock: $this->clock, sessionRegistry: $this->sessionRegistry, refreshTokenStore: $this->refreshTokenStore, oidcProvider: $this->oidcProvider, oAuthClientRegistry: $this->oauthClientRegistry),
            backChannelLogout: new BackChannelLogout(currentAuthentication: $this->currentAuthentication, identity: $this->identity, auditLog: $this->auditLog, clock: $this->clock, sessionRegistry: $this->sessionRegistry, refreshTokenStore: $this->refreshTokenStore, oidcProvider: $this->oidcProvider, oAuthClientRegistry: $this->oauthClientRegistry),
        ) : null;
        $buildOidcJarmResponse     = $this->oidcProvider instanceof OidcProviderInterface ? new BuildJarmResponse(oidcProvider: $this->oidcProvider, clock: $this->clock) : null;
        $authorizeCode             = new AuthorizeCode(currentAuthentication: $this->currentAuthentication, userSource: $this->userSource, oAuthClientRegistry: $this->oauthClientRegistry, authorizationCodeStore: $this->authorizationCodeStore, auditLog: $this->auditLog, clock: $this->clock, oidcProvider: $this->oidcProvider, validateRequestObject: $validateRequestObject);

        // OAuth capability
        $oauth = new OAuth(
            registerClient: $this->authCapabilityReadiness->oauth() ? $registerClient : null,
            approveClientRegistration: $this->authCapabilityReadiness->oauth() ? $approveClientRegistration : null,
            updateClient: $this->authCapabilityReadiness->oauth() ? $updateClient : null,
            disableClient: $this->authCapabilityReadiness->oauth() ? $disableClient : null,
            rotateClientSecret: $this->authCapabilityReadiness->oauth() ? $rotateClientSecret : null,
            readClients: $this->authCapabilityReadiness->oauth() ? $readClients : null,
            readWorkloadIdentities: $this->authCapabilityReadiness->oauth() ? $readWorkloadIdentities : null,
            authorizeCode: $this->authCapabilityReadiness->oauth() ? $authorizeCode : null,
            exchangeAuthorizationCode: $this->authCapabilityReadiness->oauth() ? new ExchangeAuthorizationCode(oAuthClientRegistry: $this->oauthClientRegistry, authorizationCodeStore: $this->authorizationCodeStore, userSource: $this->userSource, jwtIdentity: $jwtIdentity ?? throw ConfigurationException::missingCapabilityDependency(capability: 'oauth', requirement: 'jwt_identity', buildPath: 'AuthBuilder::ready()', option: 'withIdentityBackends(jwtIdentity: ...) or withIdentity(new Identity(jwtIdentity: ...))', cause: 'OAuth capability assembly was attempted.'), refreshTokenStore: $this->refreshTokenStore, auditLog: $this->auditLog, clock: $this->clock, currentAuthentication: $this->currentAuthentication, oidcProvider: $this->oidcProvider) : null,
            exchangeClientCredentials: $this->authCapabilityReadiness->oauth() ? new ExchangeClientCredentials(oAuthClientRegistry: $this->oauthClientRegistry, jwtIdentity: $jwtIdentity ?? throw ConfigurationException::missingCapabilityDependency(capability: 'oauth', requirement: 'jwt_identity', buildPath: 'AuthBuilder::ready()', option: 'withIdentityBackends(jwtIdentity: ...) or withIdentity(new Identity(jwtIdentity: ...))', cause: 'OAuth capability assembly was attempted.'), auditLog: $this->auditLog, clock: $this->clock) : null,
            exchangeRefreshToken: $this->authCapabilityReadiness->oauth() ? new ExchangeRefreshToken(oAuthClientRegistry: $this->oauthClientRegistry, refreshTokenStore: $this->refreshTokenStore, userSource: $this->userSource, jwtIdentity: $jwtIdentity ?? throw ConfigurationException::missingCapabilityDependency(capability: 'oauth', requirement: 'jwt_identity', buildPath: 'AuthBuilder::ready()', option: 'withIdentityBackends(jwtIdentity: ...) or withIdentity(new Identity(jwtIdentity: ...))', cause: 'OAuth capability assembly was attempted.'), auditLog: $this->auditLog, clock: $this->clock, deterministicRiskEngine: $this->deterministicRiskEngine) : null,
            revokeToken: $this->authCapabilityReadiness->oauth() ? new RevokeToken(oAuthClientRegistry: $this->oauthClientRegistry, refreshTokenStore: $this->refreshTokenStore, jwtIdentity: $jwtIdentity ?? throw ConfigurationException::missingCapabilityDependency(capability: 'oauth', requirement: 'jwt_identity', buildPath: 'AuthBuilder::ready()', option: 'withIdentityBackends(jwtIdentity: ...) or withIdentity(new Identity(jwtIdentity: ...))', cause: 'OAuth capability assembly was attempted.'), auditLog: $this->auditLog, clock: $this->clock) : null,
            introspectToken: $this->authCapabilityReadiness->oauth() ? new IntrospectToken(oAuthClientRegistry: $this->oauthClientRegistry, jwtIdentity: $jwtIdentity ?? throw ConfigurationException::missingCapabilityDependency(capability: 'oauth', requirement: 'jwt_identity', buildPath: 'AuthBuilder::ready()', option: 'withIdentityBackends(jwtIdentity: ...) or withIdentity(new Identity(jwtIdentity: ...))', cause: 'OAuth capability assembly was attempted.'), auditLog: $this->auditLog, clock: $this->clock) : null,
        );

        // OpenID Connect capability
        $openIDConnect = new OpenIDConnect(
            pushAuthorizationRequest: $this->authCapabilityReadiness->oauth() ? $pushOidcAuthorizationRequest : null,
            logout: $this->authCapabilityReadiness->oauth() ? $oidcLogout : null,
            buildJarmResponse: $this->authCapabilityReadiness->oauth() ? $buildOidcJarmResponse : null,
            readOidcProviderMetadata: $this->authCapabilityReadiness->oauth() ? $readOidcProviderMetadata : null,
            readOidcJsonWebKeySet: $this->authCapabilityReadiness->oauth() ? $readOidcJsonWebKeySet : null,
            readOidcUserInfo: $this->authCapabilityReadiness->oauth() ? $readOidcUserInfo : null,
        );

        // Federation / SingleSignOn capability
        $groupRoleMappingValidator = new GroupRoleMappingValidator();

        $singleSignOn = new SingleSignOn(
            startFederatedLogin: $this->authCapabilityReadiness->federation() ? new StartFederatedLogin(auditLog: $this->auditLog, clock: $this->clock, federationConnectionStore: $this->federationConnectionStore, federationRuntime: $this->federationRuntime ?? throw ConfigurationException::missingCapabilityDependency(capability: 'federation', requirement: 'runtime', buildPath: 'AuthBuilder::ready()', option: 'withFederationRuntime()', cause: 'Federation capability assembly was attempted.')) : null,
            completeFederatedLogin: $this->authCapabilityReadiness->federation() ? new CompleteFederatedLogin(userSource: $this->userSource, identity: $this->identity, projectAuthenticatedUser: $this->projectAuthenticatedUser, currentAuthentication: $this->currentAuthentication, passwordHasher: $this->passwordHasher, idGenerator: $this->idGenerator, auditLog: $this->auditLog, clock: $this->clock, federationConnectionStore: $this->federationConnectionStore, federationRuntime: $this->federationRuntime ?? throw ConfigurationException::missingCapabilityDependency(capability: 'federation', requirement: 'runtime', buildPath: 'AuthBuilder::ready()', option: 'withFederationRuntime()', cause: 'Federation capability assembly was attempted.'), federatedIdentityLinkStore: $this->federatedIdentityLinkStore, deterministicRiskEngine: $this->deterministicRiskEngine, lifecycleOrchestrator: $this->lifecycle) : null,
            registerFederationConnection: $this->authCapabilityReadiness->federation() ? new RegisterFederationConnection(groupRoleMappingValidator: $groupRoleMappingValidator, auditLog: $this->auditLog, clock: $this->clock, federationConnectionStore: $this->federationConnectionStore) : null,
            readFederationConnections: $this->authCapabilityReadiness->federation() ? new ReadFederationConnections(federationConnectionStore: $this->federationConnectionStore) : null,
            verifyFederationDomain: $this->authCapabilityReadiness->federation() ? new VerifyFederationDomain(auditLog: $this->auditLog, clock: $this->clock, federationConnectionStore: $this->federationConnectionStore) : null,
            syncFederationMetadata: $this->authCapabilityReadiness->federation() && $this->federationMetadataRuntime instanceof FederationMetadataRuntimeInterface ? new SyncFederationMetadata(auditLog: $this->auditLog, clock: $this->clock, federationConnectionStore: $this->federationConnectionStore, federationMetadataRuntime: $this->federationMetadataRuntime) : null,
            checkFederationConnectionHealth: $this->authCapabilityReadiness->federation() && $this->federationHealthCheck instanceof FederationHealthCheckInterface ? new CheckFederationConnectionHealth(auditLog: $this->auditLog, clock: $this->clock, federationConnectionStore: $this->federationConnectionStore, federationHealthCheck: $this->federationHealthCheck) : null,
            evaluateFederationBreakGlassBypass: $this->authCapabilityReadiness->federation() ? new EvaluateFederationBreakGlassBypass(auditLog: $this->auditLog, clock: $this->clock, federationConnectionStore: $this->federationConnectionStore) : null,
            discoverFederationConnection: $this->authCapabilityReadiness->federation() ? new DiscoverFederationConnection(federationConnectionStore: $this->federationConnectionStore) : null,
        );

        $externalIdentity = new ExternalIdentity(oauth: $oauth, openIDConnect: $openIDConnect, singleSignOn: $singleSignOn);

        // Provisioning
        $provisioning = new Provisioning(
            suspendUser: $this->provisionableUserSource instanceof ProvisionableUserSourceInterface ? new SuspendUser(provisionableUserSource: $this->provisionableUserSource, requireAdminElevation: $this->requireAdminElevation, auditLog: $this->auditLog, clock: $this->clock, lifecycleOrchestrator: $this->lifecycle) : null,
            reactivateUser: $this->provisionableUserSource instanceof ProvisionableUserSourceInterface ? new ReactivateUser(provisionableUserSource: $this->provisionableUserSource, requireAdminElevation: $this->requireAdminElevation, auditLog: $this->auditLog, clock: $this->clock, lifecycleOrchestrator: $this->lifecycle) : null,
            deprovisionUser: $this->provisionableUserSource instanceof ProvisionableUserSourceInterface ? new DeprovisionUser(provisionableUserSource: $this->provisionableUserSource, requireAdminElevation: $this->requireAdminElevation, auditLog: $this->auditLog, clock: $this->clock, sessionRegistry: $this->sessionRegistry, refreshTokenStore: $this->refreshTokenStore, adminElevationStore: $this->adminElevationStore, lifecycleOrchestrator: $this->lifecycle) : null,
        );

        $identitySync = new IdentitySync(scim: $this->scim, provisioning: $provisioning);
        new Diagnostics(authIssueExplainer: new AuthIssueExplainer());
    }
}
