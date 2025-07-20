<?php

declare(strict_types=1);

/**
 * Represents a container for database migration commands.
 */

namespace Gemini\Database\Migration\Runner\Commands;

use Gemini\Database\Migration\Runner\Generators\Migration\MigrationGenerator;
use Gemini\DataHandling\ArrayHandling\Arrhae;

/**
 * The Commands class is responsible for managing and organizing available commands
 * within the application.
 *
 * It initializes and retrieves a collection of commands during instantiation.
 */
class Commands
{
    private Arrhae $commands;

    public function __construct()
    {
        $this->commands = $this->getCommands();
    }

    private function getCommands() : Arrhae
    {
        return Arrhae::make(
            items: [
                       //'install'          => new InstallCommand(),
                       'create:migration' => new MigrationGenerator(),
                   ]
        );
    }
}