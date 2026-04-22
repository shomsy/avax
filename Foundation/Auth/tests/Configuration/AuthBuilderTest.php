<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Configuration;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\InMemoryOAuthClientRegistry;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\InMemoryOidcRequestObjectStore;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcIdToken;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcJsonWebKey;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcJsonWebKeySet;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcProviderInterface;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcProviderMetadata;
use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\InMemoryFederatedIdentityLinkStore;
use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\InMemoryPasskeyCredentialStore;
use Avax\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentity;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Sessions\Registry\InMemorySessionRegistry;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Codec\HmacTokenCodec;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\InMemoryRefreshTokenStore;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\InMemoryTokenRevocationStore;
use Avax\Auth\System\Capabilities\Identity\User\User;
use Avax\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\InMemoryScimDirectoryStore;
use Avax\Auth\System\Configuration\AuthBuilder;
use Avax\Auth\System\Flows\Register\RegistrationData;
use Avax\Auth\System\Foundation\Clock;
use Avax\Auth\System\Foundation\Exceptions\ConfigurationException;
use Avax\Auth\System\Foundation\IdGeneratorInterface;
use Avax\Auth\Tests\Support\ArraySessionStore;
use Avax\Auth\Tests\Support\FakeFederationRuntime;
use Avax\Auth\Tests\Support\FakePasskeyRuntime;
use Avax\Auth\Tests\Support\FrozenClock;
use BadMethodCallException;
use DateTimeImmutable;
use Exception;
use Mockery;
use Override;
use PHPUnit\Framework\TestCase;
use SensitiveParameter;

/**
 * Unit test for AuthBuilder (Configuration slice).
 */
class AuthBuilderTest extends TestCase
{
    public function testAuthBuilderThrowsExceptionWithoutUserSource() : void
    {
        $builder = new AuthBuilder();
        $this->expectException(exception: ConfigurationException::class);
        $this->expectExceptionMessage(message: 'requires a user source');
        $builder->ready();
    }

    public function testAuthBuilderThrowsExceptionWithoutIdentity() : void
    {
        $userSource = Mockery::mock(UserSourceInterface::class);
        $builder    = new AuthBuilder();
        $builder->forUser(userSource: $userSource);

        $this->expectException(exception: ConfigurationException::class);
        $this->expectExceptionMessage(message: 'requires an identity coordinator');
        $builder->ready();
    }

    public function testAuthBuilderBuildsAuthInstance() : void
    {
        $userSource = Mockery::mock(UserSourceInterface::class);
        $identity   = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('sessionIdentity')->andReturn(Mockery::mock(SessionIdentityInterface::class));
        $identity->shouldReceive('jwtIdentity')->andReturn(null);

        $builder = new AuthBuilder();
        $builder->forUser(userSource: $userSource)
            ->withIdentity(identity: $identity)
            ->requirePhishingResistantAdminElevation();

        $auth = $builder->ready();

        $this->assertInstanceOf(expected: Auth::class, actual: $auth);
    }

    /**
     * @throws Exception
     */
    public function testAuthBuilderUsesConfiguredIdGenerator() : void
    {
        $data = new RegistrationData(
            email   : 'builder@example.com',
            username: 'builder',
            password: 'password'
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('emailExists')->with($data->email)->andReturn(false);
        $userSource->shouldReceive('usernameExists')->with($data->username)->andReturn(false);
        $userSource->shouldReceive('create')->once()->andReturnUsing(static fn ($user) => $user);

        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('sessionIdentity')->andReturn(Mockery::mock(SessionIdentityInterface::class));
        $identity->shouldReceive('jwtIdentity')->andReturn(null);

        $idGenerator = new class implements IdGeneratorInterface {
            public function generate() : int
            {
                return 987654;
            }
        };

        $passwordHasher = new PasswordHasher(algo: PASSWORD_BCRYPT, options: ['cost' => 4]);

        $builder = new AuthBuilder();
        $auth    = $builder
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: $identity)
            ->usingIdGenerator(idGenerator: $idGenerator)
            ->usingHasher(passwordHasher: $passwordHasher)
            ->ready();

        $user = $auth->register(data: $data);

        $this->assertSame(expected: 987654, actual: $user->user()->id);
    }

    /**
     * @throws Exception
     */
    public function testAuthBuilderUsesConfiguredClockForRegistrationAuditEvents() : void
    {
        $data = new RegistrationData(
            email   : 'clocked@example.com',
            username: 'clocked',
            password: 'password'
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('emailExists')->with($data->email)->andReturn(false);
        $userSource->shouldReceive('usernameExists')->with($data->username)->andReturn(false);
        $userSource->shouldReceive('create')->once()->andReturnUsing(static fn ($user) => $user);

        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('sessionIdentity')->andReturn(Mockery::mock(SessionIdentityInterface::class));
        $identity->shouldReceive('jwtIdentity')->andReturn(null);

        $auditLog = new InMemoryAuditLog();
        $clock    = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-20T10:00:00+00:00'));

        $auth = (new AuthBuilder())
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: $identity)
            ->usingHasher(passwordHasher: new PasswordHasher(algo: PASSWORD_BCRYPT, options: ['cost' => 4]))
            ->usingIdGenerator(idGenerator: new class implements IdGeneratorInterface {
                public function generate() : int
                {
                    return 123456;
                }
            })
            ->withAuditLog(auditLog: $auditLog)
            ->withClock(clock: $clock)
            ->ready();

        $auth->register(data: $data);

        $events = $auditLog->events();

        $this->assertCount(expectedCount: 1, haystack: $events);
        $this->assertSame(expected: 'auth.register.succeeded', actual: $events[0]->name);
        $this->assertEquals(expected: $clock->now(), actual: $events[0]->occurredAt);
    }

    public function testEnterpriseModeRequiresSessionRegistry() : void
    {
        $userSource = Mockery::mock(UserSourceInterface::class);
        $identity   = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('sessionIdentity')->andReturn(Mockery::mock(SessionIdentityInterface::class));
        $identity->shouldReceive('jwtIdentity')->andReturn(null);

        $builder = new AuthBuilder();
        $builder->forUser(userSource: $userSource)
            ->withIdentity(identity: $identity)
            ->enterprise();

        $this->expectException(exception: ConfigurationException::class);
        $this->expectExceptionMessage(message: 'cannot enable [enterprise_mode] because [session_registry] is missing');
        $builder->ready();
    }

    public function testEnterpriseModeBuildsWithSessionRegistry() : void
    {
        $userSource = Mockery::mock(UserSourceInterface::class);
        $identity   = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('sessionIdentity')->andReturn(Mockery::mock(SessionIdentityInterface::class));
        $identity->shouldReceive('jwtIdentity')->andReturn(null);

        $sessionRegistry = new InMemorySessionRegistry();

        $builder = new AuthBuilder();
        $builder->forUser(userSource: $userSource)
            ->withIdentity(identity: $identity)
            ->withSessionRegistry(sessionRegistry: $sessionRegistry)
            ->enterprise();

        $auth = $builder->ready();

        $this->assertInstanceOf(expected: Auth::class, actual: $auth);
    }

    public function testAuthBuilderFailsFastWhenIdentityHasNoBackend() : void
    {
        $userSource = Mockery::mock(UserSourceInterface::class);
        $identity   = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('sessionIdentity')->andReturn(null);
        $identity->shouldReceive('jwtIdentity')->andReturn(null);

        $builder = new AuthBuilder();
        $builder->forUser(userSource: $userSource)
            ->withIdentity(identity: $identity);

        $this->expectException(exception: ConfigurationException::class);
        $this->expectExceptionMessage(message: 'requires at least one identity backend');

        $builder->ready();
    }

    public function testAuthBuilderBuildsUsableKernelFromStableIdentityBackendSeam() : void
    {
        $auth = (new AuthBuilder())
            ->forUser(userSource: new InMemoryUserSource())
            ->withIdentityBackends(sessionIdentity: new SessionIdentity(store: new ArraySessionStore()))
            ->ready();

        $result = $auth->register(data: new RegistrationData(
                                            email   : 'seam@example.com',
                                            username: 'seam-user',
                                            password: 'password'
                                        ));

        $this->assertInstanceOf(expected: Auth::class, actual: $auth);
        $this->assertSame(expected: 'seam@example.com', actual: $result->user()->email);
        $this->assertFalse(condition: $auth->externalIdentity()->oauth()->isConfigured());
    }

    public function testAuthBuilderBuildsEnterpriseishKernelWithOptionalCapabilities() : void
    {
        $userSource      = new InMemoryUserSource();
        $refreshTokens   = new InMemoryRefreshTokenStore();
        $sessionRegistry = new InMemorySessionRegistry();

        $auth = (new AuthBuilder())
            ->forUser(userSource: $userSource)
            ->withIdentityBackends(
                sessionIdentity: new SessionIdentity(
                                     store          : new ArraySessionStore(),
                                     sessionRegistry: $sessionRegistry
                                 ),
                jwtIdentity    : $this->jwtIdentity(
                                     userSource   : $userSource,
                                     refreshTokens: $refreshTokens
                                 )
            )
            ->withSessionRegistry(sessionRegistry: $sessionRegistry)
            ->withRefreshTokenStore(refreshTokenStore: $refreshTokens)
            ->withPasskeyRuntime(passkeyRuntime: new FakePasskeyRuntime())
            ->withOidcProvider(oidcProvider: $this->oidcProvider())
            ->withFederationRuntime(federationRuntime: new FakeFederationRuntime())
            ->enterprise()
            ->ready();

        $this->assertTrue(condition: $auth->identity()->passkey()->isConfigured());
        $this->assertTrue(condition: $auth->externalIdentity()->oauth()->isConfigured());
        $this->assertTrue(condition: $auth->externalIdentity()->oidc()->isConfigured());
        $this->assertTrue(condition: $auth->externalIdentity()->sso()->isConfigured());
        $this->assertTrue(condition: $auth->identitySync()->scim()->isConfigured());
    }

    public function testAuthBuilderFailsFastWhenPasskeyConfigurationIsPartial() : void
    {
        $exception = null;

        try {
            (new AuthBuilder())
                ->forUser(userSource: new InMemoryUserSource())
                ->withIdentityBackends(sessionIdentity: new SessionIdentity(store: new ArraySessionStore()))
                ->withPasskeyCredentialStore(passkeyCredentialStore: new InMemoryPasskeyCredentialStore())
                ->ready();
        } catch (ConfigurationException $caught) {
            $exception = $caught;
        }

        $this->assertInstanceOf(expected: ConfigurationException::class, actual: $exception);
        $this->assertSame(expected: 'auth.configuration.dependency_missing', actual: $exception->errorCode());
        $this->assertSame(expected: 'passkey', actual: $exception->context()['capability']);
        $this->assertSame(expected: 'runtime', actual: $exception->context()['requirement']);
    }

    public function testAuthBuilderFailsFastWhenOidcRequestObjectsAreConfiguredWithoutProvider() : void
    {
        $exception = null;

        try {
            (new AuthBuilder())
                ->forUser(userSource: new InMemoryUserSource())
                ->withIdentityBackends(sessionIdentity: new SessionIdentity(store: new ArraySessionStore()))
                ->withOidcRequestObjectStore(oidcRequestObjectStore: new InMemoryOidcRequestObjectStore())
                ->ready();
        } catch (ConfigurationException $caught) {
            $exception = $caught;
        }

        $this->assertInstanceOf(expected: ConfigurationException::class, actual: $exception);
        $this->assertSame(expected: 'auth.configuration.dependency_missing', actual: $exception->errorCode());
        $this->assertSame(expected: 'oidc', actual: $exception->context()['capability']);
        $this->assertSame(expected: 'provider', actual: $exception->context()['requirement']);
    }

    public function testAuthBuilderFailsFastWhenOAuthConfigurationIsMissingRefreshTokens() : void
    {
        $userSource = new InMemoryUserSource();
        $exception  = null;

        try {
            (new AuthBuilder())
                ->forUser(userSource: $userSource)
                ->withIdentityBackends(jwtIdentity: $this->jwtIdentity(
                    userSource   : $userSource,
                    refreshTokens: new InMemoryRefreshTokenStore()
                ))
                ->withOAuthClientRegistry(oauthClientRegistry: new InMemoryOAuthClientRegistry(passwordHasher: new PasswordHasher()))
                ->ready();
        } catch (ConfigurationException $caught) {
            $exception = $caught;
        }

        $this->assertInstanceOf(expected: ConfigurationException::class, actual: $exception);
        $this->assertSame(expected: 'auth.configuration.dependency_missing', actual: $exception->errorCode());
        $this->assertSame(expected: 'oauth', actual: $exception->context()['capability']);
        $this->assertSame(expected: 'refresh_token_store', actual: $exception->context()['requirement']);
    }

    public function testAuthBuilderFailsFastWhenFederationConfigurationIsPartial() : void
    {
        $userSource = new InMemoryUserSource();
        $exception  = null;

        try {
            (new AuthBuilder())
                ->forUser(userSource: $userSource)
                ->withIdentityBackends(jwtIdentity: $this->jwtIdentity(
                    userSource   : $userSource,
                    refreshTokens: new InMemoryRefreshTokenStore()
                ))
                ->withFederatedIdentityLinkStore(
                    federatedIdentityLinkStore: new InMemoryFederatedIdentityLinkStore()
                )
                ->ready();
        } catch (ConfigurationException $caught) {
            $exception = $caught;
        }

        $this->assertInstanceOf(expected: ConfigurationException::class, actual: $exception);
        $this->assertSame(expected: 'auth.configuration.dependency_missing', actual: $exception->errorCode());
        $this->assertSame(expected: 'federation', actual: $exception->context()['capability']);
        $this->assertSame(expected: 'runtime', actual: $exception->context()['requirement']);
    }

    public function testAuthBuilderFailsFastWhenScimStoresTargetNonProvisionableUserSource() : void
    {
        $userSource = Mockery::mock(UserSourceInterface::class);
        $identity   = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('sessionIdentity')->andReturn(Mockery::mock(SessionIdentityInterface::class));
        $identity->shouldReceive('jwtIdentity')->andReturn(null);

        $exception = null;

        try {
            (new AuthBuilder())
                ->forUser(userSource: $userSource)
                ->withIdentity(identity: $identity)
                ->withScimDirectoryStore(scimDirectoryStore: new InMemoryScimDirectoryStore(
                                                                 passwordHasher: new PasswordHasher()
                                                             ))
                ->ready();
        } catch (ConfigurationException $caught) {
            $exception = $caught;
        }

        $this->assertInstanceOf(expected: ConfigurationException::class, actual: $exception);
        $this->assertSame(expected: 'auth.configuration.dependency_missing', actual: $exception->errorCode());
        $this->assertSame(expected: 'scim', actual: $exception->context()['capability']);
        $this->assertSame(expected: 'provisionable_user_source', actual: $exception->context()['requirement']);
    }

    private function jwtIdentity(
        InMemoryUserSource        $userSource,
        #[SensitiveParameter] InMemoryRefreshTokenStore $refreshTokens
    ) : JwtIdentity
    {
        return new JwtIdentity(
            userSource       : $userSource,
            codec            : new HmacTokenCodec(secret: 'builder-secret'),
            clock            : new Clock(),
            revocationStore  : new InMemoryTokenRevocationStore(),
            refreshTokenStore: $refreshTokens
        );
    }

    private function oidcProvider() : OidcProviderInterface
    {
        return new class('https://auth.example.test') implements OidcProviderInterface {
            public function __construct(
                private readonly string $issuer
            ) {}

            public function issueIdToken(
                User                              $user,
                string                            $clientId,
                array                             $scopes,
                string|null                       $nonce = null,
                DateTimeImmutable|null            $authenticatedAt = null,
                #[SensitiveParameter] string|null $sessionId = null,
                bool                              $phishingResistant = false
            ) : OidcIdToken
            {
                return new OidcIdToken(
                    token    : 'id-token',
                    expiresAt: new DateTimeImmutable(datetime: '+5 minutes')
                );
            }

            public function issueJwt(array $claims) : string
            {
                return 'provider-jwt';
            }

            public function readProviderMetadata() : OidcProviderMetadata
            {
                return new OidcProviderMetadata(
                    issuer                                        : $this->issuer,
                    authorizationEndpoint                         : $this->issuer . '/authorize',
                    tokenEndpoint                                 : $this->issuer . '/token',
                    registrationEndpoint                          : $this->issuer . '/oidc/register',
                    userInfoEndpoint                              : $this->issuer . '/userinfo',
                    endSessionEndpoint                            : $this->issuer . '/logout',
                    pushedAuthorizationRequestEndpoint            : $this->issuer . '/par',
                    jsonWebKeySetUri                              : $this->issuer . '/jwks',
                    scopesSupported                               : ['openid'],
                    responseTypesSupported                        : ['code'],
                    grantTypesSupported                           : ['authorization_code'],
                    subjectTypesSupported                         : ['public'],
                    idTokenSigningAlgValuesSupported              : ['RS256'],
                    codeChallengeMethodsSupported                 : ['S256'],
                    frontChannelLogoutSupported                   : true,
                    backChannelLogoutSupported                    : true,
                    backChannelLogoutSessionSupported             : true,
                    requestObjectSigningAlgValuesSupported        : ['RS256'],
                    authorizationResponseSigningAlgValuesSupported: ['RS256']
                );
            }

            public function readJsonWebKeySet() : OidcJsonWebKeySet
            {
                return new OidcJsonWebKeySet(keys: [
                                                       new OidcJsonWebKey(
                                                           keyType  : 'RSA',
                                                           keyId    : 'kid-1',
                                                           algorithm: 'RS256',
                                                           use      : 'sig',
                                                           modulus  : 'modulus',
                                                           exponent : 'AQAB'
                                                       ),
                                                   ]);
            }

            public function subjectIdentifier(User $user, string $clientId) : string
            {
                return 'subject-' . $clientId . '-' . $user->getId()->value;
            }

            public function resolveIdToken(#[SensitiveParameter] string $idToken) : array|null
            {
                throw new BadMethodCallException(message: 'Not required for this test.');
            }

            public function resolveJwt(#[SensitiveParameter] string $jwt) : array|null
            {
                throw new BadMethodCallException(message: 'Not required for this test.');
            }
        };
    }

    #[Override]
    protected function tearDown() : void
    {
        Mockery::close();
    }
}
