<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\ApiVersioning;

use Avax\Components\HTTP\ApiVersioning\System\PublicSurface\ApiVersion;
use Avax\Tests\TestCase;
use DateTimeImmutable;
use GuzzleHttp\Psr7\ServerRequest;

final class ApiVersionTest extends TestCase
{
    public function test_api_version_is_resolved_from_headers_and_deprecation_registry() : void
    {
        $sunset = new DateTimeImmutable(datetime: '+30 days');
        ApiVersion::deprecated(version: 2, sunset: $sunset);

        $resolved = ApiVersion::resolve(
            request: new ServerRequest(method: 'GET', uri: '/reports', headers: ['X-API-Version' => '2']),
        );

        self::assertSame(expected: 2, actual: $resolved->version);
        self::assertTrue(condition: $resolved->deprecated);
        self::assertSame(expected: $sunset, actual: $resolved->sunset);
    }
}
