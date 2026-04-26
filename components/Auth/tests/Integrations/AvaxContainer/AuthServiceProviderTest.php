<?php

declare(strict_types=1);

namespace components\Auth\Tests\Integrations\AvaxContainer;

use components\Auth\Integrations\AvaxContainer\AuthServiceProvider;
use components\Auth\System\Auth;
use components\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use components\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use components\Auth\System\Capabilities\Identity\Session\SessionIdentity;
use components\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use components\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use components\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use components\Auth\System\Flows\Register\RegistrationData;
use components\Auth\System\Foundation\Clock;
use components\Auth\System\Foundation\Exceptions\ConfigurationException;
use components\Auth\System\Foundation\IdGeneratorInterface;
use components\Auth\Tests\Support\FrozenClock;
use components\Container\Core\AppFactory;
use components\Container\Providers\ServiceProvider;
use components\Tests\TestCase;
use DateTimeImmutable;
use Override;
use ReflectionException;

/**
 * Integration test for the optional Avax container adapter.
 */
class AuthServiceProviderTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testAuthServiceProviderResolvesAuthFacade() : void
    {
        if (! class_exists(class: AppFactory::class)) {
            self::markTestSkipped(message: 'Avax container is not installed in this environment.');
        }

        $dependencies = new class extends ServiceProvider {
            #[Override]
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

        $auth = $container->get(id: Auth::class);

        $this->assertInstanceOf(expected: Auth::class, actual: $auth);

        $result = $auth->register(data: new RegistrationData(
                                            email   : 'provider@example.com',
                                            username: 'provider',
                                            password: 'password'
                                        ));

        $this->assertSame(expected: 424242, actual: $result->user()->id);
    }

    /**
     * @throws ReflectionException
     */
    public function testAuthServiceProviderFailsWithoutIdentityBackend() : void
    {
        if (! class_exists(class: AppFactory::class)) {
            self::markTestSkipped(message: 'Avax container is not installed in this environment.');
        }

        $dependencies = new class extends ServiceProvider {
            #[Override]
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

        $this->expectException(exception: ConfigurationException::class);
        $this->expectExceptionMessage(
            message: 'AuthServiceProvider::registerIdentity() requires at least one identity backend'
        );

        $container->get(id: Auth::class);
    }

    /**
     * @throws ReflectionException
     */
    public function testAuthServiceProviderUsesBoundClockAndAuditLog() : void
    {
        if (! class_exists(class: AppFactory::class)) {
            self::markTestSkipped(message: 'Avax container is not installed in this environment.');
        }

        $auditLog = new InMemoryAuditLog();
        $clock    = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-20T13:00:00+00:00'));

        $dependencies = new class($auditLog, $clock) extends ServiceProvider {
            public function __construct(
                private InMemoryAuditLog $auditLog,
                private FrozenClock      $clock
            ) {}

            #[Override]
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

        $auth = $container->get(id: Auth::class);

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
