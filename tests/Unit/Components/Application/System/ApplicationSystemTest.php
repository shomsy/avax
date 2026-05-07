<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\System;

use Avax\Components\Application\System\System\PublicSurface\Application;
use PHPUnit\Framework\TestCase;

final class ApplicationSystemTest extends TestCase
{
    public function test_it_can_bootstrap_application() : void
    {
        $app = Application::bootstrap();
        $this->assertInstanceOf(Application::class, $app);
    }
}
