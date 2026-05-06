<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\CLI\Console;

use Avax\Components\CLI\Console\System\Capabilities\Input\ConsoleInput;
use Avax\Components\CLI\Console\System\Capabilities\Output\ConsoleOutput;
use Avax\Components\CLI\Console\System\PublicSurface\Command;
use Avax\Components\CLI\Console\System\PublicSurface\Console;
use PHPUnit\Framework\TestCase;
use Throwable;

final class ConsoleCapabilitiesTest extends TestCase
{
    public function test_console_registers_resolves_and_lists_public_commands() : void
    {
        $console = $this->console();
        $command = $this->command(name: 'demo', description: 'Demo command', signature: 'demo {name}');

        $console->register($command);

        self::assertTrue($console->has('demo'));
        self::assertSame($command, $console->resolve('demo'));
        self::assertSame(['demo' => $command], $console->getCommands());

        $output = $this->captureOutput(static function () use ($console) : void {
            $console->list();
        });

        self::assertStringContainsString('AvaX Test Console version 1.2.3', $output);
        self::assertStringContainsString('Available commands:', $output);
        self::assertStringContainsString('demo', $output);
        self::assertStringContainsString('Demo command', $output);
    }

    private function console() : Console
    {
        return new Console(
            name         : 'AvaX Test Console',
            version      : '1.2.3',
            consoleOutput: new ConsoleOutput(false),
        );
    }

    private function command(
        string $name,
        string $description = 'Inspectable command',
        string $signature = 'inspect {name} [--name=] [-v]',
        int    $exitCode = Command::SUCCESS,
    ) : Command
    {
        return new class($name, $description, $signature, $exitCode) extends Command {
            public function __construct(
                string               $name,
                string               $description,
                string               $signature,
                private readonly int $exitCode,
            )
            {
                $this->name        = $name;
                $this->description = $description;
                $this->signature   = $signature;
            }

            protected function handle() : int
            {
                $this->line('argument=' . (string) $this->argument('name', 'none'));
                $this->line('name=' . (string) $this->option('name', 'none'));
                $this->line('verbose=' . ($this->hasOption('v') ? 'yes' : 'no'));

                return $this->exitCode;
            }
        };
    }

    /**
     * @param callable(): void $operation
     */
    private function captureOutput(callable $operation) : string
    {
        ob_start();

        try {
            $operation();

            return (string) ob_get_clean();
        } catch (Throwable $throwable) {
            ob_end_clean();

            throw $throwable;
        }
    }

    public function test_console_without_command_prints_usage_and_returns_success() : void
    {
        $console = $this->console();

        $exitCode = null;
        $output   = $this->captureOutput(static function () use ($console, &$exitCode) : void {
            $exitCode = $console->run(['avax']);
        });

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('Usage:', $output);
        self::assertStringContainsString('No commands registered.', $output);
    }

    public function test_console_version_flag_prints_configured_name_and_version() : void
    {
        $console = $this->console();

        $exitCode = null;
        $output   = $this->captureOutput(static function () use ($console, &$exitCode) : void {
            $exitCode = $console->run(['avax', '--version']);
        });

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertSame('AvaX Test Console version 1.2.3' . PHP_EOL, $output);
    }

    public function test_unknown_command_prints_error_lists_registered_commands_and_returns_failure() : void
    {
        $console = $this->console();
        $console->register($this->command(name: 'known', description: 'Known command', signature: 'known'));

        $exitCode = null;
        $output   = $this->captureOutput(static function () use ($console, &$exitCode) : void {
            $exitCode = $console->run(['avax', 'missing']);
        });

        self::assertSame(Command::FAILURE, $exitCode);
        self::assertStringContainsString("Command 'missing' not found.", $output);
        self::assertStringContainsString('known', $output);
        self::assertStringContainsString('Known command', $output);
    }

    public function test_registered_command_receives_arguments_options_and_propagates_exit_code() : void
    {
        $console = $this->console();
        $console->register($this->command(name: 'inspect', exitCode: Command::INVALID));

        $exitCode = null;
        $output   = $this->captureOutput(static function () use ($console, &$exitCode) : void {
            $exitCode = $console->run(['avax', 'inspect', 'first', '--name=avax', '-v']);
        });

        self::assertSame(Command::INVALID, $exitCode);
        self::assertStringContainsString('argument=first', $output);
        self::assertStringContainsString('name=avax', $output);
        self::assertStringContainsString('verbose=yes', $output);
    }

    public function test_command_optional_argument_resolves_to_default_when_not_provided() : void
    {
        $console = $this->console();
        $console->register(new class extends Command {
            public function __construct()
            {
                $this->name        = 'opt';
                $this->description = 'Optional arg';
                $this->signature   = 'opt {arg?}';
            }

            protected function handle() : int
            {
                $value = $this->argument('arg', 'default_value');
                $this->line('resolved=' . $value);

                return Command::SUCCESS;
            }
        });

        $exitCode = null;
        $output   = $this->captureOutput(static function () use ($console, &$exitCode) : void {
            $exitCode = $console->run(['avax', 'opt']);
        });

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('resolved=default_value', $output);
    }

    public function test_command_binds_multiple_positional_arguments_by_name() : void
    {
        $console = $this->console();
        $console->register(new class extends Command {
            public function __construct()
            {
                $this->name        = 'multi';
                $this->description = 'Multi args';
                $this->signature   = 'multi {first} {second} {third}';
            }

            protected function handle() : int
            {
                $this->line('first=' . $this->argument('first'));
                $this->line('second=' . $this->argument('second'));
                $this->line('third=' . $this->argument('third'));

                return Command::SUCCESS;
            }
        });

        $exitCode = null;
        $output   = $this->captureOutput(static function () use ($console, &$exitCode) : void {
            $exitCode = $console->run(['avax', 'multi', 'alpha', 'beta', 'gamma']);
        });

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('first=alpha', $output);
        self::assertStringContainsString('second=beta', $output);
        self::assertStringContainsString('third=gamma', $output);
    }

    public function test_command_options_do_not_interfere_with_positional_binding() : void
    {
        $console = $this->console();
        $console->register(new class extends Command {
            public function __construct()
            {
                $this->name        = 'mixed';
                $this->description = 'Mixed args';
                $this->signature   = 'mixed {filename} [--format=]';
            }

            protected function handle() : int
            {
                $this->line('file=' . $this->argument('filename'));
                $this->line('fmt=' . $this->option('format', 'none'));

                return Command::SUCCESS;
            }
        });

        $exitCode = null;
        $output   = $this->captureOutput(static function () use ($console, &$exitCode) : void {
            $exitCode = $console->run(['avax', 'mixed', 'test.php', '--format=json']);
        });

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('file=test.php', $output);
        self::assertStringContainsString('fmt=json', $output);
    }

    public function test_command_positional_arguments_are_bound_by_name() : void
    {
        $console = $this->console();
        $console->register(new class extends Command {
            public function __construct()
            {
                $this->name      = 'test';
                $this->signature = 'test {first} {second}';
            }

            protected function handle() : int
            {
                $this->line('1=' . $this->argument('first'));
                $this->line('2=' . $this->argument('second'));

                return self::SUCCESS;
            }
        });

        $output = $this->captureOutput(fn () => $console->run(['avax', 'test', 'val1', 'val2']));

        self::assertStringContainsString('1=val1', $output);
        self::assertStringContainsString('2=val2', $output);
    }

    public function test_command_options_are_bound_by_name() : void
    {
        $console = $this->console();
        $console->register(new class extends Command {
            public function __construct()
            {
                $this->name      = 'test';
                $this->signature = 'test [--name=] [--force]';
            }

            protected function handle() : int
            {
                $this->line('name=' . $this->option('name', 'none'));
                $this->line('force=' . ($this->hasOption('force') ? 'yes' : 'no'));

                return self::SUCCESS;
            }
        });

        $output = $this->captureOutput(fn () => $console->run(['avax', 'test', '--name=avax', '--force']));

        self::assertStringContainsString('name=avax', $output);
        self::assertStringContainsString('force=yes', $output);
    }

    public function test_command_missing_optional_arguments_resolve_safely_to_default() : void
    {
        $console = $this->console();
        $console->register(new class extends Command {
            public function __construct()
            {
                $this->name      = 'test';
                $this->signature = 'test {opt?}';
            }

            protected function handle() : int
            {
                $this->line('val=' . $this->argument('opt', 'fallback'));

                return self::SUCCESS;
            }
        });

        $output = $this->captureOutput(fn () => $console->run(['avax', 'test']));

        self::assertStringContainsString('val=fallback', $output);
    }

    public function test_command_missing_required_arguments_fail_clearly() : void
    {
        $console = $this->console();
        $console->register(new class extends Command {
            public function __construct()
            {
                $this->name      = 'test';
                $this->signature = 'test {required}';
            }

            protected function handle() : int
            {
                return self::SUCCESS;
            }
        });

        $exitCode = null;
        $output   = $this->captureOutput(static function () use ($console, &$exitCode) : void {
            $exitCode = $console->run(['avax', 'test']);
        });

        self::assertSame(Command::INVALID, $exitCode);
        self::assertStringContainsString('Missing required argument: required', $output);
    }

    public function test_command_argument_lookup_does_not_depend_on_accidental_array_index_behavior() : void
    {
        $console = $this->console();
        $console->register(new class extends Command {
            public function __construct()
            {
                $this->name      = 'test';
                $this->signature = 'test {first}';
            }

            protected function handle() : int
            {
                $this->line('by_name=' . $this->argument('first'));
                $this->line('by_index=' . $this->argument(0));

                return self::SUCCESS;
            }
        });

        $output = $this->captureOutput(fn () => $console->run(['avax', 'test', 'value']));

        self::assertStringContainsString('by_name=value', $output);
        self::assertStringContainsString('by_index=value', $output);
    }

    public function test_console_input_parses_positional_arguments_and_flags() : void
    {
        $input = ConsoleInput::fromArgv(['first', '--name=avax', '--dry-run', '-xz', '-p=8080']);

        self::assertSame(['first'], $input->getArguments());
        self::assertSame('first', $input->getFirstArgument());
        self::assertSame([], $input->getRemainingArguments());
        self::assertSame('avax', $input->getOption('name'));
        self::assertTrue($input->getOption('dry-run'));
        self::assertTrue($input->hasOption('x'));
        self::assertTrue($input->hasOption('z'));
        self::assertSame('8080', $input->getOption('p'));
    }

    public function test_console_output_writes_plain_messages_without_ansi_when_colors_are_disabled() : void
    {
        $output = new ConsoleOutput(false);

        $captured = $this->captureOutput(static function () use ($output) : void {
            $output->info('info');
            $output->error('error');
            $output->warn('warn');
            $output->comment('comment');
            $output->bold('bold');
            $output->write('raw');
            $output->newLine();
        });

        self::assertSame(
            'info' . PHP_EOL
            . 'error' . PHP_EOL
            . 'warn' . PHP_EOL
            . 'comment' . PHP_EOL
            . 'bold' . PHP_EOL
            . 'raw' . PHP_EOL,
            $captured,
        );
    }
}
