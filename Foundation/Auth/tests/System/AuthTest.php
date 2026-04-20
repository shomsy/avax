<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\System;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capabilities\Access\AccessInterface;
use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Identity\Identity;
use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentity;
use Avax\Auth\System\Capabilities\OAuth\OAuthClientType;
use Avax\Auth\System\Capabilities\OAuth\OAuthGrantType;
use Avax\Auth\System\Capabilities\OAuth\SenderConstraint\OAuthSenderConstraintType;
use Avax\Auth\System\Capabilities\Session\InMemorySessionRegistry;
use Avax\Auth\System\Capabilities\UserSource\InMemoryUserSource;
use Avax\Auth\System\Configuration\AuthBuilder;
use Avax\Auth\System\Flows\AuthenticateRequest\AuthenticationRequest;
use Avax\Auth\System\Flows\Diagnostics\AuditEvent;
use Avax\Auth\System\Flows\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flows\Login\AuthenticationFailed;
use Avax\Auth\System\Flows\Login\Credentials;
use Avax\Auth\System\Flows\OAuth\RegisterClient\RegisterClientData;
use Avax\Auth\System\Flows\Register\RegistrationData;
use Avax\Auth\System\Flows\Register\RegistrationFailed;
use Avax\Auth\System\Flows\Token\HmacTokenCodec;
use Avax\Auth\System\Flows\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flows\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Foundation\Clock;
use Avax\Auth\Tests\Support\ArraySessionStore;
use PHPUnit\Framework\TestCase;

/**
 * Smoke tests for the public Auth facade.
 */
final class AuthTest extends TestCase
{
    public function testAuthConfigurationReturnsBuilder() : void
    {
        $this->assertInstanceOf(expected: AuthBuilder::class, actual: Auth::configuration());
    }

    /**
     */
    public function testAuthFacadeRunsCorePublicFlowSurface() : void
    {
        $userSource    = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();
        $auth          = Auth::configuration()
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: new Identity(jwtIdentity: new JwtIdentity(
                                                                   userSource       : $userSource,
                                                                   codec            : new HmacTokenCodec(secret: 'auth-test-secret'),
                                                                   clock            : new Clock(),
                                                                   revocationStore  : new InMemoryTokenRevocationStore(),
                                                                   refreshTokenStore: $refreshTokens
                                                               )))
            ->withRefreshTokenStore(refreshTokenStore: $refreshTokens)
            ->ready();

        $registration = $auth->register(data: new RegistrationData(
                                                  email   : 'facade@example.com',
                                                  username: 'facade',
                                                  password: 'secret'
                                              ));
        $this->assertSame(expected: 'facade@example.com', actual: $registration->user()->email);

        $login = $auth->login(credentials: new Credentials(
                                               identifier: 'facade@example.com',
                                               password  : 'secret'
                                           ));

        $this->assertTrue(condition: $login->isAuthenticated());
        $this->assertNotNull(actual: $login->accessToken());
        $this->assertNotNull(actual: $login->refreshToken());
        $this->assertTrue(condition: $auth->check());
        $this->assertSame(expected: $login->user()?->id, actual: $auth->current()->user()?->id);
        $this->assertSame(expected: $login->user()?->email, actual: $auth->user()?->email);
        $this->assertInstanceOf(expected: AccessInterface::class, actual: $auth->access());

        $resolved = $auth->authenticateRequest(request: AuthenticationRequest::bearer(bearerToken: $login->accessToken() ?? ''));
        $this->assertTrue(condition: $resolved->isAuthenticated());
        $this->assertSame(expected: 'facade@example.com', actual: $resolved->user()?->email);

        $auth->logout();

        $this->assertFalse(condition: $auth->check());
        $this->assertNull(actual: $auth->user());
    }

    /**
     * @throws Unauthenticated
     */
    public function testAuthFacadeCanReadAndRevokeTrackedSessions() : void
    {
        $userSource      = new InMemoryUserSource();
        $sessionRegistry = new InMemorySessionRegistry();
        $sessionStore    = new ArraySessionStore();
        $auth            = Auth::configuration()
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: new Identity(
                                         sessionIdentity: new SessionIdentity(
                                                              store          : $sessionStore,
                                                              sessionRegistry: $sessionRegistry
                                                          )
                                     ))
            ->withSessionRegistry(sessionRegistry: $sessionRegistry)
            ->ready();

        $auth->register(data: new RegistrationData(
                                  email   : 'session@example.com',
                                  username: 'session-user',
                                  password: 'secret'
                              ));

        $login = $auth->login(credentials: new Credentials(
                                               identifier: 'session@example.com',
                                               password  : 'secret',
                                               ipAddress : '127.0.0.1',
                                               userAgent : 'PHPUnit'
                                           ));

        $this->assertTrue(condition: $login->isAuthenticated());
        $sessions = $auth->readActiveSessions();
        $this->assertCount(expectedCount: 1, haystack: $sessions);
        $this->assertTrue(condition: $sessions[0]->current);

        $auth->revokeSession(sessionId: $sessions[0]->sessionId);

        $this->assertFalse(condition: $auth->check());
        $this->assertNull(actual: $auth->user());
    }

    /**
     */
    public function testAuthBuilderPropagatesAuditCorrelationIdAcrossFlows() : void
    {
        $userSource    = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();
        $auditLog      = new InMemoryAuditLog();
        $auth          = Auth::configuration()
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: new Identity(jwtIdentity: new JwtIdentity(
                                                                   userSource       : $userSource,
                                                                   codec            : new HmacTokenCodec(secret: 'auth-correlation-secret'),
                                                                   clock            : new Clock(),
                                                                   revocationStore  : new InMemoryTokenRevocationStore(),
                                                                   refreshTokenStore: $refreshTokens
                                                               )))
            ->withRefreshTokenStore(refreshTokenStore: $refreshTokens)
            ->withAuditLog(auditLog: $auditLog)
            ->withAuditCorrelationId(correlationId: 'corr-auth-1')
            ->ready();

        $auth->register(data: new RegistrationData(
                                  email   : 'trace@example.com',
                                  username: 'trace-user',
                                  password: 'secret'
                              ));
        $auth->login(credentials: new Credentials(
                                      identifier: 'trace@example.com',
                                      password  : 'secret'
                                  ));
        $auth->logout();

        $events = $auditLog->events();
        $this->assertNotSame(expected: [], actual: $events);
        $this->assertContainsOnlyInstancesOf(className: AuditEvent::class, haystack: $events);
        array_map(static fn ($event) => $event->correlationId, $events)
            |> array_filter(...)
            |> array_unique(...)
            |> array_values(...)
            |> (fn ($x) => $this->assertSame(expected: ['corr-auth-1'], actual: $x));
    }

    public function testAuthFacadeReadsWorkloadIdentityInventory() : void
    {
        $userSource    = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();
        $auth          = Auth::configuration()
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: new Identity(jwtIdentity: new JwtIdentity(
                                                                   userSource       : $userSource,
                                                                   codec            : new HmacTokenCodec(secret: 'auth-workload-secret'),
                                                                   clock            : new Clock(),
                                                                   revocationStore  : new InMemoryTokenRevocationStore(),
                                                                   refreshTokenStore: $refreshTokens
                                                               )))
            ->withRefreshTokenStore(refreshTokenStore: $refreshTokens)
            ->ready();

        $auth->registerOAuthClient(data: new RegisterClientData(
                                             name                    : 'Search Worker',
                                             type                    : OAuthClientType::CONFIDENTIAL,
                                             redirectUris            : ['urn:avax:oauth:search-worker'],
                                             allowedScopes           : ['search.read'],
                                             allowedAudiences        : ['search-api'],
                                             allowedGrantTypes       : [OAuthGrantType::CLIENT_CREDENTIALS],
                                             requiredSenderConstraint: OAuthSenderConstraintType::MTLS,
                                             workloadIdentity        : true
                                         ));

        $profiles = $auth->readWorkloadIdentities();

        $this->assertCount(expectedCount: 1, haystack: $profiles);
        $this->assertSame(expected: ['search-api'], actual: $profiles[0]->allowedAudiences);
    }

    public function testAuthFacadeExposesExplainabilitySurface() : void
    {
        $auth = Auth::configuration()
            ->forUser(userSource: new InMemoryUserSource())
            ->withIdentity(identity: new Identity(
                                         sessionIdentity: new SessionIdentity(store: new ArraySessionStore())
                                     ))
            ->ready();

        $explanation = $auth->explainSenderConstraintFailure(
            reason            : 'binding_mismatch',
            requiredConstraint: 'mtls'
        );

        $this->assertSame(expected: 'sender_constraint_failed', actual: $explanation->code);
        $this->assertSame(expected: 'mtls', actual: $explanation->context['required_constraint']);
    }
}
