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
        $application = Avax::boot(
            builder: BuildApplication::fromProjectPath(projectPath: $this->projectRoot()),
        );

        self::assertTrue($application->state()->isBooted());
        self::assertSame([], $application->components()->names());
    }

    private function projectRoot(): string
    {
        return dirname(path: __DIR__, levels: 3);
    }
}
