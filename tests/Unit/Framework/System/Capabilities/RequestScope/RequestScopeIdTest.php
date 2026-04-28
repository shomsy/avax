<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Capabilities\RequestScope;

use Avax\Framework\System\Capabilities\RequestScope\RequestScopeId;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;
use Avax\Tests\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RequestScopeId::class)]
final class RequestScopeIdTest extends TestCase
{
    #[Test]
    public function it_accepts_valid_id_value(): void
    {
        $id = new RequestScopeId(value: 'abc123def456');

        self::assertSame('abc123def456', $id->toString());
    }

    #[Test]
    public function it_trims_whitespace(): void
    {
        $id = new RequestScopeId(value: '  trimmed  ');

        self::assertSame('trimmed', $id->toString());
    }

    #[Test]
    public function it_throws_for_empty_value(): void
    {
        $this->expectException(FrameworkMisconfigured::class);
        $this->expectExceptionMessage('Request scope id cannot be empty.');

        new RequestScopeId(value: '   ');
    }

    #[Test]
    public function it_generates_unique_random_ids(): void
    {
        $id1 = RequestScopeId::generate();
        $id2 = RequestScopeId::generate();

        self::assertNotSame($id1->toString(), $id2->toString());
    }

    #[Test]
    public function it_generates_32_character_hex_id(): void
    {
        $id = RequestScopeId::generate();

        self::assertSame(32, strlen($id->toString()));
        self::assertMatchesRegularExpression(pattern: '/^[a-f0-9]{32}$/', $id->toString());
    }
}