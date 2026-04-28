<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Flows\Passkey;

use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Stores\InMemoryMfaStore;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\BeginAuthentication\BeginPasskeyAuthentication;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\BeginAuthentication\BeginPasskeyAuthenticationData;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\BeginRegistration\BeginPasskeyRegistration;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteAuthentication\CompletePasskeyAuthentication;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteAuthentication\CompletePasskeyAuthenticationData;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteRegistration\CompletePasskeyRegistration;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteRegistration\CompletePasskeyRegistrationData;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\ListPasskeys\ListPasskeys;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyOperationFailed;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\RenamePasskey\RenamePasskey;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\RenamePasskey\RenamePasskeyData;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\RevokePasskey\RevokePasskey;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Support\InMemoryPasskeyChallengeStore;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Support\InMemoryPasskeyCredentialStore;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredential;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Codec\HmacTokenCodec;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\InMemoryRefreshTokenStore;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\InMemoryTokenRevocationStore;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\InMemoryEmailVerificationStateStore;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Auth\Tests\Support\FakePasskeyRuntime;
use Avax\Tests\TestCase;
use DateMalformedStringException;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

final class PasskeyFlowTest extends TestCase
{
    /**
     * @throws Unauthenticated
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function testPasskeyRegistrationAuthenticationAndRevocation() : void
    {
        $clock           = new Clock();
        $userSource      = new InMemoryUserSource();
        $credentialStore = new InMemoryPasskeyCredentialStore();
        $challengeStore  = new InMemoryPasskeyChallengeStore();
        $current         = new CurrentAuthentication();
        $user            = User::create(
            id          : new UserId(value: 5),
            email       : new UserEmail(value: 'passkey@example.com'),
            username    : 'passkey-user',
            passwordHash: 'hash'
        );
        $userSource->create(user: $user);
        $current->store(context: AuthenticationContext::authenticated(
            user     : new AuthenticatedUser(
                           id      : 5,
                           email   : 'passkey@example.com',
                           username: 'passkey-user'
                       ),
            mode     : AuthenticationMode::SESSION,
            sessionId: 'session-5'
        ));

        $runtime              = new FakePasskeyRuntime();
        $beginRegistration    = new BeginPasskeyRegistration(
            currentAuthentication: $current,
            requireFreshMfa      : new RequireFreshMfa(currentAuthentication: $current, clock: $clock),
            runtime              : $runtime,
            credentialStore      : $credentialStore,
            challengeStore       : $challengeStore,
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock,
            rpId                 : 'example.test',
            rpName               : 'Example'
        );
        $completeRegistration = new CompletePasskeyRegistration(
            currentAuthentication: $current,
            runtime              : $runtime,
            credentialStore      : $credentialStore,
            challengeStore       : $challengeStore,
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock,
            rpId                 : 'example.test'
        );

        $registration = $beginRegistration->execute();
        $credential   = $completeRegistration->execute(data: new CompletePasskeyRegistrationData(
                                                                 challengeId: $registration->challengeId,
                                                                 response   : ['credential_id' => 'cred-1', 'label' => 'Laptop']
                                                             ));
        $renamed      = new RenamePasskey(
            currentAuthentication: $current,
            credentialStore      : $credentialStore
        )->execute(data: new RenamePasskeyData(
                             credentialId: 'cred-1',
                             label       : 'Primary Laptop'
                         ));

        $this->assertSame(expected: 'cred-1', actual: $credential->credentialId);
        $this->assertSame(expected: 'Primary Laptop', actual: $renamed->label);
        $this->assertCount(expectedCount: 1, haystack: new ListPasskeys(currentAuthentication: $current, credentialStore: $credentialStore)->execute());

        $current->clear();
        $identity               = new Identity(jwtIdentity: new JwtIdentity(
                                                                userSource       : $userSource,
                                                                codec            : new HmacTokenCodec(secret: 'passkey-secret'),
                                                                clock            : $clock,
                                                                revocationStore  : new InMemoryTokenRevocationStore(),
                                                                refreshTokenStore: new InMemoryRefreshTokenStore()
                                                            ));
        $beginAuthentication    = new BeginPasskeyAuthentication(
            userSource     : $userSource,
            runtime        : $runtime,
            credentialStore: $credentialStore,
            challengeStore : $challengeStore,
            auditLog       : new InMemoryAuditLog(),
            clock          : $clock,
            rpId           : 'example.test'
        );
        $completeAuthentication = new CompletePasskeyAuthentication(
            runtime                 : $runtime,
            challengeStore          : $challengeStore,
            credentialStore         : $credentialStore,
            userSource              : $userSource,
            identity                : $identity,
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                                          emailVerificationState: new InMemoryEmailVerificationStateStore(),
                                          mfaStore              : new InMemoryMfaStore()
                                      ),
            currentAuthentication   : $current,
            auditLog                : new InMemoryAuditLog(),
            clock                   : $clock,
            rpId                    : 'example.test'
        );

        $challenge = $beginAuthentication->execute(data: new BeginPasskeyAuthenticationData(identifier: 'passkey@example.com'));
        $result    = $completeAuthentication->execute(data: new CompletePasskeyAuthenticationData(
                                                                challengeId: $challenge->challengeId,
                                                                response   : ['credential_id' => 'cred-1']
                                                            ));

        $this->assertTrue(condition: $result->isAuthenticated());

        new RevokePasskey(
            currentAuthentication: $current,
            requireFreshMfa      : new RequireFreshMfa(currentAuthentication: $current, clock: $clock),
            credentialStore      : $credentialStore,
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock
        )->execute(credentialId: 'cred-1');

        $this->assertTrue(condition: $credentialStore->find(credentialId: 'cred-1')?->isRevoked() ?? false);
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function testPasskeyAuthenticationChallengeCannotBeReplayed() : void
    {
        $clock           = new Clock();
        $userSource      = new InMemoryUserSource();
        $credentialStore = new InMemoryPasskeyCredentialStore();
        $challengeStore  = new InMemoryPasskeyChallengeStore();
        $current         = new CurrentAuthentication();
        $user            = User::create(
            id          : new UserId(value: 6),
            email       : new UserEmail(value: 'passkey-replay@example.com'),
            username    : 'passkey-replay',
            passwordHash: 'hash'
        );
        $userSource->create(user: $user);
        $credentialStore->save(credential: new PasskeyCredential(
                                               userId      : 6,
                                               credentialId: 'cred-replay',
                                               label       : 'Replay Device',
                                               registeredAt: $clock->now()
                                           ));

        $runtime   = new FakePasskeyRuntime();
        $challenge = new BeginPasskeyAuthentication(
            userSource     : $userSource,
            runtime        : $runtime,
            credentialStore: $credentialStore,
            challengeStore : $challengeStore,
            auditLog       : new InMemoryAuditLog(),
            clock          : $clock,
            rpId           : 'example.test'
        )->execute(data: new BeginPasskeyAuthenticationData(identifier: 'passkey-replay@example.com'));

        $complete = new CompletePasskeyAuthentication(
            runtime                 : $runtime,
            challengeStore          : $challengeStore,
            credentialStore         : $credentialStore,
            userSource              : $userSource,
            identity                : new Identity(jwtIdentity: new JwtIdentity(
                                                                    userSource       : $userSource,
                                                                    codec            : new HmacTokenCodec(secret: 'passkey-replay-secret'),
                                                                    clock            : $clock,
                                                                    revocationStore  : new InMemoryTokenRevocationStore(),
                                                                    refreshTokenStore: new InMemoryRefreshTokenStore()
                                                                )),
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                                          emailVerificationState: new InMemoryEmailVerificationStateStore(),
                                          mfaStore              : new InMemoryMfaStore()
                                      ),
            currentAuthentication   : $current,
            auditLog                : new InMemoryAuditLog(),
            clock                   : $clock,
            rpId                    : 'example.test'
        );

        $result = $complete->execute(data: new CompletePasskeyAuthenticationData(
                                               challengeId: $challenge->challengeId,
                                               response   : ['credential_id' => 'cred-replay']
                                           ));
        $this->assertTrue(condition: $result->isAuthenticated());

        $this->expectException(PasskeyOperationFailed::class);
        $this->expectExceptionMessage('Passkey challenge has already been used.');

        $complete->execute(data: new CompletePasskeyAuthenticationData(
                                     challengeId: $challenge->challengeId,
                                     response   : ['credential_id' => 'cred-replay']
                                 ));
    }
}
