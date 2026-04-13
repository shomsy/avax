<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Passkey;

use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\Passkey\InMemoryPasskeyChallengeStore;
use Avax\Auth\System\Capability\Passkey\InMemoryPasskeyCredentialStore;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Passkey\BeginAuthentication\BeginPasskeyAuthentication;
use Avax\Auth\System\Flow\Passkey\BeginAuthentication\BeginPasskeyAuthenticationData;
use Avax\Auth\System\Flow\Passkey\BeginRegistration\BeginPasskeyRegistration;
use Avax\Auth\System\Flow\Passkey\CompleteAuthentication\CompletePasskeyAuthentication;
use Avax\Auth\System\Flow\Passkey\CompleteAuthentication\CompletePasskeyAuthenticationData;
use Avax\Auth\System\Flow\Passkey\CompleteRegistration\CompletePasskeyRegistration;
use Avax\Auth\System\Flow\Passkey\CompleteRegistration\CompletePasskeyRegistrationData;
use Avax\Auth\System\Flow\Passkey\ListPasskeys\ListPasskeys;
use Avax\Auth\System\Flow\Passkey\PasskeyOperationFailed;
use Avax\Auth\System\Flow\Passkey\RenamePasskey\RenamePasskey;
use Avax\Auth\System\Flow\Passkey\RenamePasskey\RenamePasskeyData;
use Avax\Auth\System\Flow\Passkey\RevokePasskey\RevokePasskey;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flow\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Flow\Verify\InMemoryEmailVerificationStateStore;
use Avax\Auth\Tests\Support\FakePasskeyRuntime;
use Avax\Auth\System\Flow\Mfa\InMemoryMfaStore;
use Avax\Auth\System\Flow\Mfa\StepUp\RequireFreshMfa;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

final class PasskeyFlowTest extends TestCase
{
    public function testPasskeyRegistrationAuthenticationAndRevocation() : void
    {
        $clock           = new Clock();
        $userSource      = new InMemoryUserSource();
        $credentialStore = new InMemoryPasskeyCredentialStore();
        $challengeStore  = new InMemoryPasskeyChallengeStore();
        $current         = new CurrentAuthentication();
        $user            = User::create(
            id          : new UserId(5),
            email       : new UserEmail('passkey@example.com'),
            username    : 'passkey-user',
            passwordHash: 'hash'
        );
        $userSource->create($user);
        $current->store(AuthenticationContext::authenticated(
            user      : new AuthenticatedUser(
                id       : 5,
                email    : 'passkey@example.com',
                username : 'passkey-user'
            ),
            mode      : AuthenticationMode::SESSION,
            sessionId : 'session-5'
        ));

        $runtime = new FakePasskeyRuntime();
        $beginRegistration = new BeginPasskeyRegistration(
            currentAuthentication: $current,
            requireFreshMfa      : new RequireFreshMfa($current, $clock),
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
        $credential   = $completeRegistration->execute(new CompletePasskeyRegistrationData(
            challengeId: $registration->challengeId,
            response   : ['credential_id' => 'cred-1', 'label' => 'Laptop']
        ));
        $renamed      = (new RenamePasskey(
            currentAuthentication: $current,
            credentialStore      : $credentialStore
        ))->execute(new RenamePasskeyData(
            credentialId: 'cred-1',
            label       : 'Primary Laptop'
        ));

        $this->assertSame('cred-1', $credential->credentialId);
        $this->assertSame('Primary Laptop', $renamed->label);
        $this->assertCount(1, (new ListPasskeys($current, $credentialStore))->execute());

        $current->clear();
        $identity = new Identity(jwtIdentity: new JwtIdentity(
            userSource       : $userSource,
            codec            : new HmacTokenCodec('passkey-secret'),
            clock            : $clock,
            revocationStore  : new InMemoryTokenRevocationStore(),
            refreshTokenStore: new InMemoryRefreshTokenStore()
        ));
        $beginAuthentication = new BeginPasskeyAuthentication(
            userSource      : $userSource,
            runtime         : $runtime,
            credentialStore : $credentialStore,
            challengeStore  : $challengeStore,
            auditLog        : new InMemoryAuditLog(),
            clock           : $clock,
            rpId            : 'example.test'
        );
        $completeAuthentication = new CompletePasskeyAuthentication(
            runtime              : $runtime,
            challengeStore       : $challengeStore,
            credentialStore      : $credentialStore,
            userSource           : $userSource,
            identity             : $identity,
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                emailVerificationState: new InMemoryEmailVerificationStateStore(),
                mfaStore              : new InMemoryMfaStore()
            ),
            currentAuthentication: $current,
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock,
            rpId                 : 'example.test'
        );

        $challenge = $beginAuthentication->execute(new BeginPasskeyAuthenticationData(identifier: 'passkey@example.com'));
        $result    = $completeAuthentication->execute(new CompletePasskeyAuthenticationData(
            challengeId: $challenge->challengeId,
            response   : ['credential_id' => 'cred-1']
        ));

        $this->assertTrue($result->isAuthenticated());

        (new RevokePasskey(
            currentAuthentication: $current,
            requireFreshMfa      : new RequireFreshMfa($current, $clock),
            credentialStore      : $credentialStore,
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock
        ))->execute('cred-1');

        $this->assertTrue($credentialStore->find('cred-1')?->isRevoked() ?? false);
    }

    public function testPasskeyAuthenticationChallengeCannotBeReplayed() : void
    {
        $clock           = new Clock();
        $userSource      = new InMemoryUserSource();
        $credentialStore = new InMemoryPasskeyCredentialStore();
        $challengeStore  = new InMemoryPasskeyChallengeStore();
        $current         = new CurrentAuthentication();
        $user            = User::create(
            id          : new UserId(6),
            email       : new UserEmail('passkey-replay@example.com'),
            username    : 'passkey-replay',
            passwordHash: 'hash'
        );
        $userSource->create($user);
        $credentialStore->save(new \Avax\Auth\System\Capability\Passkey\PasskeyCredential(
            userId      : 6,
            credentialId: 'cred-replay',
            label       : 'Replay Device',
            registeredAt: $clock->now()
        ));

        $runtime = new FakePasskeyRuntime();
        $challenge = (new BeginPasskeyAuthentication(
            userSource      : $userSource,
            runtime         : $runtime,
            credentialStore : $credentialStore,
            challengeStore  : $challengeStore,
            auditLog        : new InMemoryAuditLog(),
            clock           : $clock,
            rpId            : 'example.test'
        ))->execute(new BeginPasskeyAuthenticationData(identifier: 'passkey-replay@example.com'));

        $complete = new CompletePasskeyAuthentication(
            runtime              : $runtime,
            challengeStore       : $challengeStore,
            credentialStore      : $credentialStore,
            userSource           : $userSource,
            identity             : new Identity(jwtIdentity: new JwtIdentity(
                userSource       : $userSource,
                codec            : new HmacTokenCodec('passkey-replay-secret'),
                clock            : $clock,
                revocationStore  : new InMemoryTokenRevocationStore(),
                refreshTokenStore: new InMemoryRefreshTokenStore()
            )),
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                emailVerificationState: new InMemoryEmailVerificationStateStore(),
                mfaStore              : new InMemoryMfaStore()
            ),
            currentAuthentication: $current,
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock,
            rpId                 : 'example.test'
        );

        $result = $complete->execute(new CompletePasskeyAuthenticationData(
            challengeId: $challenge->challengeId,
            response   : ['credential_id' => 'cred-replay']
        ));
        $this->assertTrue($result->isAuthenticated());

        $this->expectException(PasskeyOperationFailed::class);
        $this->expectExceptionMessage('Passkey challenge has already been used.');

        $complete->execute(new CompletePasskeyAuthenticationData(
            challengeId: $challenge->challengeId,
            response   : ['credential_id' => 'cred-replay']
        ));
    }
}
