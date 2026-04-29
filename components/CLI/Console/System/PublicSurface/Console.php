<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\PublicSurface;

use Avax\Components\CLI\Console\System\Capabilities\Commands\Command;

/**
 * Console application that can register and run commands.
 */
class Console
{
    /** @var array<string, Command> */
    private array  $commands   = [];
    private string $appName    = 'Avax Console';
    private string $appVersion = '1.0.0';

    public function __construct(?string $name = null, ?string $version = null)
    {
        if ($name !== null) {
            $this->appName = $name;
        }
        if ($version !== null) {
            $this->appVersion = $version;
        }
    }

    public function add(Command $command) : self
    {
        $this->commands[$command->getName()] = $command;

        return $this;
    }

    public function has(string $name) : bool
    {
        return isset($this->commands[$name]);
    }

    public function get(string $name) : ?Command
    {
        return $this->commands[$name] ?? null;
    }

    public function run(?array $argv = null) : int
    {
        $argv = $argv ?? $_SERVER['argv'] ?? [];
        array_shift($argv); // Remove script name

        if (empty($argv)) {
            $this->displayHelp();

            return 0;
        }

        $commandName = $argv[0];

        if ($commandName === '--help' || $commandName === '-h') {
            $this->displayHelp();

            return 0;
        }

        if ($commandName === '--version' || $commandName === '-V') {
            echo "{$this->appName} version {$this->appVersion}" . PHP_EOL;

            return 0;
        }

        if (! isset($this->commands[$commandName])) {
            echo "Command '{$commandName}' not found." . PHP_EOL;
            $this->displayHelp();

            return 1;
        }

        $command = $this->commands[$commandName];
        array_shift($argv); // Remove command name

        // Parse arguments and options
        $args = $this->parseArguments($argv);

        return $command->handle($args);
    }

    private function displayHelp() : void
    {
        echo "{$this->appName} version {$this->appVersion}" . PHP_EOL . PHP_EOL;
        echo "Usage:" . PHP_EOL;
        echo "  command [arguments] [options]" . PHP_EOL . PHP_EOL;

        if (empty($this->commands)) {
            echo "No commands registered." . PHP_EOL;

            return;
        }

        echo "Available commands:" . PHP_EOL;

        $maxNameLength = max(array_map(fn ($cmd) => strlen($cmd->getName()), $this->commands));

        foreach ($this->commands as $name => $command) {
            $padded = str_pad($name, $maxNameLength);
            $desc   = $command->getDescription();
            echo "  {$padded}  {$desc}" . PHP_EOL;
        }
    }

    private function parseArguments(array $argv) : array
    {
        $args    = [];
        $options = [];

        foreach ($argv as $arg) {
            if (str_starts_with($arg, '--')) {
                // Long option: --key=value or --key
                $parts         = explode('=', substr($arg, 2), 2);
                $key           = $parts[0];
                $options[$key] = $parts[1] ?? true;
            } elseif (str_starts_with($arg, '-')) {
                // Short option: -k=value or -k
                $parts         = explode('=', substr($arg, 1), 2);
                $key           = $parts[0];
                $options[$key] = $parts[1] ?? true;
            } else {
                // Positional argument
                $args[] = $arg;
            }
        }

        return [
            'arguments' => $args,
            'options'   => $options,
        ];
    }
}
