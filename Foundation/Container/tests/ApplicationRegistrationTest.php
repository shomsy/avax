<?php

declare(strict_types=1);

namespace Avax\Tests\Container;

use Avax\Container\Features\Operate\Boot\Application;
use PHPUnit\Framework\TestCase;

final class ApplicationRegistrationTest extends TestCase
{
    private Application $app;

    protected function setUp(): void
    {
        // Setup minimal app
        $this->app = Application::start(root: __DIR__)->build();
    }

    public function testSingletonRegistration(): void
    {
        $this->app->singleton('shared_service', function () {
            return new \stdClass();
        });

        $instance1 = $this->app->make('shared_service');
        $instance2 = $this->app->make('shared_service');

        $this->assertInstanceOf(\stdClass::class, $instance1);
        $this->assertSame($instance1, $instance2);
    }

    public function testBindRegistration(): void
    {
        $this->app->bind('transient_service', function () {
            return new \stdClass();
        });

        $instance1 = $this->app->make('transient_service');
        $instance2 = $this->app->make('transient_service');

        $this->assertNotSame($instance1, $instance2);
    }

    public function testScopedRegistration(): void
    {
        $this->app->scoped('scoped_service', function () {
            return new \stdClass();
        });

        // Scope 1
        $this->app->getContainer()->beginScope();
        $instance1 = $this->app->make('scoped_service');
        $instance2 = $this->app->make('scoped_service');
        $this->assertSame($instance1, $instance2);
        $this->app->getContainer()->endScope();

        // Scope 2
        $this->app->getContainer()->beginScope();
        $instance3 = $this->app->make('scoped_service');
        $this->assertNotSame($instance1, $instance3);
        $this->app->getContainer()->endScope();
    }
}
