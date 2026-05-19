<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration\Assembly;

use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\AttemptThrottle;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Runtime\AssessCurrentRisk\AssessCurrentRisk;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Runtime\ReadRiskSignals\ReadRiskSignals;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\DeterministicRiskEngine;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners\Account;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners\Authentication;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners\Recovery;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners\Verification;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\LogoutAllSessions\LogoutAllSessions;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\ReadActiveSessions\ReadActiveSessions;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\RevokeSession\RevokeSession;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Sessions;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\ProvisionableUserSourceInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleOrchestrator;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectoryStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimProvisionedIdentityStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\RunScimBulk;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\DeleteUser\DeleteScimUser;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUser;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadGroups\ReadScimGroups;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadUsers\ReadScimUsers;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\SyncGroups\SyncScimGroups;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\SCIM;
use Avax\Components\Identity\Auth\System\Configuration\Readiness\AuthCapabilityReadiness;
use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\BeginEmailChange;
use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\ConfirmEmailChange;
use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\EmailChangeStoreInterface;
use Avax\Components\Identity\Auth\System\Flows\ChangePassword\ChangePassword;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\Login\FindUserByCredentials;
use Avax\Components\Identity\Auth\System\Flows\Login\Login;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\LoginRateLimit;
use Avax\Components\Identity\Auth\System\Flows\Login\StartAuthenticatedSession;
use Avax\Components\Identity\Auth\System\Flows\Login\VerifyPassword;
use Avax\Components\Identity\Auth\System\Flows\Logout\ClearAuthenticatedIdentity;
use Avax\Components\Identity\Auth\System\Flows\Logout\Logout;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\BeginPasswordReset;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\PasswordResetStoreInterface;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\ResetPassword;
use Avax\Components\Identity\Auth\System\Flows\Register\CreateRegisteredUser;
use Avax\Components\Identity\Auth\System\Flows\Register\HashRegisteredPassword;
use Avax\Components\Identity\Auth\System\Flows\Register\Register;
use Avax\Components\Identity\Auth\System\Flows\Register\ValidateRegistrationData;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\BeginEmailVerification;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\EmailVerificationStateStoreInterface;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\EmailVerificationStoreInterface;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\VerifyEmail;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Auth\System\Foundation\Exceptions\ConfigurationException;
use Avax\Components\Identity\Auth\System\Foundation\IdGeneratorInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Mfa;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Backup\GenerateBackupCodes;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Backup\RegenerateBackupCodes;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Backup\VerifyBackupCode;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Disable\DisableMfa;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enroll\CancelMfaEnrollment;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enroll\ConfirmMfaEnrollment;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enroll\StartMfaEnrollment;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Limit\LimitMfaAttempts;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Recover\ConfirmMfaRecovery;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Recover\StartMfaRecovery;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Stores\MfaStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Totp\TotpInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\MfaChallengeStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\StartMfaChallenge;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\VerifyMfaChallenge;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Passkey;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyChallengeStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyCredentialStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyRuntimeInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\BeginAuthentication\BeginPasskeyAuthentication;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\BeginRegistration\BeginPasskeyRegistration;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\CompleteAuthentication\CompletePasskeyAuthentication;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\CompleteRegistration\CompletePasskeyRegistration;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\ListPasskeys\ListPasskeys;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\RenamePasskey\RenamePasskey;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\RevokePasskey\RevokePasskey;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnectionStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationRuntimeInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\GroupRoleMappingValidator;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\AdminElevationStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\AcceptInvite\AcceptTenantInvite;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\CreateTenant\CreateTenant;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\InviteMember\InviteTenantMember;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\ReadMembers\ReadTenantMembers;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\ReadTenants\ReadTenants;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\RemoveMember\RemoveTenantMember;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\SuspendMember\SuspendTenantMember;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\TransferOwnership\TransferTenantOwnership;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\ApplyChange\ApplyTenantSecurityChange;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\ApproveChange\ApproveTenantSecurityChange;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\BeginChange\BeginTenantSecurityChange;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\ReadChangeRequest\ReadTenantSecurityChangeRequest;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\ReadChangeRequests\ReadTenantSecurityChangeRequests;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\ReadConfiguration\ReadTenantSecurityConfiguration;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\RollbackChange\RollbackTenantSecurityChange;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\Security;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityChangeRequestStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityConfiguration;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityConfigurationStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Tenancy;
use Avax\Components\Identity\Tenancy\System\Capabilities\Tenants\Tenants;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantStoreInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Flow\RefreshAuthentication;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;

/**
 * Assembles the complete identity, tenancy, SCIM, and diagnostics object graphs.
 *
 * Capability: Produces Identity, Tenancy, SCIM, risk-assessment, and diagnostics
 * objects that form the internal auth system. The Auth facade exposes only the Identity root.
 *
 * This is the first extraction slice from AuthBuilder::ready(). It receives pre-built
 * core primitives (authentication state, MFA helpers, etc.) from the builder and assembles
 * the deep identity graph that was previously inlined.
 *
 * Subsequent slices will extract the external identity (OAuth/OIDC/Federation) graph.
 */
final class AssembleAuthIdentityGraph
{
    public function __construct(
        private UserSourceInterface $userSource,
        private IdentityInterface $identity,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        private PasswordHasher $passwordHasher,
        private IdGeneratorInterface $idGenerator,
        private SessionRegistryInterface $sessionRegistry,
        private RefreshTokenStoreInterface $refreshTokenStore,
        private MfaStoreInterface $mfaStore,
        private MfaChallengeStoreInterface $mfaChallengeStore,
        private TotpInterface $totp,
        private LimitMfaAttempts $limitMfaAttempts,
        private AttemptThrottle $passwordResetThrottle,
        private AttemptThrottle $mfaRecoveryThrottle,
        private AttemptThrottle $scimThrottle,
        private PasswordResetStoreInterface $passwordResetStore,
        private EmailVerificationStoreInterface $emailVerificationStore,
        private EmailVerificationStateStoreInterface $emailVerificationStateStore,
        private EmailChangeStoreInterface $emailChangeStore,
        private AdminElevationStoreInterface $adminElevationStore,
        private DeterministicRiskEngine $riskEngine,
        private PasskeyRuntimeInterface $passkeyRuntime,
        private PasskeyCredentialStoreInterface $passkeyCredentialStore,
        private PasskeyChallengeStoreInterface $passkeyChallengeStore,
        private LifecycleStoreInterface $lifecycleStore,
        private ScimDirectoryStoreInterface $scimDirectoryStore,
        private ScimProvisionedIdentityStoreInterface $scimProvisionedIdentityStore,
        private TenantStoreInterface $tenantStore,
        private TenantSecurityConfigurationStoreInterface $tenantSecurityConfigurationStore,
        private TenantSecurityChangeRequestStoreInterface $tenantSecurityChangeRequestStore,
        private FederationConnectionStoreInterface $federationConnectionStore,
        private FederationRuntimeInterface|null $federationRuntime = null,
        private LoginRateLimit|null $loginRateLimit = null,
        private string $mfaIssuer = 'Avax Auth',
        private string $passkeyRpId = 'localhost',
        private string $passkeyRpName = 'Avax Auth',
        // Phase 2 primitives built by AuthBuilder before delegation
        private CurrentAuthentication|null $currentAuthentication = null,
        private ProjectAuthenticatedUser|null $projectAuthenticatedUser = null,
        private RequireFreshMfa|null $requireFreshMfa = null,
        private GenerateBackupCodes|null $generateBackupCodes = null,
        private VerifyBackupCode|null $verifyBackupCode = null,
        private StartMfaChallenge|null $startMfaChallenge = null,
    ) {
    }

    /**
     * @return array{
     *     identity: Identity,
     *     tenancy: Tenancy,
     *     scim: SCIM,
     *     assessCurrentRisk: AssessCurrentRisk,
     *     readRiskSignals: ReadRiskSignals,
     *     currentAuthentication: CurrentAuthentication,
     *     projectAuthenticatedUser: ProjectAuthenticatedUser,
     *     requireFreshMfa: RequireFreshMfa,
     *     generateBackupCodes: GenerateBackupCodes,
     *     verifyBackupCode: VerifyBackupCode,
     *     startMfaChallenge: StartMfaChallenge,
     *     authCapabilityReadiness: AuthCapabilityReadiness,
     *     provisionableUserSource: ProvisionableUserSourceInterface|null,
     *     lifecycle: LifecycleOrchestrator|null,
     * }
     */
    public function assemble() : array
    {
        $currentAuthentication    = $this->currentAuthentication ?? new CurrentAuthentication();
        $projectAuthenticatedUser = $this->projectAuthenticatedUser ?? new ProjectAuthenticatedUser(
            emailVerificationStateStore: $this->emailVerificationStateStore,
            mfaStore: $this->mfaStore,
        );
        $requireFreshMfa          = $this->requireFreshMfa ?? new RequireFreshMfa(currentAuthentication: $currentAuthentication, clock: $this->clock);
        $generateBackupCodes      = $this->generateBackupCodes ?? new GenerateBackupCodes(passwordHasher: $this->passwordHasher, clock: $this->clock);
        $verifyBackupCode         = $this->verifyBackupCode ?? new VerifyBackupCode(mfaStore: $this->mfaStore, passwordHasher: $this->passwordHasher, auditLog: $this->auditLog, clock: $this->clock);
        $startMfaChallenge        = $this->startMfaChallenge ?? new StartMfaChallenge(
            currentAuthentication: $currentAuthentication,
            generalMfaStore: $this->mfaStore,
            mfaChallengeStore: $this->mfaChallengeStore,
            auditLog: $this->auditLog,
            clock: $this->clock,
        );

        $provisionableUserSource = $this->userSource instanceof ProvisionableUserSourceInterface ? $this->userSource : null;
        $lifecycle               = $provisionableUserSource instanceof ProvisionableUserSourceInterface
            ? new LifecycleOrchestrator(provisionableUserSource: $provisionableUserSource, lifecycleStore: $this->lifecycleStore, auditLog: $this->auditLog, clock: $this->clock)
            : null;

        $authCapabilityReadiness = AuthCapabilityReadiness::from(
            jwtIdentity            : $this->identity->jwtIdentity(),
            refreshTokenStore      : $this->refreshTokenStore,
            passkeyRuntime         : $this->passkeyRuntime,
            federationRuntime      : $this->federationRuntime,
            provisionableUserSource: $provisionableUserSource,
        );

        $provisionScimUser = $authCapabilityReadiness->scim()
            ? new ProvisionScimUser(
                passwordHasher: $this->passwordHasher,
                idGenerator: $this->idGenerator,
                auditLog: $this->auditLog,
                clock: $this->clock,
                attemptThrottle: $this->scimThrottle,
                provisionableUserSource: $provisionableUserSource ?? throw ConfigurationException::missingDependency("ProvisionableUserSource", "forUser()"),
                scimDirectoryStore: $this->scimDirectoryStore,
                scimProvisionedIdentityStore: $this->scimProvisionedIdentityStore,
                lifecycleOrchestrator: $lifecycle,
            )
            : null;
        $readScimUsers = $authCapabilityReadiness->scim()
            ? new ReadScimUsers(userSource: $this->userSource, scimProvisionedIdentityStore: $this->scimProvisionedIdentityStore)
            : null;
        $readScimGroups = $authCapabilityReadiness->scim() && $readScimUsers instanceof ReadScimUsers
            ? new ReadScimGroups(readScimUsers: $readScimUsers)
            : null;
        $runScimBulk = $authCapabilityReadiness->scim() && $provisionScimUser instanceof ProvisionScimUser
            ? new RunScimBulk(
                provisionScimUser: $provisionScimUser,
                deleteScimUser: new DeleteScimUser(
                    provisionableUserSource: $provisionableUserSource ?? throw ConfigurationException::missingDependency("ProvisionableUserSource", "forUser()"),
                    scimDirectoryStore: $this->scimDirectoryStore,
                    scimProvisionedIdentityStore: $this->scimProvisionedIdentityStore,
                    auditLog: $this->auditLog,
                    clock: $this->clock,
                    lifecycleOrchestrator: $lifecycle,
                    attemptThrottle: $this->scimThrottle,
                ),
            )
            : null;

        $assessCurrentRisk = new AssessCurrentRisk(
            currentAuthentication: $currentAuthentication,
            userSource: $this->userSource,
            deterministicRiskEngine: $this->riskEngine,
        );
        $readRiskSignals = new ReadRiskSignals(
            currentAuthentication: $currentAuthentication,
            deterministicRiskEngine: $this->riskEngine,
        );

        $sessions = new Sessions(
            logoutAllSessions: new LogoutAllSessions(
                identity: $this->identity,
                currentAuthentication: $currentAuthentication,
                auditLog: $this->auditLog,
                clock: $this->clock,
                sessionRegistry: $this->sessionRegistry,
                refreshTokenStore: $this->refreshTokenStore,
            ),
            readActiveSessions: new ReadActiveSessions(
                currentAuthentication: $currentAuthentication,
                clock: $this->clock,
                sessionRegistry: $this->sessionRegistry,
            ),
            revokeSession: new RevokeSession(
                identity: $this->identity,
                currentAuthentication: $currentAuthentication,
                auditLog: $this->auditLog,
                clock: $this->clock,
                sessionRegistry: $this->sessionRegistry,
            ),
        );

        $authentication = new Authentication(
            login: new Login(
                findUserByCredentials: new FindUserByCredentials(userSource: $this->userSource),
                verifyPassword: new VerifyPassword(passwordHasher: $this->passwordHasher),
                startAuthenticatedSession: new StartAuthenticatedSession(sessions: $sessions),
                identity: $this->identity,
            ),
            logout: new Logout(
                clearAuthenticatedIdentity: new ClearAuthenticatedIdentity(sessions: $sessions),
                identity: $this->identity,
            ),
            refreshAuthentication: new RefreshAuthentication(
                userSource: $this->userSource,
                projectAuthenticatedUser: $projectAuthenticatedUser,
                currentAuthentication: $currentAuthentication,
                auditLog: $this->auditLog,
                clock: $this->clock,
                refreshTokenStore: $this->refreshTokenStore,
                jwtIdentity: $this->identity->jwtIdentity(),
                deterministicRiskEngine: $this->riskEngine,
            ),
        );

        $account = new Account(
            changePassword: new ChangePassword(
                userSource: $this->userSource,
                passwordHasher: $this->passwordHasher,
                identity: $this->identity,
                currentAuthentication: $currentAuthentication,
                auditLog: $this->auditLog,
                clock: $this->clock,
                sessionRegistry: $this->sessionRegistry,
                mfaChallengeStore: $this->mfaChallengeStore,
                refreshTokenStore: $this->refreshTokenStore,
                loginRateLimit: $this->loginRateLimit,
                requireFreshMfa: $requireFreshMfa,
            ),
            beginEmailChange: new BeginEmailChange(
                currentAuthentication: $currentAuthentication,
                userSource: $this->userSource,
                passwordHasher: $this->passwordHasher,
                emailChangeStore: $this->emailChangeStore,
                requireFreshMfa: $requireFreshMfa,
                auditLog: $this->auditLog,
                clock: $this->clock,
            ),
            confirmEmailChange: $provisionableUserSource instanceof ProvisionableUserSourceInterface
                ? new ConfirmEmailChange(
                    provisionableUserSource: $provisionableUserSource,
                    emailChangeStore: $this->emailChangeStore,
                    emailVerificationStateStore: $this->emailVerificationStateStore,
                    auditLog: $this->auditLog,
                    clock: $this->clock,
                    currentAuthentication: $currentAuthentication,
                    identity: $this->identity,
                    sessionRegistry: $this->sessionRegistry,
                    mfaChallengeStore: $this->mfaChallengeStore,
                    refreshTokenStore: $this->refreshTokenStore,
                )
                : null,
            register: new Register(
                validateRegistrationData: new ValidateRegistrationData(),
                hashRegisteredPassword: new HashRegisteredPassword(passwordHasher: $this->passwordHasher),
                createRegisteredUser: new CreateRegisteredUser(userSource: $this->userSource, idGenerator: $this->idGenerator),
                identity: $this->identity,
            ),
        );

        $recovery = new Recovery(
            beginPasswordReset: new BeginPasswordReset(
                userSource: $this->userSource,
                passwordResetStore: $this->passwordResetStore,
                auditLog: $this->auditLog,
                clock: $this->clock,
                attemptThrottle: $this->passwordResetThrottle,
            ),
            resetPassword: new ResetPassword(
                userSource: $this->userSource,
                passwordHasher: $this->passwordHasher,
                passwordResetStore: $this->passwordResetStore,
                auditLog: $this->auditLog,
                clock: $this->clock,
                sessionRegistry: $this->sessionRegistry,
                mfaChallengeStore: $this->mfaChallengeStore,
                refreshTokenStore: $this->refreshTokenStore,
            ),
        );

        $verification = new Verification(
            beginEmailVerification: new BeginEmailVerification(
                userSource: $this->userSource,
                emailVerificationStore: $this->emailVerificationStore,
                auditLog: $this->auditLog,
                clock: $this->clock,
            ),
            verifyEmail: new VerifyEmail(
                emailVerificationStore: $this->emailVerificationStore,
                emailVerificationStateStore: $this->emailVerificationStateStore,
                auditLog: $this->auditLog,
                clock: $this->clock,
            ),
        );

        $mfa = new Mfa(
            startMfaEnrollment: new StartMfaEnrollment(
                currentAuthentication: $currentAuthentication,
                mfaStore: $this->mfaStore,
                totp: $this->totp,
                auditLog: $this->auditLog,
                clock: $this->clock,
                issuer: $this->mfaIssuer,
            ),
            confirmMfaEnrollment: new ConfirmMfaEnrollment(
                currentAuthentication: $currentAuthentication,
                mfaStore: $this->mfaStore,
                totp: $this->totp,
                generateBackupCodes: $generateBackupCodes,
                auditLog: $this->auditLog,
                clock: $this->clock,
            ),
            cancelMfaEnrollment: new CancelMfaEnrollment(
                currentAuthentication: $currentAuthentication,
                mfaStore: $this->mfaStore,
                auditLog: $this->auditLog,
                clock: $this->clock,
            ),
            startMfaChallenge: $startMfaChallenge,
            verifyMfaChallenge: new VerifyMfaChallenge(
                mfaChallengeStore: $this->mfaChallengeStore,
                mfaStore: $this->mfaStore,
                totp: $this->totp,
                verifyBackupCode: $verifyBackupCode,
                userSource: $this->userSource,
                identity: $this->identity,
                projectAuthenticatedUser: $projectAuthenticatedUser,
                currentAuthentication: $currentAuthentication,
                auditLog: $this->auditLog,
                clock: $this->clock,
                limitMfaAttempts: $this->limitMfaAttempts,
                deterministicRiskEngine: $this->riskEngine,
            ),
            regenerateBackupCodes: new RegenerateBackupCodes(
                currentAuthentication: $currentAuthentication,
                requireFreshMfa: $requireFreshMfa,
                mfaStore: $this->mfaStore,
                generateBackupCodes: $generateBackupCodes,
                auditLog: $this->auditLog,
                clock: $this->clock,
            ),
            disableMfa: new DisableMfa(
                currentAuthentication: $currentAuthentication,
                requireFreshMfa: $requireFreshMfa,
                mfaStore: $this->mfaStore,
                mfaChallengeStore: $this->mfaChallengeStore,
                auditLog: $this->auditLog,
                clock: $this->clock,
                refreshTokenStore: $this->refreshTokenStore,
            ),
            startMfaRecovery: new StartMfaRecovery(
                userSource: $this->userSource,
                mfaStore: $this->mfaStore,
                auditLog: $this->auditLog,
                clock: $this->clock,
                attemptThrottle: $this->mfaRecoveryThrottle,
            ),
            confirmMfaRecovery: new ConfirmMfaRecovery(
                mfaStore: $this->mfaStore,
                mfaChallengeStore: $this->mfaChallengeStore,
                auditLog: $this->auditLog,
                clock: $this->clock,
                sessionRegistry: $this->sessionRegistry,
                refreshTokenStore: $this->refreshTokenStore,
                currentAuthentication: $currentAuthentication,
                identity: $this->identity,
            ),
        );

        $passkey = new Passkey(
            beginPasskeyRegistration: $authCapabilityReadiness->passkey()
                ? new BeginPasskeyRegistration(
                    currentAuthentication: $currentAuthentication,
                    requireFreshMfa: $requireFreshMfa,
                    passkeyRuntime: $this->passkeyRuntime,
                    passkeyCredentialStore: $this->passkeyCredentialStore,
                    passkeyChallengeStore: $this->passkeyChallengeStore,
                    auditLog: $this->auditLog,
                    clock: $this->clock,
                    rpId: $this->passkeyRpId,
                    rpName: $this->passkeyRpName,
                )
                : null,
            completePasskeyRegistration: $authCapabilityReadiness->passkey()
                ? new CompletePasskeyRegistration(
                    currentAuthentication: $currentAuthentication,
                    passkeyRuntime: $this->passkeyRuntime,
                    passkeyCredentialStore: $this->passkeyCredentialStore,
                    passkeyChallengeStore: $this->passkeyChallengeStore,
                    auditLog: $this->auditLog,
                    clock: $this->clock,
                    rpId: $this->passkeyRpId,
                )
                : null,
            beginPasskeyAuthentication: $authCapabilityReadiness->passkey()
                ? new BeginPasskeyAuthentication(
                    userSource: $this->userSource,
                    passkeyRuntime: $this->passkeyRuntime,
                    passkeyCredentialStore: $this->passkeyCredentialStore,
                    passkeyChallengeStore: $this->passkeyChallengeStore,
                    auditLog: $this->auditLog,
                    clock: $this->clock,
                    rpId: $this->passkeyRpId,
                )
                : null,
            completePasskeyAuthentication: $authCapabilityReadiness->passkey()
                ? new CompletePasskeyAuthentication(
                    passkeyRuntime: $this->passkeyRuntime,
                    passkeyChallengeStore: $this->passkeyChallengeStore,
                    passkeyCredentialStore: $this->passkeyCredentialStore,
                    userSource: $this->userSource,
                    identity: $this->identity,
                    projectAuthenticatedUser: $projectAuthenticatedUser,
                    currentAuthentication: $currentAuthentication,
                    auditLog: $this->auditLog,
                    clock: $this->clock,
                    rpId: $this->passkeyRpId,
                )
                : null,
            renamePasskey: $authCapabilityReadiness->passkey()
                ? new RenamePasskey(currentAuthentication: $currentAuthentication, passkeyCredentialStore: $this->passkeyCredentialStore)
                : null,
            revokePasskey: $authCapabilityReadiness->passkey()
                ? new RevokePasskey(
                    currentAuthentication: $currentAuthentication,
                    requireFreshMfa: $requireFreshMfa,
                    passkeyCredentialStore: $this->passkeyCredentialStore,
                    auditLog: $this->auditLog,
                    clock: $this->clock,
                )
                : null,
            listPasskeys: $authCapabilityReadiness->passkey()
                ? new ListPasskeys(currentAuthentication: $currentAuthentication, passkeyCredentialStore: $this->passkeyCredentialStore)
                : null,
        );

        $identity = new Identity(
            authentication: $authentication,
            sessions: $sessions,
            account: $account,
            recovery: $recovery,
            verification: $verification,
            mfa: $mfa,
            passkey: $passkey,
            sessionIdentity: $this->identity->sessionIdentity(),
            jwtIdentity: $this->identity->jwtIdentity(),
        );

        $scim = new SCIM(
            registerScimDirectory: $authCapabilityReadiness->scim()
                ? new \Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RegisterDirectory\RegisterScimDirectory(
                    passwordHasher: $this->passwordHasher,
                    groupRoleMappingValidator: new GroupRoleMappingValidator(),
                    auditLog: $this->auditLog,
                    clock: $this->clock,
                    scimDirectoryStore: $this->scimDirectoryStore,
                )
                : null,
            readScimDirectories: $authCapabilityReadiness->scim()
                ? new \Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadDirectories\ReadScimDirectories(scimDirectoryStore: $this->scimDirectoryStore)
                : null,
            rotateScimToken: $authCapabilityReadiness->scim()
                ? new \Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RotateToken\RotateScimToken(
                    passwordHasher: $this->passwordHasher,
                    auditLog: $this->auditLog,
                    clock: $this->clock,
                    attemptThrottle: $this->scimThrottle,
                    scimDirectoryStore: $this->scimDirectoryStore,
                )
                : null,
            markScimDirectoryOutage: $authCapabilityReadiness->scim()
                ? new \Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\MarkOutage\MarkScimDirectoryOutage(
                    auditLog: $this->auditLog,
                    clock: $this->clock,
                    scimDirectoryStore: $this->scimDirectoryStore,
                )
                : null,
            recoverScimDirectoryOutage: $authCapabilityReadiness->scim()
                ? new \Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RecoverOutage\RecoverScimDirectoryOutage(
                    auditLog: $this->auditLog,
                    clock: $this->clock,
                    scimDirectoryStore: $this->scimDirectoryStore,
                )
                : null,
            provisionScimUser: $provisionScimUser,
            deleteScimUser: $authCapabilityReadiness->scim()
                ? new DeleteScimUser(
                    auditLog: $this->auditLog,
                    clock: $this->clock,
                    attemptThrottle: $this->scimThrottle,
                    provisionableUserSource: $provisionableUserSource ?? throw ConfigurationException::missingDependency("ProvisionableUserSource", "forUser()"),
                    scimDirectoryStore: $this->scimDirectoryStore,
                    scimProvisionedIdentityStore: $this->scimProvisionedIdentityStore,
                    lifecycleOrchestrator: $lifecycle,
                )
                : null,
            readScimUsers: $readScimUsers,
            readScimGroups: $readScimGroups,
            syncScimGroups: $authCapabilityReadiness->scim() && $provisionScimUser instanceof ProvisionScimUser
                ? new SyncScimGroups(
                    userSource: $this->userSource,
                    provisionScimUser: $provisionScimUser,
                    scimDirectoryStore: $this->scimDirectoryStore,
                    scimProvisionedIdentityStore: $this->scimProvisionedIdentityStore,
                )
                : null,
            runScimBulk: $runScimBulk,
        );

        $tenants = new Tenants(
            createTenant: new CreateTenant(tenantStore: $this->tenantStore, userSource: $this->userSource, auditLog: $this->auditLog, clock: $this->clock),
            readTenants: new ReadTenants(tenantStore: $this->tenantStore),
            inviteTenantMember: new InviteTenantMember(tenantStore: $this->tenantStore, auditLog: $this->auditLog, clock: $this->clock),
            acceptTenantInvite: new AcceptTenantInvite(tenantStore: $this->tenantStore, userSource: $this->userSource, auditLog: $this->auditLog, clock: $this->clock),
            readTenantMembers: new ReadTenantMembers(tenantStore: $this->tenantStore),
            removeTenantMember: new RemoveTenantMember(tenantStore: $this->tenantStore, auditLog: $this->auditLog, clock: $this->clock),
            suspendTenantMember: new SuspendTenantMember(tenantStore: $this->tenantStore, auditLog: $this->auditLog, clock: $this->clock),
            transferTenantOwnership: new TransferTenantOwnership(tenantStore: $this->tenantStore, auditLog: $this->auditLog, clock: $this->clock),
        );

        $security = new Security(
            readTenantSecurityConfiguration: new ReadTenantSecurityConfiguration(tenantSecurityConfigurationStore: $this->tenantSecurityConfigurationStore),
            readTenantSecurityChangeRequest: new ReadTenantSecurityChangeRequest(tenantSecurityChangeRequestStore: $this->tenantSecurityChangeRequestStore),
            readTenantSecurityChangeRequests: new ReadTenantSecurityChangeRequests(tenantSecurityChangeRequestStore: $this->tenantSecurityChangeRequestStore),
            beginTenantSecurityChange: new BeginTenantSecurityChange(
                tenantSecurityConfigurationStore: $this->tenantSecurityConfigurationStore,
                tenantSecurityChangeRequestStore: $this->tenantSecurityChangeRequestStore,
                federationConnectionStore: $this->federationConnectionStore,
                scimDirectoryStore: $this->scimDirectoryStore,
                auditLog: $this->auditLog,
                clock: $this->clock,
            ),
            approveTenantSecurityChange: new ApproveTenantSecurityChange(
                auditLog: $this->auditLog,
                clock: $this->clock,
                tenantSecurityChangeRequestStore: $this->tenantSecurityChangeRequestStore,
            ),
            applyTenantSecurityChange: new ApplyTenantSecurityChange(
                auditLog: $this->auditLog,
                clock: $this->clock,
                tenantSecurityConfigurationStore: $this->tenantSecurityConfigurationStore,
                tenantSecurityChangeRequestStore: $this->tenantSecurityChangeRequestStore,
            ),
            rollbackTenantSecurityChange: new RollbackTenantSecurityChange(
                auditLog: $this->auditLog,
                clock: $this->clock,
                tenantSecurityConfigurationStore: $this->tenantSecurityConfigurationStore,
                tenantSecurityChangeRequestStore: $this->tenantSecurityChangeRequestStore,
                defaultConfiguration: new TenantSecurityConfiguration(tenantSlug: 'default'),
            ),
        );

        $tenancy = new Tenancy(tenants: $tenants, security: $security);

        return [
            'identity' => $identity,
            'tenancy' => $tenancy,
            'scim' => $scim,
            'assessCurrentRisk' => $assessCurrentRisk,
            'readRiskSignals' => $readRiskSignals,
            'currentAuthentication' => $currentAuthentication,
            'projectAuthenticatedUser' => $projectAuthenticatedUser,
            'requireFreshMfa' => $requireFreshMfa,
            'generateBackupCodes' => $generateBackupCodes,
            'verifyBackupCode' => $verifyBackupCode,
            'startMfaChallenge' => $startMfaChallenge,
            'authCapabilityReadiness' => $authCapabilityReadiness,
            'provisionableUserSource' => $provisionableUserSource,
            'lifecycle' => $lifecycle,
        ];
    }
}
