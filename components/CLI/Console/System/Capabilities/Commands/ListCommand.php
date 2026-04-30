<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Capabilities\Commands;

use Avax\Components\CLI\Console\System\PublicSurface\Command;
use Avax\Components\CLI\Console\System\PublicSurface\Console;

/**
 * Command that lists all registered console commands.
 */
class ListCommand extends Command
{
    protected string $name        = 'list';
    protected string $description = 'List all available commands';
    protected string $signature   = 'list';

    public function __construct(private readonly Console $console) {}

    protected function handle() : int
    {
        $commands = $this->console->getCommands();

        if (empty($commands)) {
            $this->info('No commands registered.');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($commands as $command) {
            $rows[] = [
                $command->getName(),
                $command->getDescription(),
                $command->getSignature(),
            ];
        }

        $this->output->table(['Command', 'Description', 'Signature'], $rows);

        return self::SUCCESS;
    }
}
