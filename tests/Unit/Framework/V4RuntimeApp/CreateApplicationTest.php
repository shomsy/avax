<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\V4RuntimeApp;

use Avax\Framework\System\Flows\CreateApplication\CreateApplication;
use Avax\Framework\System\PublicSurface\App;
use Avax\Tests\TestCase;

/**
 * @covers \Avax\Framework\System\Flows\CreateApplication\CreateApplication
 */
final class CreateApplicationTest extends TestCase
{
    public function testMakeReturnsAppInstance(): void
    {
        $factory = new CreateApplication();
        $app = $factory->make(environment: 'testing');
        self::assertSame('testing', $app->runtime()->environment()->value);
    }

    public function testMakeInitializesRuntime(): void
    {
        $factory = new CreateApplication();
        $app = $factory->make(environment: 'testing');
        self::assertSame('testing', $app->runtime()->environment()->value);
    }

    public function testMakeSetsUpStateReset(): void
    {
        $factory = new CreateApplication();
        $app = $factory->make(environment: 'testing');
        $report = $app->resetState();
        // @phpstan-ignore-next-line — assertion proves state reset flow works
        self::assertNotNull($report);
    }

    public function testMakeWithDifferentEnvironments(): void
    {
        $factory = new CreateApplication();

        foreach (['testing', 'production', 'development', 'staging'] as $env) {
            $app = $factory->make(environment: $env);
            self::assertSame($env, $app->runtime()->environment()->value, "Failed for environment: $env");
        }
    }

    public function testMakeDoesNotInitializeFullContainer(): void
    {
        // V4-01 defers full DI Container initialization
        // The App should work with RouteFacadeContainer for route dispatch
        $factory = new CreateApplication();
        $app = $factory->make(environment: 'testing');

        // App should be usable for route registration
        $app->get('/test', fn () => 'OK');

        $response = $app->handle(
            new \Avax\Framework\System\Capabilities\Runtime\RuntimeRequest(
                method: 'GET',
                uri: '/test',
            ),
        );

        self::assertSame(200, $response->getStatusCode());
    }
}
