<?php

declare(strict_types=1);

namespace Avax\Tests\Feature\Framework;

use Avax\Framework\System\Configuration\BuildApplication\BuildApplication;
use Avax\Framework\System\PublicSurface\Avax;
use PHPUnit\Framework\TestCase;

final class ConsoleApplicationFeatureTest extends TestCase
{
    public function test_console_public_surface_runs_a_framework_command_end_to_end(): void
    {
        $avax = Avax::boot(
            builder: BuildApplication::fromProjectPath(projectPath: $this->projectRoot())
                ->registerConsoleCommand(
                    name   : 'ping',
                    command: static fn (): string => 'pong',
                ),
        );

        $runtimeResult = $avax->console()->run(commandName: 'ping');

        self::assertSame(0, $runtimeResult->exitCode());
        self::assertSame('pong', $runtimeResult->output());
    }

    private function projectRoot(): string
    {
        return dirname(path: __DIR__, levels: 3);
    }
}
