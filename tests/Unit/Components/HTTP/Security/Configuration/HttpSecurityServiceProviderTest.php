<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\Security\Configuration;

use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use Avax\Components\HTTP\Security\System\Capabilities\Csrf\CsrfTokens;
use Avax\Components\HTTP\Security\System\Configuration\HttpSecurityServiceProvider;
use Avax\Components\HTTP\Security\System\PublicSurface\Security;
use Avax\Components\HTTP\Session\System\Capabilities\Storage\ArraySessionStore;
use Avax\Components\HTTP\Session\System\PublicSurface\Session;
use Avax\Components\HTTP\Session\System\PublicSurface\SessionScope;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class HttpSecurityServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private HttpSecurityServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();

        // Register cross-component dependencies required by HttpSecurityServiceProvider
        // Using singleton() because SimpleContainer::resolve() only returns pre-registered
        // instances when they're marked as singletons
        $this->container->singleton(Session::class, static function (): Session {
            $sessionStore = new ArraySessionStore();
            $sessionScope = new SessionScope($sessionStore);
            return new Session($sessionScope);
        });

        // CsrfTokens requires a non-nullable LoggerInterface
        $this->container->singleton(LoggerInterface::class, static fn () => new NullLogger());

        $this->provider = new HttpSecurityServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_csrf_tokens_resolves(): void
    {
        $csrfTokens = $this->container->get(CsrfTokens::class);

        $this->assertInstanceOf(CsrfTokens::class, $csrfTokens);
    }

    public function test_security_resolves(): void
    {
        $security = $this->container->get(Security::class);

        $this->assertInstanceOf(Security::class, $security);
    }
}
