<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth\Capabilities\PasswordHashing;

use Avax\Components\Identity\Auth\System\Capabilities\PasswordHashing\PasswordHash;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class PasswordHashTest extends TestCase
{
    #[Test]
    public function test_password_hash_holds_value(): void
    {
        $hash = new PasswordHash('$2y$12$something');

        self::assertSame('$2y$12$something', $hash->value);
    }

    #[Test]
    public function test_password_hash_is_final_readonly(): void
    {
        $reflection = new ReflectionClass(PasswordHash::class);
        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->isReadonly());
    }
}
