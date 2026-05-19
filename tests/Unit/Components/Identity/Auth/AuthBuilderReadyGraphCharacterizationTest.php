<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth;

use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\AttemptThrottle;
use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\InMemoryAttemptThrottleStore;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\DeterministicRiskEngine;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\InMemoryKnownAuthenticationEnvironmentStore;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\InMemoryRiskSignalStore;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\NullAuditLog;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\InMemorySessionRegistry;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Components\Identity\Auth\System\Configuration\Assembly\AssembleAuthIdentityGraph;
use Avax\Components\Identity\Auth\System\Configuration\Readiness\AuthCapabilityReadiness;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Auth\System\Foundation\IdGenerator;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Limit\InMemoryAttemptLimitStorage;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Limit\LimitMfaAttempts;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Stores\InMemoryMfaStore;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Totp\Totp;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\InMemoryMfaChallengeStore;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\InMemoryPasskeyChallengeStore;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\InMemoryPasskeyCredentialStore;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyRuntimeInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\ResolvedPasskeyCredential;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\VerifiedPasskeyAuthentication;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnection;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederatedIdentity;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationRuntimeInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\InMemoryFederationConnectionStore;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\StartedFederatedLogin;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\InMemoryOAuthClientRegistry;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\InMemoryAuthorizationCodeStore;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\InMemoryAdminElevationStore;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\InMemoryTenantStore;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\InMemoryTenantSecurityChangeRequestStore;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\InMemoryTenantSecurityConfigurationStore;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\InMemoryRefreshTokenStore;
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\EmailChangeStoreInterface;
use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\InMemoryEmailChangeStore;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\InMemoryLoginRateLimitStorage;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\LoginRateLimit;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\InMemoryPasswordResetStore;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\EmailVerificationStateStoreInterface;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\EmailVerificationStoreInterface;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\InMemoryEmailVerificationStateStore;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\InMemoryEmailVerificationStore;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\InMemoryLifecycleStore;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\InMemoryScimDirectoryStore;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\InMemoryScimProvisionedIdentityStore;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectoryStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimProvisionedIdentityStoreInterface;
use Avax\Components\Identity\Auth\System\Configuration\Builders\AuthBuilder;
use Avax\Components\Identity\Auth\System\Foundation\Exceptions\ConfigurationException;
use Avax\Components\Identity\Auth\System\PublicSurface\Auth;

/**
 * Characterization tests for the AuthBuilder first-slice split.
 *
 * These tests prove the boundary between AuthBuilder and AssembleAuthIdentityGraph
 * is correct, stable, and safe.
 */
final class AuthBuilderReadyGraphCharacterizationTest extends TestCase
{
    private Clock $clock;
    private PasswordHasher $passwordHasher;

    #[Override]
    protected function setUp(): void
    {
        $this->clock = new Clock();
        $this->passwordHasher = new PasswordHasher();
    }

    /** @return array<string, mixed> */
    private function makeDependencies(): array
    {
        $riskEnvStore = new InMemoryKnownAuthenticationEnvironmentStore();
        $riskSignalStore = new InMemoryRiskSignalStore();
        $throttleStore = new InMemoryAttemptThrottleStore();

        $passkeyRuntime = new class implements PasskeyRuntimeInterface {
            #[Override]
            public function beginRegistration(string $rpId, string $rpName, int $userId, string $userName, string $displayName, string $challenge, array $excludeCredentialIds): array {
                return ['challenge' => $challenge, 'rpId' => $rpId];
            }
            #[Override]
            public function completeRegistration(string $rpId, string $challenge, array $response): ResolvedPasskeyCredential {
                return new ResolvedPasskeyCredential(credentialId: 'cred-1', label: 'test-passkey');
            }
            #[Override]
            public function beginAuthentication(string $rpId, string $challenge, array $allowCredentialIds): array {
                return ['challenge' => $challenge, 'rpId' => $rpId];
            }
            #[Override]
            public function completeAuthentication(string $rpId, string $challenge, array $response, array $knownCredentials): VerifiedPasskeyAuthentication {
                return new VerifiedPasskeyAuthentication(userId: 1, credentialId: 'cred-1');
            }
        };

        return [
            'userSource' => new InMemoryUserSource(),
            'auditLog' => new NullAuditLog(),
            'clock' => $this->clock,
            'passwordHasher' => $this->passwordHasher,
            'idGenerator' => new IdGenerator(),
            'sessionRegistry' => new InMemorySessionRegistry(),
            'refreshTokenStore' => new InMemoryRefreshTokenStore(),
            'mfaStore' => new InMemoryMfaStore(),
            'mfaChallengeStore' => new InMemoryMfaChallengeStore(),
            'totp' => new Totp(),
            'limitMfaAttempts' => new LimitMfaAttempts(new InMemoryAttemptLimitStorage(), $this->clock),
            'passwordResetThrottle' => new AttemptThrottle($throttleStore, $this->clock, 5, 300),
            'mfaRecoveryThrottle' => new AttemptThrottle($throttleStore, $this->clock, 3, 600),
            'scimThrottle' => new AttemptThrottle($throttleStore, $this->clock, 10, 60),
            'passwordResetStore' => new InMemoryPasswordResetStore(),
            'emailVerificationStore' => new InMemoryEmailVerificationStore(),
            'emailVerificationStateStore' => new InMemoryEmailVerificationStateStore(),
            'emailChangeStore' => new InMemoryEmailChangeStore(),
            'adminElevationStore' => new InMemoryAdminElevationStore(),
            'riskEngine' => new DeterministicRiskEngine($riskEnvStore, $riskSignalStore, $this->clock),
            'passkeyRuntime' => $passkeyRuntime,
            'passkeyCredentialStore' => new InMemoryPasskeyCredentialStore(),
            'passkeyChallengeStore' => new InMemoryPasskeyChallengeStore(),
            'lifecycleStore' => new InMemoryLifecycleStore(),
            'scimDirectoryStore' => new InMemoryScimDirectoryStore($this->passwordHasher),
            'scimProvisionedIdentityStore' => new InMemoryScimProvisionedIdentityStore(),
            'tenantStore' => new InMemoryTenantStore(),
            'tenantSecurityConfigStore' => new InMemoryTenantSecurityConfigurationStore(),
            'tenantSecurityChangeRequestStore' => new InMemoryTenantSecurityChangeRequestStore(),
            'federationConnectionStore' => new InMemoryFederationConnectionStore(),
            'oauthClientRegistry' => new InMemoryOAuthClientRegistry($this->passwordHasher),
            'authorizationCodeStore' => new InMemoryAuthorizationCodeStore(),
            'federatedIdentityLinkStore' => new \Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\InMemoryFederatedIdentityLinkStore(),
        ];
    }

    private function makeSessionIdentity(): \Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface
    {
        return new class implements \Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface {
            private ?string $userId = null;
            private ?string $sessionId = null;
            public function issue(int $userId, \DateTimeImmutable|null $mfaVerifiedAt = null, bool $phishingResistant = false): string
            {
                $this->userId = (string) $userId;
                $this->sessionId = 'session-' . $userId;
                return $this->sessionId;
            }
            public function clear(): void { $this->userId = null; $this->sessionId = null; }
            public function resolveUserId(): int|null { return $this->userId !== null ? (int) $this->userId : null; }
            public function resolveMfaVerifiedAt(): \DateTimeImmutable|null { return null; }
            public function resolvePhishingResistant(): bool { return false; }
            public function captureCurrentSession(?string $ipAddress = null, string|null $userAgent = null): void {}
            public function currentSessionId(): string|null { return $this->sessionId; }
        };
    }

    private function makeIdentity(): Identity
    {
        return Identity::fromBackends(sessionIdentity: $this->makeSessionIdentity());
    }

    #[Test]
    public function federationRuntimeConfigurationEnablesFederationReadiness(): void
    {
        $d = $this->makeDependencies();
        $identity = $this->makeIdentity();

        $federationRuntime = new class implements FederationRuntimeInterface {
            #[Override]
            public function startLogin(FederationConnection $federationConnection, string $redirectUri, string|null $state = null): StartedFederatedLogin
            {
                return new StartedFederatedLogin(redirectUrl: 'https://example.com/login', state: $state ?? 'test');
            }
            #[Override]
            public function completeLogin(FederationConnection $federationConnection, array $payload): FederatedIdentity
            {
                return new FederatedIdentity(subject: 'test-user', email: 'test@example.com', displayName: 'Test User');
            }
        };

        $graph = (new AssembleAuthIdentityGraph(
            userSource: $d['userSource'], identity: $identity, auditLog: $d['auditLog'], clock: $d['clock'],
            passwordHasher: $d['passwordHasher'], idGenerator: $d['idGenerator'],
            sessionRegistry: $d['sessionRegistry'], refreshTokenStore: $d['refreshTokenStore'],
            mfaStore: $d['mfaStore'], mfaChallengeStore: $d['mfaChallengeStore'],
            totp: $d['totp'], limitMfaAttempts: $d['limitMfaAttempts'],
            passwordResetThrottle: $d['passwordResetThrottle'], mfaRecoveryThrottle: $d['mfaRecoveryThrottle'],
            scimThrottle: $d['scimThrottle'],
            passwordResetStore: $d['passwordResetStore'], emailVerificationStore: $d['emailVerificationStore'],
            emailVerificationStateStore: $d['emailVerificationStateStore'], emailChangeStore: $d['emailChangeStore'],
            adminElevationStore: $d['adminElevationStore'], riskEngine: $d['riskEngine'],
            passkeyRuntime: $d['passkeyRuntime'], passkeyCredentialStore: $d['passkeyCredentialStore'],
            passkeyChallengeStore: $d['passkeyChallengeStore'], lifecycleStore: $d['lifecycleStore'],
            scimDirectoryStore: $d['scimDirectoryStore'], scimProvisionedIdentityStore: $d['scimProvisionedIdentityStore'],
            tenantStore: $d['tenantStore'], tenantSecurityConfigurationStore: $d['tenantSecurityConfigStore'],
            tenantSecurityChangeRequestStore: $d['tenantSecurityChangeRequestStore'],
            federationConnectionStore: $d['federationConnectionStore'],
            federationRuntime: $federationRuntime,
        ))->assemble();

        self::assertTrue($graph['authCapabilityReadiness']->federation());
    }

    #[Test]
    public function federationReadinessIsFalseWithoutRuntime(): void
    {
        $d = $this->makeDependencies();
        $identity = $this->makeIdentity();

        $graph = (new AssembleAuthIdentityGraph(
            userSource: $d['userSource'], identity: $identity, auditLog: $d['auditLog'], clock: $d['clock'],
            passwordHasher: $d['passwordHasher'], idGenerator: $d['idGenerator'],
            sessionRegistry: $d['sessionRegistry'], refreshTokenStore: $d['refreshTokenStore'],
            mfaStore: $d['mfaStore'], mfaChallengeStore: $d['mfaChallengeStore'],
            totp: $d['totp'], limitMfaAttempts: $d['limitMfaAttempts'],
            passwordResetThrottle: $d['passwordResetThrottle'], mfaRecoveryThrottle: $d['mfaRecoveryThrottle'],
            scimThrottle: $d['scimThrottle'],
            passwordResetStore: $d['passwordResetStore'], emailVerificationStore: $d['emailVerificationStore'],
            emailVerificationStateStore: $d['emailVerificationStateStore'], emailChangeStore: $d['emailChangeStore'],
            adminElevationStore: $d['adminElevationStore'], riskEngine: $d['riskEngine'],
            passkeyRuntime: $d['passkeyRuntime'], passkeyCredentialStore: $d['passkeyCredentialStore'],
            passkeyChallengeStore: $d['passkeyChallengeStore'], lifecycleStore: $d['lifecycleStore'],
            scimDirectoryStore: $d['scimDirectoryStore'], scimProvisionedIdentityStore: $d['scimProvisionedIdentityStore'],
            tenantStore: $d['tenantStore'], tenantSecurityConfigurationStore: $d['tenantSecurityConfigStore'],
            tenantSecurityChangeRequestStore: $d['tenantSecurityChangeRequestStore'],
            federationConnectionStore: $d['federationConnectionStore'],
            federationRuntime: null,
        ))->assemble();

        self::assertFalse($graph['authCapabilityReadiness']->federation());
    }

    #[Test]
    public function scimConfigurationEnablesScimReadiness(): void
    {
        $d = $this->makeDependencies();
        $identity = $this->makeIdentity();

        $graph = (new AssembleAuthIdentityGraph(
            userSource: $d['userSource'], identity: $identity, auditLog: $d['auditLog'], clock: $d['clock'],
            passwordHasher: $d['passwordHasher'], idGenerator: $d['idGenerator'],
            sessionRegistry: $d['sessionRegistry'], refreshTokenStore: $d['refreshTokenStore'],
            mfaStore: $d['mfaStore'], mfaChallengeStore: $d['mfaChallengeStore'],
            totp: $d['totp'], limitMfaAttempts: $d['limitMfaAttempts'],
            passwordResetThrottle: $d['passwordResetThrottle'], mfaRecoveryThrottle: $d['mfaRecoveryThrottle'],
            scimThrottle: $d['scimThrottle'],
            passwordResetStore: $d['passwordResetStore'], emailVerificationStore: $d['emailVerificationStore'],
            emailVerificationStateStore: $d['emailVerificationStateStore'], emailChangeStore: $d['emailChangeStore'],
            adminElevationStore: $d['adminElevationStore'], riskEngine: $d['riskEngine'],
            passkeyRuntime: $d['passkeyRuntime'], passkeyCredentialStore: $d['passkeyCredentialStore'],
            passkeyChallengeStore: $d['passkeyChallengeStore'], lifecycleStore: $d['lifecycleStore'],
            scimDirectoryStore: $d['scimDirectoryStore'], scimProvisionedIdentityStore: $d['scimProvisionedIdentityStore'],
            tenantStore: $d['tenantStore'], tenantSecurityConfigurationStore: $d['tenantSecurityConfigStore'],
            tenantSecurityChangeRequestStore: $d['tenantSecurityChangeRequestStore'],
            federationConnectionStore: $d['federationConnectionStore'],
        ))->assemble();

        self::assertTrue($graph['authCapabilityReadiness']->scim());
    }

    #[Test]
    public function mfaPrimitivesAreWiredConsistently(): void
    {
        $d = $this->makeDependencies();
        $identity = $this->makeIdentity();

        $graph = (new AssembleAuthIdentityGraph(
            userSource: $d['userSource'], identity: $identity, auditLog: $d['auditLog'], clock: $d['clock'],
            passwordHasher: $d['passwordHasher'], idGenerator: $d['idGenerator'],
            sessionRegistry: $d['sessionRegistry'], refreshTokenStore: $d['refreshTokenStore'],
            mfaStore: $d['mfaStore'], mfaChallengeStore: $d['mfaChallengeStore'],
            totp: $d['totp'], limitMfaAttempts: $d['limitMfaAttempts'],
            passwordResetThrottle: $d['passwordResetThrottle'], mfaRecoveryThrottle: $d['mfaRecoveryThrottle'],
            scimThrottle: $d['scimThrottle'],
            passwordResetStore: $d['passwordResetStore'], emailVerificationStore: $d['emailVerificationStore'],
            emailVerificationStateStore: $d['emailVerificationStateStore'], emailChangeStore: $d['emailChangeStore'],
            adminElevationStore: $d['adminElevationStore'], riskEngine: $d['riskEngine'],
            passkeyRuntime: $d['passkeyRuntime'], passkeyCredentialStore: $d['passkeyCredentialStore'],
            passkeyChallengeStore: $d['passkeyChallengeStore'], lifecycleStore: $d['lifecycleStore'],
            scimDirectoryStore: $d['scimDirectoryStore'], scimProvisionedIdentityStore: $d['scimProvisionedIdentityStore'],
            tenantStore: $d['tenantStore'], tenantSecurityConfigurationStore: $d['tenantSecurityConfigStore'],
            tenantSecurityChangeRequestStore: $d['tenantSecurityChangeRequestStore'],
            federationConnectionStore: $d['federationConnectionStore'],
        ))->assemble();

        // MFA capabilities are wired: identity exposes mfa() and graph has startMfaChallenge
        // Prove by checking class names (assertInstanceOf always-true when PHPStan knows exact types)
        $mfa = $graph['identity']->mfa();
        // @phpstan-ignore-next-line — characterization test: catches if type changes at runtime
        self::assertSame(\Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Mfa::class, $mfa::class);
        // @phpstan-ignore-next-line — characterization test: catches if type changes at runtime
        self::assertSame(\Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\StartMfaChallenge::class, $graph['startMfaChallenge']::class);
    }

    #[Test]
    public function authCapabilityReadinessDefaultsAreConsistent(): void
    {
        $d = $this->makeDependencies();
        $identity = $this->makeIdentity();

        $readiness = AuthCapabilityReadiness::from(
            jwtIdentity: $identity->jwtIdentity(),
            refreshTokenStore: $d['refreshTokenStore'],
            passkeyRuntime: null,
            federationRuntime: null,
            provisionableUserSource: null,
        );

        // Without JWT identity, OAuth should be false
        self::assertFalse($readiness->oauth());
        // Without passkey runtime, passkey should be false
        self::assertFalse($readiness->passkey());
        // Without federation runtime, federation should be false
        self::assertFalse($readiness->federation());
        // Without provisionable user source, SCIM should be false
        self::assertFalse($readiness->scim());
    }

    /**
     * Scenario 1: minimal ready() happy path — assemble() produces graph with all expected keys.
     *
     * Proves that AssembleAuthIdentityGraph::assemble() returns the expected array shape
     * with all 14 required keys. AuthBuilder::ready() delegates to this and unpacks these keys.
     *
     * This test operates at the assembly level because AuthBuilder::ready() requires the full
     * dependency wall (including JwtIdentityInterface) to be satisfied. The existing 49 Auth
     * tests cover the AuthBuilder::ready() integration at the flow level.
     */
    #[Test]
    public function assembleGraphProducesAllExpectedKeys(): void
    {
        $d = $this->makeDependencies();
        $identity = $this->makeIdentity();

        $graph = (new AssembleAuthIdentityGraph(
            userSource: $d['userSource'], identity: $identity, auditLog: $d['auditLog'], clock: $d['clock'],
            passwordHasher: $d['passwordHasher'], idGenerator: $d['idGenerator'],
            sessionRegistry: $d['sessionRegistry'], refreshTokenStore: $d['refreshTokenStore'],
            mfaStore: $d['mfaStore'], mfaChallengeStore: $d['mfaChallengeStore'],
            totp: $d['totp'], limitMfaAttempts: $d['limitMfaAttempts'],
            passwordResetThrottle: $d['passwordResetThrottle'], mfaRecoveryThrottle: $d['mfaRecoveryThrottle'],
            scimThrottle: $d['scimThrottle'],
            passwordResetStore: $d['passwordResetStore'], emailVerificationStore: $d['emailVerificationStore'],
            emailVerificationStateStore: $d['emailVerificationStateStore'], emailChangeStore: $d['emailChangeStore'],
            adminElevationStore: $d['adminElevationStore'], riskEngine: $d['riskEngine'],
            passkeyRuntime: $d['passkeyRuntime'], passkeyCredentialStore: $d['passkeyCredentialStore'],
            passkeyChallengeStore: $d['passkeyChallengeStore'], lifecycleStore: $d['lifecycleStore'],
            scimDirectoryStore: $d['scimDirectoryStore'], scimProvisionedIdentityStore: $d['scimProvisionedIdentityStore'],
            tenantStore: $d['tenantStore'], tenantSecurityConfigurationStore: $d['tenantSecurityConfigStore'],
            tenantSecurityChangeRequestStore: $d['tenantSecurityChangeRequestStore'],
            federationConnectionStore: $d['federationConnectionStore'],
            federationRuntime: null,
        ))->assemble();

        $expectedKeys = [
            'identity', 'tenancy', 'scim',
            'assessCurrentRisk', 'readRiskSignals',
            'currentAuthentication', 'projectAuthenticatedUser',
            'requireFreshMfa', 'generateBackupCodes', 'verifyBackupCode', 'startMfaChallenge',
            'authCapabilityReadiness', 'provisionableUserSource', 'lifecycle',
        ];
        foreach ($expectedKeys as $key) {
            self::assertArrayHasKey($key, $graph, "Graph should have key: $key");
        }
        self::assertCount(14, $graph); // @phpstan-ignore-line — characterization test: catches if graph shape changes
    }

    /**
     * Scenario 2: existing fluent DSL call site remains stable — each DSL method returns self.
     *
     * Proves that the fluent builder chain returns $this at each step. This is a structural
     * stability test: if a method signature changes to not return $this, this test catches it.
     */
    #[Test]
    public function fluentDslMethodsReturnSelf(): void
    {
        $builder = new AuthBuilder();
        $throttleStore = new InMemoryAttemptThrottleStore();

        // Each method should return the same builder instance for chaining
        self::assertSame($builder, $builder->forUser(new InMemoryUserSource()));
        self::assertSame($builder, $builder->withAuditLog(new NullAuditLog()));
        self::assertSame($builder, $builder->withClock($this->clock));
        self::assertSame($builder, $builder->usingHasher($this->passwordHasher));
        self::assertSame($builder, $builder->withSessionRegistry(new InMemorySessionRegistry()));
        self::assertSame($builder, $builder->withRefreshTokenStore(new InMemoryRefreshTokenStore()));
        self::assertSame($builder, $builder->withMfaStore(new InMemoryMfaStore()));
        self::assertSame($builder, $builder->withMfaChallengeStore(new InMemoryMfaChallengeStore()));
        self::assertSame($builder, $builder->withMfaAttemptLimit(new LimitMfaAttempts(new InMemoryAttemptLimitStorage(), $this->clock)));
        self::assertSame($builder, $builder->withPasswordResetThrottle(new AttemptThrottle($throttleStore, $this->clock, 5, 300)));
        self::assertSame($builder, $builder->withMfaRecoveryThrottle(new AttemptThrottle($throttleStore, $this->clock, 3, 600)));
        self::assertSame($builder, $builder->withScimThrottle(new AttemptThrottle($throttleStore, $this->clock, 10, 60)));
        self::assertSame($builder, $builder->withPasswordResetStore(new InMemoryPasswordResetStore()));
        self::assertSame($builder, $builder->withEmailVerificationStore(new InMemoryEmailVerificationStore()));
        self::assertSame($builder, $builder->withEmailVerificationState(new InMemoryEmailVerificationStateStore()));
        self::assertSame($builder, $builder->withEmailChangeStore(new InMemoryEmailChangeStore()));
        self::assertSame($builder, $builder->withAdminElevationStore(new InMemoryAdminElevationStore()));
        self::assertSame($builder, $builder->withRiskEngine(new DeterministicRiskEngine(new InMemoryKnownAuthenticationEnvironmentStore(), new InMemoryRiskSignalStore(), $this->clock)));
        self::assertSame($builder, $builder->withPasskeyRuntime(new class implements PasskeyRuntimeInterface {
            #[Override] public function beginRegistration(string $rpId, string $rpName, int $userId, string $userName, string $displayName, string $challenge, array $excludeCredentialIds): array { return []; }
            #[Override] public function completeRegistration(string $rpId, string $challenge, array $response): ResolvedPasskeyCredential { return new ResolvedPasskeyCredential('c', 'l'); }
            #[Override] public function beginAuthentication(string $rpId, string $challenge, array $allowCredentialIds): array { return []; }
            #[Override] public function completeAuthentication(string $rpId, string $challenge, array $response, array $knownCredentials): VerifiedPasskeyAuthentication { return new VerifiedPasskeyAuthentication(1, 'c'); }
        }));
        self::assertSame($builder, $builder->withPasskeyCredentialStore(new InMemoryPasskeyCredentialStore()));
        self::assertSame($builder, $builder->withPasskeyChallengeStore(new InMemoryPasskeyChallengeStore()));
        self::assertSame($builder, $builder->withLifecycleStore(new InMemoryLifecycleStore()));
        self::assertSame($builder, $builder->withScimDirectoryStore(new InMemoryScimDirectoryStore($this->passwordHasher)));
        self::assertSame($builder, $builder->withScimProvisionedIdentityStore(new InMemoryScimProvisionedIdentityStore()));
        self::assertSame($builder, $builder->withTenantStore(new InMemoryTenantStore()));
        self::assertSame($builder, $builder->withTenantSecurityConfigurationStore(new InMemoryTenantSecurityConfigurationStore()));
        self::assertSame($builder, $builder->withTenantSecurityChangeRequestStore(new InMemoryTenantSecurityChangeRequestStore()));
        self::assertSame($builder, $builder->withFederationConnectionStore(new InMemoryFederationConnectionStore()));
        self::assertSame($builder, $builder->withFederatedIdentityLinkStore(new \Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\InMemoryFederatedIdentityLinkStore()));
        self::assertSame($builder, $builder->withOAuthClientRegistry(new InMemoryOAuthClientRegistry($this->passwordHasher)));
        self::assertSame($builder, $builder->withAuthorizationCodeStore(new InMemoryAuthorizationCodeStore()));
        self::assertSame($builder, $builder->withFederationRuntime(new class implements FederationRuntimeInterface {
            #[Override] public function startLogin(FederationConnection $fc, string $ru, string|null $s = null): StartedFederatedLogin { return new StartedFederatedLogin(redirectUrl: 'https://x.com', state: $s ?? 't'); }
            #[Override] public function completeLogin(FederationConnection $fc, array $p): FederatedIdentity { return new FederatedIdentity(subject: 's', email: 'e@x.com', displayName: 'D'); }
        }));
    }

    /**
     * Scenario 3: missing required dependency fails explicitly.
     *
     * Proves that calling ready() without a required dependency throws
     * ConfigurationException rather than silently producing a broken object.
     */
    #[Test]
    public function missingRequiredDependencyThrowsConfigurationException(): void
    {
        $this->expectException(ConfigurationException::class);

        // Empty builder — no dependencies configured
        (new AuthBuilder())->ready();
    }

    /**
     * Scenario 7: public Auth DSL does not expose internal assembly classes.
     *
     * Proves that the Auth public surface does not leak AssembleAuthIdentityGraph,
     * AuthCapabilityReadiness, or any internal assembly types. All public methods
     * return domain-level types (User, AuthenticationResult, void, bool).
     *
     * Uses reflection to inspect return types directly — no runtime Identity wiring needed.
     */
    #[Test]
    public function publicAuthDoesNotExposeInternalAssemblyClasses(): void
    {
        // Inspect Auth class via reflection — no builder or Identity wiring needed
        $reflection = new \ReflectionClass(Auth::class);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        $internalTypes = [
            'AssembleAuthIdentityGraph',
            'AuthCapabilityReadiness',
        ];

        foreach ($methods as $method) {
            $returnType = $method->getReturnType();
            if ($returnType === null) {
                continue;
            }
            $returnName = $returnType instanceof \ReflectionNamedType ? $returnType->getName() : '';
            // Check for exact internal assembly type names (not substring matches)
            $shortName = explode(separator: '\\', string: $returnName);
            $shortName = end(array: $shortName);
            foreach ($internalTypes as $internalType) {
                self::assertNotSame(
                    $internalType,
                    $shortName,
                    sprintf('Auth::%s() should not expose internal type %s', $method->getName(), $internalType),
                );
            }
        }

        // Verify return types are domain-level via reflection on specific methods
        $guestMethod = $reflection->getMethod('guest');
        self::assertEquals('bool', (string) $guestMethod->getReturnType());

        $checkMethod = $reflection->getMethod('check');
        self::assertEquals('bool', (string) $checkMethod->getReturnType());

        $userMethod = $reflection->getMethod('user');
        $userReturnType = (string) $userMethod->getReturnType();
        self::assertStringContainsString('User', $userReturnType);
    }

    /**
     * Scenario 8: AuthCapabilityReadiness oauth() is false without jwtIdentity.
     *
     * Proves that even though AuthBuilder requires OAuth infrastructure dependencies
     * (OAuthClientRegistry, AuthorizationCodeStore), the oauth() readiness capability
     * remains false unless jwtIdentity is also provided. This means OAuth/OIDC features
     * are not active for minimal identity usage.
     *
     * Already covered by authCapabilityReadinessDefaultsAreConsistent which asserts
     * oauth() is false when jwtIdentity is null.
     */
}
