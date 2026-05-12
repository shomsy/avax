<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\V4HealthEndpoints;

use Avax\Framework\System\Capabilities\Health\CheckLiveness;
use Avax\Framework\System\Capabilities\Health\CheckReadiness;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthFinding;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Flows\CreateApplication\CreateApplication;
use Avax\Framework\System\Flows\RegisterHealthRoutes\RegisterHealthRoutes;
use Avax\Framework\System\PublicSurface\App;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class V4HealthEndpointsTest extends TestCase
{
    private App $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = (new CreateApplication())->make(environment: 'testing');
    }

    #[Test]
    public function health_endpoint_returns_ok(): void
    {
        $this->registerHealthRoutes();

        $response = $this->app->handle(new RuntimeRequest(method: 'GET', uri: '/health'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $data = json_decode($body, true);
        self::assertSame('ok', $data['status']);
        self::assertArrayHasKey('timestamp', $data);
    }

    #[Test]
    public function health_live_endpoint_returns_alive(): void
    {
        $this->registerHealthRoutes();

        $response = $this->app->handle(new RuntimeRequest(method: 'GET', uri: '/health/live'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $data = json_decode($body, true);
        self::assertSame('green', $data['status']);
    }

    #[Test]
    public function health_ready_endpoint_returns_ready_when_healthy(): void
    {
        $readiness = new CheckReadiness([
            static fn () => new HealthFinding('db', HealthStatus::Green, 'Connected.'),
        ], '1.0.0');

        $this->registerHealthRoutes(readiness: $readiness);

        $response = $this->app->handle(new RuntimeRequest(method: 'GET', uri: '/health/ready'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $data = json_decode($body, true);
        self::assertSame('green', $data['status']);
        self::assertSame('1.0.0', $data['version']);
    }

    #[Test]
    public function health_ready_returns_non_ready_when_unhealthy(): void
    {
        $readiness = new CheckReadiness([
            static fn () => new HealthFinding('db', HealthStatus::Red, 'Connection refused.'),
        ]);

        $this->registerHealthRoutes(readiness: $readiness);

        $response = $this->app->handle(new RuntimeRequest(method: 'GET', uri: '/health/ready'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $data = json_decode($body, true);
        self::assertSame('red', $data['status']);
    }

    #[Test]
    public function production_mode_redacts_details(): void
    {
        $this->registerHealthRoutes(productionMode: true);

        $response = $this->app->handle(new RuntimeRequest(method: 'GET', uri: '/health/live'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        // In production mode, no detailed findings are exposed
        self::assertArrayNotHasKey('findings', $data);
    }

    #[Test]
    public function test_mode_includes_details(): void
    {
        $this->registerHealthRoutes(productionMode: false);

        $response = $this->app->handle(new RuntimeRequest(method: 'GET', uri: '/health/live'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        // In test mode, detailed findings are included
        self::assertArrayHasKey('findings', $data);
    }

    #[Test]
    public function health_ready_shows_checks_in_test_mode(): void
    {
        $readiness = new CheckReadiness([
            static fn () => new HealthFinding('db', HealthStatus::Green, 'Connected.'),
            static fn () => new HealthFinding('cache', HealthStatus::Green, 'Available.'),
        ]);

        $this->registerHealthRoutes(readiness: $readiness, productionMode: false);

        $response = $this->app->handle(new RuntimeRequest(method: 'GET', uri: '/health/ready'));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        self::assertArrayHasKey('checks', $data);
        self::assertCount(2, $data['checks']);
    }

    #[Test]
    public function health_ready_hides_checks_in_production_mode(): void
    {
        $readiness = new CheckReadiness([
            static fn () => new HealthFinding('db', HealthStatus::Green, 'Connected.'),
        ]);

        $this->registerHealthRoutes(readiness: $readiness, productionMode: true);

        $response = $this->app->handle(new RuntimeRequest(method: 'GET', uri: '/health/ready'));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        self::assertArrayNotHasKey('checks', $data);
    }

    private function registerHealthRoutes(CheckReadiness|null $readiness = null,
        bool $productionMode = false,
    ): void {
        $liveness = new CheckLiveness();
        $readiness ??= new CheckReadiness([], '1.0.0');

        $registerer = new RegisterHealthRoutes(
            liveness: $liveness,
            readiness: $readiness,
            productionMode: $productionMode,
        );

        $registerer->register($this->app);
    }
}
