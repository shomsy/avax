<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Commands\Middlewares;

use Avax\Database\Migration\Runner\Generators\CommandInterface;

/**
 * Middleware for logging command execution.
 *
 * This middleware logs the execution of migration commands, both at the start and at the
 * completion of the command's execution. It helps to track command activities, useful for
 * debugging and auditing purposes.
 */
class LoggingMiddleware
{
    /**
     * Handles the command execution with logging.
     *
     * Logs the start and end of command execution, providing insights into command activities.
     *
     * @param array    $input                                               The input parameters for the command.
     * @param callable $next                                                The next middleware or the actual command
     *                                                                      execution.
     *
     */
    public function handle(CommandInterface $command, array $input, callable $next) : void
    {
        // Log the start of the command execution.
        logger(message: 'Executing command: ' . $command::class, context: $input, level: 'debug');

        // Proceed to the next middleware or actual command execution.
        $next();

        // Log the end of the command execution.
        logger(message: 'Command execution finished: ' . $command::class, context: [], level: 'debug');
    }
}