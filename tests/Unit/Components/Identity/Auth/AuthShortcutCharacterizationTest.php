<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth;

use Avax\Components\Identity\Auth\System\PublicSurface\Auth;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AuthShortcutCharacterizationTest extends TestCase
{
    #[Test]
    public function authShortcutDelegatesWithoutContainerShortcut(): void
    {
        require_once __DIR__ . '/../../../../../components/Identity/Auth/System/PublicSurface/shortcuts.php';

        self::assertTrue(function_exists('auth'));
        self::assertInstanceOf(Auth::class, auth());
    }
}
