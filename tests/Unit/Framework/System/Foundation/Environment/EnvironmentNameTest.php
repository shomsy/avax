<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Foundation\Environment;

use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;
use Avax\Tests\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(EnvironmentName::class)]
final class EnvironmentNameTest extends TestCase
{
    #[Test]
    public function it_accepts_valid_environment_name(): void
    {
        $environmentName = new EnvironmentName(value: 'production');

        self::assertSame('production', $environmentName->toString());
    }

    #[Test]
    public function it_accepts_case_insensitive_names(): void
    {
        $environmentName = new EnvironmentName(value: '  STAGING  ');

        self::assertSame('STAGING', $environmentName->toString());
    }

    #[Test]
    public function it_normalizes_environment_name(): void
    {
        $environmentName = new EnvironmentName(value: '  local  ');

        self::assertSame('local', $environmentName->toString());
    }

    #[Test]
    public function it_throws_for_empty_environment_name(): void
    {
        $this->expectException(FrameworkMisconfigured::class);
        $this->expectExceptionMessage('Environment name cannot be empty.');

        new EnvironmentName(value: '   ');
    }
}
