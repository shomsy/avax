<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integrations\AvaxContainer;

use Avax\Auth\Integrations\AvaxContainer\AuthServiceProvider;
use Avax\Auth\System\AuthInterface;
use Avax\Auth\System\Capability\Identity\Session\SessionIdentity;
use Avax\Auth\System\Capability\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\Register\RegistrationData;
use Avax\Auth\System\Foundation\IdGeneratorInterface;
use Avax\Container\Core\AppFactory;
use Avax\Container\Providers\ServiceProvider;
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
            self::markTestSkipped('Avax container is not installed in this environment.');
        }

        $dependencies = new class extends ServiceProvider {
            public function register() : void
            {
                $this->app->singleton(UserSourceInterface::class, InMemoryUserSource::class);
                $this->app->singleton(SessionIdentityInterface::class, SessionIdentity::class);
                $this->app->instance(
                    IdGeneratorInterface::class,
                    new class implements IdGeneratorInterface {
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

        $auth = $container->get(AuthInterface::class);

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
            self::markTestSkipped('Avax container is not installed in this environment.');
        }

        $dependencies = new class extends ServiceProvider {
            public function register() : void
            {
                $this->app->singleton(UserSourceInterface::class, InMemoryUserSource::class);
                $this->app->instance(
                    IdGeneratorInterface::class,
                    new class implements IdGeneratorInterface {
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

        $container->get(AuthInterface::class);
    }
}
