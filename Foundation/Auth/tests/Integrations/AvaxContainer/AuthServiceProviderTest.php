<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integrations\AvaxContainer;

use Avax\Auth\Integrations\AvaxContainer\AuthServiceProvider;
use Avax\Auth\System\AuthInterface;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentity;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capabilities\UserSource\InMemoryUserSource;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;
use Avax\Auth\System\Flows\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flows\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flows\Register\RegistrationData;
use Avax\Auth\System\Foundation\Clock;
use Avax\Auth\System\Foundation\IdGeneratorInterface;
use Avax\Auth\Tests\Support\FrozenClock;
use Avax\Container\Core\AppFactory;
use Avax\Container\Providers\ServiceProvider;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Integration test for the optional Avax container adapter.
 */
class AuthServiceProviderTest extends TestCase
{
    public function testAuthServiceProviderResolvesAuthFacade() : void
    {
        if (! class_exists(AppFactory::class)) {
            self::markTestSkipped(message: 'Avax container is not installed in this environment.');
        }

        $dependencies = new class extends ServiceProvider {
            #[\Override]
            public function register() : void
            {
                $this->app->singleton(id: UserSourceInterface::class, implementation: InMemoryUserSource::class);
                $this->app->singleton(id: SessionIdentityInterface::class, implementation: SessionIdentity::class);
                $this->app->instance(
                    id            : IdGeneratorInterface::class,
                    implementation: new class implements IdGeneratorInterface {
                                        public function generate() : int
                                        {
                                            return 424242;
                                        }
                                    }
                );
            }
        };

        $container = AppFactory::cli(
            providers: [$dependencies, AuthServiceProvider::class],
            cacheDir : sys_get_temp_dir()
        );

        $auth = $container->get(id: AuthInterface::class);

        $this->assertInstanceOf(expected: AuthInterface::class, actual: $auth);

        $result = $auth->register(data: new RegistrationData(
                                            email   : 'provider@example.com',
                                            username: 'provider',
                                            password: 'password'
                                        ));

        $this->assertSame(expected: 424242, actual: $result->user()->id);
    }

    public function testAuthServiceProviderFailsWithoutIdentityBackend() : void
    {
        if (! class_exists(AppFactory::class)) {
            self::markTestSkipped(message: 'Avax container is not installed in this environment.');
        }

        $dependencies = new class extends ServiceProvider {
            #[\Override]
            public function register() : void
            {
                $this->app->singleton(id: UserSourceInterface::class, implementation: InMemoryUserSource::class);
                $this->app->instance(
                    id            : IdGeneratorInterface::class,
                    implementation: new class implements IdGeneratorInterface {
                                        public function generate() : int
                                        {
                                            return 424242;
                                        }
                                    }
                );
            }
        };

        $container = AppFactory::cli(
            providers: [$dependencies, AuthServiceProvider::class],
            cacheDir : sys_get_temp_dir()
        );

        $this->expectException(exception: RuntimeException::class);
        $this->expectExceptionMessage(
            message: 'AuthServiceProvider requires a SessionIdentityInterface or JwtIdentityInterface binding.'
        );

        $container->get(id: AuthInterface::class);
    }

    public function testAuthServiceProviderUsesBoundClockAndAuditLog() : void
    {
        if (! class_exists(AppFactory::class)) {
            self::markTestSkipped(message: 'Avax container is not installed in this environment.');
        }

        $auditLog = new InMemoryAuditLog();
        $clock    = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-20T13:00:00+00:00'));

        $dependencies = new class($auditLog, $clock) extends ServiceProvider {
            public function __construct(
                private InMemoryAuditLog $auditLog,
                private FrozenClock $clock
            ) {}

            #[\Override]
            public function register() : void
            {
                $this->app->singleton(id: UserSourceInterface::class, implementation: InMemoryUserSource::class);
                $this->app->singleton(id: SessionIdentityInterface::class, implementation: SessionIdentity::class);
                $this->app->instance(id: AuditLogInterface::class, implementation: $this->auditLog);
                $this->app->instance(id: Clock::class, implementation: $this->clock);
                $this->app->instance(
                    id            : IdGeneratorInterface::class,
                    implementation: new class implements IdGeneratorInterface {
                        public function generate() : int
                        {
                            return 424242;
                        }
                    }
                );
            }
        };

        $container = AppFactory::cli(
            providers: [$dependencies, AuthServiceProvider::class],
            cacheDir : sys_get_temp_dir()
        );

        $auth = $container->get(id: AuthInterface::class);

        $auth->register(data: new RegistrationData(
            email   : 'provider-clock@example.com',
            username: 'provider-clock',
            password: 'password'
        ));

        $events = $auditLog->events();

        $this->assertCount(expectedCount: 1, haystack: $events);
        $this->assertSame(expected: 'auth.register.succeeded', actual: $events[0]->name);
        $this->assertEquals(expected: $clock->now(), actual: $events[0]->occurredAt);
    }
}
