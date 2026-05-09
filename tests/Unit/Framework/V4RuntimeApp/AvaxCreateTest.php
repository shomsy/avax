<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\V4RuntimeApp;

use Avax\Framework\System\PublicSurface\App;
use Avax\Framework\System\PublicSurface\Avax;
use Avax\Tests\TestCase;

/**
 * @covers \Avax\Framework\System\PublicSurface\Avax
 */
final class AvaxCreateTest extends TestCase
{
    public function testCreateReturnsAppInstance(): void
    {
        $app = Avax::create(environment: 'testing');
        self::assertSame('testing', $app->runtime()->environment()->toString());
    }

    public function testCreateWithDefaultEnvironment(): void
    {
        $app = Avax::create();
        self::assertSame('production', $app->runtime()->environment()->toString());
    }

    public function testCreateAppCanRegisterRoutes(): void
    {
        $app = Avax::create(environment: 'testing');

        $app->get('/test', fn () => 'OK');

        $response = $app->handle(
            new \Avax\Framework\System\Capabilities\Runtime\RuntimeRequest(
                method: 'GET',
                uri: '/test',
            ),
        );

        self::assertSame(200, $response->getStatusCode());
    }

    public function testCreateInTestingEnvironment(): void
    {
        $app = Avax::create(environment: 'testing');

        self::assertSame('testing', $app->runtime()->environment()->toString());
    }

    public function testCreateInProductionEnvironment(): void
    {
        $app = Avax::create(environment: 'production');

        self::assertSame('production', $app->runtime()->environment()->toString());
    }

    public function testCreateInDevelopmentEnvironment(): void
    {
        $app = Avax::create(environment: 'development');

        self::assertSame('development', $app->runtime()->environment()->toString());
    }
}
