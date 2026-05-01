<?php

declare(strict_types=1);

namespace Avax\Tests\Feature\Framework;

use Avax\Framework\System\Configuration\BuildApplication\BuildApplication;
use Avax\Framework\System\PublicSurface\Avax;
use PHPUnit\Framework\TestCase;

final class BootApplicationFeatureTest extends TestCase
{
    public function test_application_can_boot_with_no_registered_components(): void
    {
        $avax = Avax::boot(
            builder: BuildApplication::fromProjectPath(projectPath: $this->projectRoot()),
        );

        self::assertTrue($avax->state()->isBooted());
        self::assertSame([], $avax->components()->names());
    }

    private function projectRoot(): string
    {
        return dirname(path: __DIR__, levels: 3);
    }
}
