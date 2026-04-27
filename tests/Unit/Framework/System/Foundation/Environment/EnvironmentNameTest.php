<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Foundation\Environment;

use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Avax\Tests\Framework\TestCase;

#[CoversClass(EnvironmentName::class)]
final class EnvironmentNameTest extends TestCase
{
    #[Test]
    public function it_accepts_valid_environment_name(): void
    {
        $env = new EnvironmentName(value: 'production');

        self::assertSame('production', $env->toString());
    }

    #[Test]
    public function it_accepts_case_insensitive_names(): void
    {
        $env = new EnvironmentName(value: '  STAGING  ');

        self::assertSame('STAGING', $env->toString());
    }

    #[Test]
    public function it_normalizes_environment_name(): void
    {
        $env = new EnvironmentName(value: '  local  ');

        self::assertSame('local', $env->toString());
    }

    #[Test]
    public function it_throws_for_empty_environment_name(): void
    {
        $this->expectException(FrameworkMisconfigured::class);
        $this->expectExceptionMessage('Environment name cannot be empty.');

        new EnvironmentName(value: '   ');
    }
}