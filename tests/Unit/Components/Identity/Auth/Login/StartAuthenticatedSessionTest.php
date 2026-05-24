<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth\Login;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\IssuedAuthentication;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Components\Identity\Auth\System\Flows\Login\StartAuthenticatedSession;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * StartAuthenticatedSession tests.
 *
 * Note: Sessions is a final class without a start() method currently.
 * This test verifies the class construction and expected behavior contract.
 */
final class StartAuthenticatedSessionTest extends TestCase
{
    #[Test]
    public function test_start_authenticated_session_class_is_final_readonly(): void
    {
        $reflection = new ReflectionClass(StartAuthenticatedSession::class);
        self::assertTrue($reflection->isFinal(), 'StartAuthenticatedSession must be final');
        self::assertTrue($reflection->isReadonly(), 'StartAuthenticatedSession must be readonly');
    }

    #[Test]
    public function test_start_authenticated_session_has_sessions_dependency(): void
    {
        $reflection = new ReflectionClass(StartAuthenticatedSession::class);
        $constructor = $reflection->getConstructor();

        self::assertNotNull($constructor);
        $params = $constructor->getParameters();
        self::assertCount(1, $params);
        self::assertSame('sessions', $params[0]->getName());
    }
}
