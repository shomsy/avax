<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Console;

use Exception;
use Avax\Database\Migration\Runner\Generators\Migration\MigrationGenerator;
use Avax\DataHandling\ArrayHandling\Arrhae;

/**
 * The CLI class handles command-line interactions for database migrations.
 * It utilizes the Arrhae collection class for managing commands and arguments,
 * providing enhanced flexibility and powerful data manipulation capabilities.
 *
 * Example Usage:
 * php Avax create:migration --name=CreateUsersTable
 */
class CLI
{
    /**
     * @var Arrhae The collection of available commands.
     */
    private Arrhae $commands;

    /**
     * CLI constructor.
     *
     * Initializes the commands collection using the Arrhae::make() factory method.
     */
    public function __construct()
    {
        // Initialize the command collection with Arrhae
        $this->commands = Arrhae::make(
            items: [
                       'make:migration' => new MigrationGenerator(),
                   ]
        );
    }

    /**
     * Executes the CLI command based on provided arguments.
     *
     * @param array $argv Command-line arguments.
     *
     * @return void
     */
    public function run(array $argv) : void
    {
        // Wrap the $argv array into an Arrhae collection for enhanced manipulation
        $args = Arrhae::make(items: $argv);

        // Check if at least one command is provided
        if ($args->count() < 2) {
            $this->displayUsage();
            exit(1);
        }

        // Retrieve the command name (second argument)
        $commandName = $args->get(key: 1);

        // Format the arguments using the Arrhae-based method
        $arguments = $this->formatArguments(args: $args->slice(offset: 2));

        // Check if the command exists in the collection
        if (! $this->commands->has(key: $commandName)) {
            echo "Command not found: {$commandName}\n";
            $this->suggestSimilarCommands(commandName: $commandName);
            exit(1);
        }

        // Retrieve the command instance
        $command = $this->commands->get(key: $commandName);

        // Ensure the command is executable
        if (! method_exists($command, 'execute')) {
            echo "Command '{$commandName}' is not executable.\n";
            exit(1);
        }

        // Execute the command with the formatted arguments
        try {
            $command->execute($arguments->toArray());
        } catch (Exception $e) {
            echo "Error executing command '{$commandName}': " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Displays the usage instructions for the CLI.
     *
     * @return void
     */
    private function displayUsage() : void
    {
        echo "Usage: php Avax <command> [options]\n";
        echo "Available Commands:\n";
        echo $this->commands->keys()->map(static fn($command) => "  - {$command}")->implode("\n") . "\n";
    }

    /**
     * Formats command-line arguments into a structured Arrhae collection.
     *
     * This method parses arguments to handle both flag-style (e.g., --key=value)
     * and positional arguments, assigning the first positional argument to 'name'.
     *
     * @param Arrhae $args Raw command-line arguments (excluding script name and command name).
     *
     * @return Arrhae Formatted arguments as an Arrhae collection.
     */
    private function formatArguments(Arrhae $args) : Arrhae
    {
        // Use Arrhae's filtering and mapping capabilities to parse arguments
        return $args
            ->filter(callback: static fn($arg, $key) => is_string($arg) && $key !== 0) // Exclude script name
            ->mapWithKeys(callback: static function ($arg) {
                if (str_starts_with($arg, '--')) {
                    // Parse --key=value arguments
                    $keyValue = substr($arg, 2);
                    $parts    = explode('=', $keyValue, 2);

                    $key = $parts[0];
                    $value = $parts[1] ?? true; // Assign true if no value is provided

                    return [$key => $value];
                } elseif (! str_starts_with($arg, '--') && ! isset($arg)) {
                    // Assign the first positional argument to 'name'
                    return ['name' => $arg];
                }

                return [];
            })
            // Ensure 'name' is set if a positional argument exists
            ->when(
                condition: $args->filter(callback: fn($arg) => ! str_starts_with($arg, '--'))->count() > 0,
                callback : function ($collection) use ($args) {
                    $positionalArgs = $args->filter(callback: fn($arg) => ! str_starts_with($arg, '--'));

                    return $collection->set('name', $positionalArgs->first());
                }
            );
    }

    /**
     * Suggests similar commands if the provided command is not found.
     *
     * @param string $commandName The command name that was not found.
     *
     * @return void
     */
    private function suggestSimilarCommands(string $commandName) : void
    {
        // Wrap the keys into an Arrhae instance to use fuzzyMatch
        $similarCommands = Arrhae::make(items: $this->commands->keys())
            ->fuzzyMatch(query: $commandName, threshold: 60)
            ->toArray();

        if (! empty($similarCommands)) {
            echo "Did you mean:\n";
            echo Arrhae::make(items: $similarCommands)
                     ->map(callback: fn($cmd) => "  - {$cmd}")
                     ->implode("\n") . "\n";
        }
    }
}
