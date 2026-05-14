<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\V4RuntimeApp;

use Avax\Components\HTTP\System\Capabilities\ResponseBuilding\ResponseFactory;
use Avax\Framework\System\Capabilities\HealthCheck\CheckApplicationHealth;
use Avax\Tests\TestCase;

/**
 * @covers \Avax\Framework\System\Capabilities\HealthCheck\CheckApplicationHealth
 */
final class CheckApplicationHealthTest extends TestCase
{
    public function testHealthEndpointReturnsOk(): void
    {
        $checker = new CheckApplicationHealth(new ResponseFactory());
        $response = $checker->check();

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $data = json_decode($body, true);
        self::assertIsArray($data);
        self::assertSame('ok', $data['status']);
    }

    public function testHealthResponseIsJson(): void
    {
        $checker = new CheckApplicationHealth(new ResponseFactory());
        $response = $checker->check();

        $contentType = $response->getHeaderLine('Content-Type');
        self::assertStringContainsString('application/json', $contentType);
    }
}
