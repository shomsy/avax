<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Flows\BootApplication;

use Avax\Framework\System\Configuration\BuildApplication\BuildApplication;
use Avax\Framework\System\Flows\BootApplication\BootApplication;
use PHPUnit\Framework\TestCase;

final class BootApplicationTest extends TestCase
{
    public function test_it_boots_runtime_with_request_scope_and_state_reset_registry(): void
    {
        $runtime = (new BootApplication)->boot(
            builder: BuildApplication::fromProjectPath(projectPath: $this->projectRoot()),
        );

        self::assertTrue($runtime->state()->isBooted());
        self::assertFalse($runtime->requestScopes()->hasCurrent());
        self::assertTrue($runtime->stateResetRegistry()->resetAll()->wasSuccessful());
    }

    private function projectRoot(): string
    {
        return dirname(path: __DIR__, levels: 6);
    }
}
