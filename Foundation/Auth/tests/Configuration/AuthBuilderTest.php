<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Configuration;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Auth\System\Capabilities\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capabilities\Session\InMemorySessionRegistry;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;
use Avax\Auth\System\Configuration\AuthBuilder;
use Avax\Auth\System\Flows\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flows\Register\RegistrationData;
use Avax\Auth\System\Foundation\IdGeneratorInterface;
use Avax\Auth\Tests\Support\FrozenClock;
use DateTimeImmutable;
use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Unit test for AuthBuilder (Configuration slice).
 */
class AuthBuilderTest extends TestCase
{
    public function testAuthBuilderThrowsExceptionWithoutUserSource() : void
    {
        $builder = new AuthBuilder();
        $this->expectException(exception: RuntimeException::class);
        $this->expectExceptionMessage(message: 'Data source is required (forUser).');
        $builder->ready();
    }

    public function testAuthBuilderThrowsExceptionWithoutIdentity() : void
    {
        $userSource = Mockery::mock(UserSourceInterface::class);
        $builder    = new AuthBuilder();
        $builder->forUser(userSource: $userSource);

        $this->expectException(exception: RuntimeException::class);
        $this->expectExceptionMessage(message: 'Identity is required (withIdentity).');
        $builder->ready();
    }

    public function testAuthBuilderBuildsAuthInstance() : void
    {
        $userSource = Mockery::mock(UserSourceInterface::class);
        $identity   = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('sessionIdentity')->andReturn(null);
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
        $identity->shouldReceive('sessionIdentity')->andReturn(null);
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
        $identity->shouldReceive('sessionIdentity')->andReturn(null);
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
        $identity->shouldReceive('sessionIdentity')->andReturn(null);
        $identity->shouldReceive('jwtIdentity')->andReturn(null);

        $builder = new AuthBuilder();
        $builder->forUser(userSource: $userSource)
            ->withIdentity(identity: $identity)
            ->enterprise();

        $this->expectException(exception: RuntimeException::class);
        $this->expectExceptionMessage(message: 'Enterprise mode requires a durable session registry.');
        $builder->ready();
    }

    public function testEnterpriseModeBuildsWithSessionRegistry() : void
    {
        $userSource = Mockery::mock(UserSourceInterface::class);
        $identity   = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('sessionIdentity')->andReturn(null);
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

    #[\Override]
    protected function tearDown() : void
    {
        Mockery::close();
    }
}
