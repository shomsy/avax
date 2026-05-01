<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DeveloperTools\Diagnostics;

use Avax\Components\DeveloperTools\Diagnostics\System\PublicSurface\CheckResult;
use Avax\Components\DeveloperTools\Diagnostics\System\PublicSurface\HealthCheck;
use Avax\Tests\TestCase;
use Override;
use RuntimeException;

final class HealthCheckTest extends TestCase
{
    public function test_readiness_uses_registered_checks_instead_of_fake_database_cache_results() : void
    {
        HealthCheck::register(name: 'database', check: static fn () : CheckResult => new CheckResult(status: 'up'));
        HealthCheck::register(name: 'cache', check: static fn () : CheckResult => throw new RuntimeException(message: 'redis unavailable'));

        $report = HealthCheck::readiness();

        self::assertSame(expected: 'degraded', actual: $report->status);
        self::assertSame(expected: 'up', actual: $report->checks['database']->status);
        self::assertSame(expected: 'down', actual: $report->checks['cache']->status);
    }

    #[Override]
    protected function tearDown() : void
    {
        HealthCheck::reset();

        parent::tearDown();
    }
}
