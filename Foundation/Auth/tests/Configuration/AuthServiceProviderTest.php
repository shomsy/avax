<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Configuration;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\AuthInterface;
use Avax\Auth\System\Configuration\AuthServiceProvider;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentity;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capabilities\UserSource\InMemoryUserSource;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;
use Avax\Auth\System\Flows\Register\RegistrationData;
use Avax\Auth\System\Foundation\IdGeneratorInterface;
use Avax\Container\Core\AppFactory;
use Avax\Container\Providers\ServiceProvider;

/**
 * Integration test for the Auth component service provider.
 */
class AuthServiceProviderTest extends TestCase
{
    public function testAuthServiceProviderResolvesAuthFacade() : void
    {
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
            cacheDir: sys_get_temp_dir()
        );

        $auth = $container->get(AuthInterface::class);

        $this->assertInstanceOf(AuthInterface::class, $auth);

        $user = $auth->register(new RegistrationData(
            email: 'provider@example.com',
            username: 'provider',
            password: 'password'
        ));

        $this->assertSame(424242, $user->getId()->value);
    }

    public function testAuthServiceProviderFailsWithoutIdentityBackend() : void
    {
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
            cacheDir: sys_get_temp_dir()
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'AuthServiceProvider requires a SessionIdentityInterface or JwtIdentityInterface binding.'
        );

        $container->get(AuthInterface::class);
    }
}
