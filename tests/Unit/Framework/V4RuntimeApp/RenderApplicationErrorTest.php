<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\V4RuntimeApp;

use Avax\Components\HTTP\Router\System\Foundation\Exceptions\MethodNotAllowedException;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\RouteNotFoundException;
use Avax\Components\HTTP\SecureRequest\System\Foundation\Failure\SecureRequestAuthorizationFailed;
use Avax\Components\HTTP\SecureRequest\System\Foundation\Failure\SecureRequestValidationFailed;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RenderApplicationError\RenderApplicationError;
use RuntimeException;
use Avax\Tests\TestCase;

/**
 * @covers \Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RenderApplicationError\RenderApplicationError
 */
final class RenderApplicationErrorTest extends TestCase
{
    private RenderApplicationError $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new RenderApplicationError();
    }

    public function testRenderInProductionModeHidesDetails(): void
    {
        $exception = new RuntimeException('Sensitive database connection string exposed');
        $response = $this->renderer->render(e: $exception, isProduction: true);

        self::assertSame(500, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertStringNotContainsString('Sensitive database connection string exposed', $body);
    }

    public function testRenderInDevelopmentModeShowsMessage(): void
    {
        $exception = new RuntimeException('Debug message for developers');
        $response = $this->renderer->render(e: $exception, isProduction: false);

        self::assertSame(500, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertStringContainsString('Debug message for developers', $body);
    }

    public function testRenderValidationReturns422(): void
    {
        $exception = new SecureRequestValidationFailed('Email is required');
        $response = $this->renderer->render(e: $exception, isProduction: true);

        self::assertSame(422, $response->getStatusCode());
    }

    public function testRenderAuthorizationReturns403(): void
    {
        $exception = new SecureRequestAuthorizationFailed('Forbidden');
        $response = $this->renderer->render(e: $exception, isProduction: true);

        self::assertSame(403, $response->getStatusCode());
    }

    public function testRenderNotFoundReturns404(): void
    {
        $exception = new RouteNotFoundException('/missing');
        $response = $this->renderer->render(e: $exception, isProduction: true);

        self::assertSame(404, $response->getStatusCode());
    }

    public function testRenderMethodNotAllowedReturns405(): void
    {
        $exception = new MethodNotAllowedException();
        $response = $this->renderer->render(e: $exception, isProduction: true);

        self::assertSame(405, $response->getStatusCode());
    }

    public function testResponseIsJson(): void
    {
        $exception = new RuntimeException('Error');
        $response = $this->renderer->render(e: $exception, isProduction: true);

        $contentType = $response->getHeaderLine('Content-Type');
        self::assertStringContainsString('application/json', $contentType);
    }
}
