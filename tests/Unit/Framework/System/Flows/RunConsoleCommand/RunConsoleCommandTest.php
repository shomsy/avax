<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Flows\RunConsoleCommand;

use Avax\Framework\System\Configuration\BuildApplication\BuildApplication;
use Avax\Framework\System\PublicSurface\Avax;
use PHPUnit\Framework\TestCase;

final class RunConsoleCommandTest extends TestCase
{
    public function test_it_runs_registered_framework_commands(): void
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

    public function test_help_lists_legacy_component_commands_from_existing_code(): void
    {
        $avax = Avax::boot(
            builder: BuildApplication::fromProjectPath(projectPath: $this->projectRoot()),
        );

        $runtimeResult = $avax->console()->run(commandName: 'help');

        self::assertSame(0, $runtimeResult->exitCode());
        self::assertStringContainsString('migrate', $runtimeResult->output());
    }

    private function projectRoot(): string
    {
        return dirname(path: __DIR__, levels: 6);
    }
}
