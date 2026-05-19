<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth;

use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\AttemptThrottle;
use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\InMemoryAttemptThrottleStore;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\DeterministicRiskEngine;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\InMemoryKnownAuthenticationEnvironmentStore;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\InMemoryRiskSignalStore;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\NullAuditLog;
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
        ];
    }

    private function makeIdentity(): Identity
    {
        $sessionIdentity = new class implements \Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface {
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

        return Identity::fromBackends(sessionIdentity: $sessionIdentity);
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

        // MFA and challenge objects are non-null (characterization assertion)
        // @phpstan-ignore-next-line
        self::assertNotNull($graph['identity']->mfa());
        // @phpstan-ignore-next-line
        self::assertNotNull($graph['startMfaChallenge']);
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
}
